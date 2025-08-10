// Simple Service Worker - Redirects to Advanced SW
// This file exists for compatibility but the real work is done by sw-advanced.js

console.log('Simple SW loaded - delegating to advanced SW');

// Import the advanced service worker
self.importScripts('/sw-advanced.js');