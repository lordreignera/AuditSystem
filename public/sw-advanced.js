// Advanced PWA Service Worker for ERA Audit System
const CACHE_VERSION = 'era-audit-v2.0';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const DYNAMIC_CACHE = `${CACHE_VERSION}-dynamic`;
const API_CACHE = `${CACHE_VERSION}-api`;
const OFFLINE_CACHE = `${CACHE_VERSION}-offline`;

// Critical resources that must be cached for offline functionality
const CRITICAL_RESOURCES = [
    '/',
    '/admin',
    '/admin/dashboard',
    '/offline.html',
    '/manifest.json',
    '/admin/assets/css/style.css',
    '/admin/assets/js/vendor.bundle.base.js',
    '/admin/assets/js/offline-manager.js',
    '/admin/assets/js/off-canvas.js',
    '/admin/assets/js/hoverable-collapse.js',
    '/admin/assets/js/misc.js',
    '/admin/assets/js/settings.js',
    '/admin/assets/images/logo-mini.svg',
    '/admin/assets/images/faces/face1.jpg', // User avatars
    '/admin/assets/vendors/mdi/css/materialdesignicons.min.css'
];

// API endpoints for intelligent caching
const CACHEABLE_API_PATTERNS = [
    /\/api\/user$/,
    /\/api\/countries$/,
    /\/api\/review-types$/,
    /\/api\/templates$/,
    /\/api\/audits\/\d+$/,
    /\/api\/questions\/\d+$/
];

// Advanced installation with intelligent prefetching
self.addEventListener('install', event => {
    console.log('🚀 ERA Audit PWA: Installing Service Worker v2.0');
    
    event.waitUntil(
        Promise.all([
            // Cache critical resources
            caches.open(STATIC_CACHE).then(cache => {
                console.log('📦 Caching critical resources');
                return cache.addAll(CRITICAL_RESOURCES);
            }),
            
            // Prefetch user data if authenticated
            prefetchUserData(),
            
            // Skip waiting to activate immediately
            self.skipWaiting()
        ])
    );
});

// Intelligent activation with cache cleanup
self.addEventListener('activate', event => {
    console.log('✅ ERA Audit PWA: Activating Service Worker');
    
    event.waitUntil(
        Promise.all([
            // Clean up old caches
            cleanupOldCaches(),
            
            // Claim all clients immediately
            self.clients.claim(),
            
            // Initialize background sync
            setupBackgroundSync(),
            
            // Setup periodic sync for data updates
            setupPeriodicSync()
        ])
    );
});

// Advanced fetch handler with intelligent caching strategies
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-HTTP requests
    if (!request.url.startsWith('http')) return;
    
    // Handle different types of requests with appropriate strategies
    if (isAPIRequest(url)) {
        event.respondWith(handleAPIRequest(request));
    } else if (isNavigationRequest(request)) {
        event.respondWith(handleNavigationRequest(request));
    } else if (isStaticAsset(url)) {
        event.respondWith(handleStaticAssetRequest(request));
    } else {
        event.respondWith(handleGenericRequest(request));
    }
});

// API Request Handler with intelligent caching
async function handleAPIRequest(request) {
    const url = new URL(request.url);
    const method = request.method;
    
    // For GET requests - Network First with fallback to cache
    if (method === 'GET') {
        try {
            const networkResponse = await fetch(request);
            
            if (networkResponse.ok) {
                // Cache successful responses for offline use
                if (isCacheableAPI(url)) {
                    const cache = await caches.open(API_CACHE);
                    cache.put(request, networkResponse.clone());
                }
                return networkResponse;
            }
        } catch (error) {
            console.log('🔌 Network failed, trying cache for:', url.pathname);
            
            // Try to serve from cache
            const cachedResponse = await caches.match(request);
            if (cachedResponse) {
                return cachedResponse;
            }
            
            // Return offline response for critical APIs
            return createOfflineAPIResponse(url);
        }
    }
    
    // For POST/PUT/DELETE - Handle offline submissions
    if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
        try {
            const networkResponse = await fetch(request);
            
            if (networkResponse.ok) {
                // Clear relevant caches on successful mutations
                await invalidateRelatedCaches(url);
                return networkResponse;
            }
        } catch (error) {
            console.log('📤 Storing offline request:', url.pathname);
            
            // Store for background sync
            await storeOfflineRequest(request);
            
            return new Response(
                JSON.stringify({
                    success: true,
                    message: 'Request saved offline. Will sync when online.',
                    offline: true,
                    timestamp: Date.now()
                }),
                {
                    status: 202, // Accepted
                    headers: { 'Content-Type': 'application/json' }
                }
            );
        }
    }
    
    // Fallback for other methods
    return fetch(request);
}

// Navigation Request Handler (App Shell Strategy)
async function handleNavigationRequest(request) {
    try {
        // Try network first for fresh content
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            return networkResponse;
        }
    } catch (error) {
        console.log('🌐 Navigation offline, serving cached version');
    }
    
    // Fallback to cached page or offline page
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
        return cachedResponse;
    }
    
    // Last resort - offline page
    return caches.match('/offline.html');
}

// Static Asset Handler (Cache First Strategy)
async function handleStaticAssetRequest(request) {
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
        // Serve from cache immediately
        
        // Update cache in background if online
        if (navigator.onLine) {
            updateCacheInBackground(request);
        }
        
        return cachedResponse;
    }
    
    // Not in cache, try network
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            // Cache successful responses
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('📁 Static asset not available offline:', request.url);
        
        // Return placeholder or fallback
        return createFallbackResponse(request);
    }
}

// Generic Request Handler
async function handleGenericRequest(request) {
    try {
        return await fetch(request);
    } catch (error) {
        const cachedResponse = await caches.match(request);
        return cachedResponse || createFallbackResponse(request);
    }
}

// Background Sync Handler
self.addEventListener('sync', event => {
    console.log('🔄 Background sync triggered:', event.tag);
    
    if (event.tag === 'era-audit-sync') {
        event.waitUntil(syncOfflineData());
    } else if (event.tag === 'era-audit-upload') {
        event.waitUntil(uploadOfflineFiles());
    }
});

// Push Notification Handler
self.addEventListener('push', event => {
    console.log('🔔 Push notification received');
    
    const options = {
        body: event.data ? event.data.text() : 'New audit notification',
        icon: '/admin/assets/images/logo-mini.png',
        badge: '/admin/assets/images/badge.png',
        vibrate: [200, 100, 200],
        data: {
            url: '/admin/dashboard'
        },
        actions: [
            {
                action: 'open',
                title: 'Open Audit',
                icon: '/admin/assets/images/open-icon.png'
            },
            {
                action: 'dismiss',
                title: 'Dismiss',
                icon: '/admin/assets/images/dismiss-icon.png'
            }
        ],
        requireInteraction: true
    };
    
    event.waitUntil(
        self.registration.showNotification('ERA Audit System', options)
    );
});

// Notification Click Handler
self.addEventListener('notificationclick', event => {
    event.notification.close();
    
    if (event.action === 'open') {
        event.waitUntil(
            clients.openWindow(event.notification.data.url || '/admin/dashboard')
        );
    }
});

// Helper Functions
function isAPIRequest(url) {
    return url.pathname.startsWith('/api/') || 
           url.pathname.startsWith('/admin/api/');
}

function isNavigationRequest(request) {
    return request.mode === 'navigate' || 
           (request.method === 'GET' && request.headers.get('accept').includes('text/html'));
}

function isStaticAsset(url) {
    const assetExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.svg', '.woff', '.woff2', '.ttf'];
    return assetExtensions.some(ext => url.pathname.includes(ext));
}

function isCacheableAPI(url) {
    return CACHEABLE_API_PATTERNS.some(pattern => pattern.test(url.pathname));
}

async function storeOfflineRequest(request) {
    const requestData = {
        url: request.url,
        method: request.method,
        headers: Object.fromEntries(request.headers.entries()),
        body: request.method !== 'GET' ? await request.text() : null,
        timestamp: Date.now(),
        id: generateUniqueId()
    };
    
    // Store in IndexedDB
    const db = await openOfflineDB();
    const transaction = db.transaction(['offline-requests'], 'readwrite');
    const store = transaction.objectStore('offline-requests');
    await store.add(requestData);
    
    // Schedule background sync
    self.registration.sync.register('era-audit-sync');
}

async function syncOfflineData() {
    const db = await openOfflineDB();
    const transaction = db.transaction(['offline-requests'], 'readonly');
    const store = transaction.objectStore('offline-requests');
    const requests = await store.getAll();
    
    let syncedCount = 0;
    
    for (const requestData of requests) {
        try {
            const response = await fetch(requestData.url, {
                method: requestData.method,
                headers: requestData.headers,
                body: requestData.body
            });
            
            if (response.ok) {
                // Remove successfully synced request
                const deleteTransaction = db.transaction(['offline-requests'], 'readwrite');
                const deleteStore = deleteTransaction.objectStore('offline-requests');
                await deleteStore.delete(requestData.id);
                syncedCount++;
                
                console.log('✅ Synced offline request:', requestData.url);
            }
        } catch (error) {
            console.log('❌ Failed to sync request:', requestData.url, error);
        }
    }
    
    // Notify clients about sync completion
    if (syncedCount > 0) {
        broadcastToClients({
            type: 'SYNC_COMPLETE',
            count: syncedCount
        });
    }
}

async function cleanupOldCaches() {
    const cacheNames = await caches.keys();
    const oldCaches = cacheNames.filter(name => 
        name.includes('era-audit') && !name.includes(CACHE_VERSION)
    );
    
    return Promise.all(
        oldCaches.map(cacheName => {
            console.log('🗑️ Deleting old cache:', cacheName);
            return caches.delete(cacheName);
        })
    );
}

async function invalidateRelatedCaches(url) {
    // Clear API cache for related endpoints
    const cache = await caches.open(API_CACHE);
    const keys = await cache.keys();
    
    const relatedKeys = keys.filter(key => {
        const keyUrl = new URL(key.url);
        return isRelatedEndpoint(url, keyUrl);
    });
    
    return Promise.all(
        relatedKeys.map(key => cache.delete(key))
    );
}

function isRelatedEndpoint(mutationUrl, cacheUrl) {
    // Define relationships between mutation and cache invalidation
    const relationships = {
        '/api/audits': ['/api/audits', '/api/dashboard'],
        '/api/templates': ['/api/templates', '/api/audits'],
        '/api/responses': ['/api/audits', '/api/responses']
    };
    
    for (const [pattern, invalidates] of Object.entries(relationships)) {
        if (mutationUrl.pathname.includes(pattern)) {
            return invalidates.some(inv => cacheUrl.pathname.includes(inv));
        }
    }
    
    return false;
}

async function prefetchUserData() {
    try {
        // Prefetch user profile and essential data
        const userResponse = await fetch('/api/user');
        if (userResponse.ok) {
            const cache = await caches.open(API_CACHE);
            cache.put('/api/user', userResponse);
        }
    } catch (error) {
        console.log('Prefetch failed:', error);
    }
}

function setupBackgroundSync() {
    // Register for background sync
    if ('sync' in self.registration) {
        console.log('🔄 Background sync available');
    }
}

function setupPeriodicSync() {
    // Setup periodic background sync for data updates
    if ('periodicSync' in self.registration) {
        self.registration.periodicSync.register('era-audit-data-sync', {
            minInterval: 24 * 60 * 60 * 1000 // 24 hours
        });
    }
}

function createOfflineAPIResponse(url) {
    const offlineData = {
        error: 'Offline',
        message: 'This request requires an internet connection',
        offline: true,
        url: url.pathname
    };
    
    return new Response(JSON.stringify(offlineData), {
        status: 503,
        headers: { 'Content-Type': 'application/json' }
    });
}

function createFallbackResponse(request) {
    if (request.destination === 'image') {
        // Return placeholder image
        return caches.match('/admin/assets/images/placeholder.png');
    }
    
    return new Response('Offline - Resource not available', {
        status: 503,
        headers: { 'Content-Type': 'text/plain' }
    });
}

async function updateCacheInBackground(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, response);
        }
    } catch (error) {
        // Silent fail for background updates
    }
}

function broadcastToClients(message) {
    self.clients.matchAll().then(clients => {
        clients.forEach(client => {
            client.postMessage(message);
        });
    });
}

async function openOfflineDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('ERAOfflineDB', 2);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        
        request.onupgradeneeded = () => {
            const db = request.result;
            
            if (!db.objectStoreNames.contains('offline-requests')) {
                const store = db.createObjectStore('offline-requests', { keyPath: 'id' });
                store.createIndex('timestamp', 'timestamp');
                store.createIndex('url', 'url');
            }
        };
    });
}

function generateUniqueId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

// Advanced PWA Features
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    } else if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_VERSION });
    } else if (event.data && event.data.type === 'CLEAR_CACHE') {
        clearAllCaches().then(() => {
            event.ports[0].postMessage({ success: true });
        });
    }
});

async function clearAllCaches() {
    const cacheNames = await caches.keys();
    return Promise.all(
        cacheNames.map(cacheName => caches.delete(cacheName))
    );
}
