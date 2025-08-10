<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PWAController extends Controller
{
    /**
     * Handle push notification subscriptions
     */
    public function subscribeToPush(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|url',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string'
        ]);

        try {
            // Store subscription in database
            $user = auth()->user();
            
            $subscription = $user->pushSubscriptions()->updateOrCreate(
                ['endpoint' => $request->endpoint],
                [
                    'p256dh' => $request->keys['p256dh'],
                    'auth' => $request->keys['auth'],
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip()
                ]
            );

            Log::info('Push subscription created', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Push subscription saved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Push subscription failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save push subscription'
            ], 500);
        }
    }

    /**
     * Send push notification to user
     */
    public function sendPushNotification(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'url' => 'nullable|url',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);

        try {
            $payload = [
                'title' => $request->title,
                'body' => $request->body,
                'icon' => '/admin/assets/images/logo-mini.png',
                'badge' => '/admin/assets/images/badge.png',
                'url' => $request->url ?? '/admin/dashboard',
                'timestamp' => now()->toISOString()
            ];

            // Get target users
            if ($request->has('user_ids')) {
                $users = \App\Models\User::whereIn('id', $request->user_ids)->get();
            } else {
                $users = \App\Models\User::where('is_active', true)->get();
            }

            $sentCount = 0;
            foreach ($users as $user) {
                $subscriptions = $user->pushSubscriptions;
                
                foreach ($subscriptions as $subscription) {
                    if ($this->sendPushToSubscription($subscription, $payload)) {
                        $sentCount++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Push notification sent to {$sentCount} devices"
            ]);

        } catch (\Exception $e) {
            Log::error('Push notification sending failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send push notifications'
            ], 500);
        }
    }

    /**
     * Analytics endpoint for PWA events
     */
    public function analytics(Request $request): JsonResponse
    {
        $request->validate([
            'event' => 'required|string|max:100',
            'data' => 'nullable|array',
            'timestamp' => 'required|integer'
        ]);

        try {
            // Store analytics data
            $analyticsData = [
                'user_id' => auth()->id(),
                'event' => $request->event,
                'data' => $request->data ?? [],
                'timestamp' => $request->timestamp,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'created_at' => now()
            ];

            // Store in cache for batch processing
            $cacheKey = 'pwa_analytics_' . date('Y-m-d-H');
            $existingData = Cache::get($cacheKey, []);
            $existingData[] = $analyticsData;
            Cache::put($cacheKey, $existingData, now()->addHours(25));

            // Log important events
            if (in_array($request->event, ['pwa_installed', 'pwa_install_accepted'])) {
                Log::info('PWA Event: ' . $request->event, $analyticsData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Analytics data recorded'
            ]);

        } catch (\Exception $e) {
            Log::error('Analytics recording failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record analytics'
            ], 500);
        }
    }

    /**
     * Connectivity check endpoint
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'timestamp' => now()->toISOString(),
            'server_time' => time()
        ]);
    }

    /**
     * Get PWA configuration
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'version' => config('app.version', '1.0.0'),
            'vapid_public_key' => config('services.vapid.public_key'),
            'features' => [
                'push_notifications' => true,
                'offline_support' => true,
                'background_sync' => true,
                'install_prompt' => true
            ],
            'cache_strategy' => [
                'static_cache_duration' => 86400, // 24 hours
                'api_cache_duration' => 3600,     // 1 hour
                'dynamic_cache_duration' => 1800  // 30 minutes
            ]
        ]);
    }

    /**
     * Handle offline data sync
     */
    public function syncOfflineData(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.type' => 'required|in:response,draft,attachment',
            'data.*.payload' => 'required|array',
            'data.*.timestamp' => 'required|integer'
        ]);

        try {
            $syncResults = [];
            
            foreach ($request->data as $item) {
                $result = $this->processOfflineItem($item);
                $syncResults[] = $result;
            }

            $successCount = count(array_filter($syncResults, fn($r) => $r['success']));
            $totalCount = count($syncResults);

            return response()->json([
                'success' => true,
                'message' => "Synced {$successCount} of {$totalCount} items",
                'results' => $syncResults
            ]);

        } catch (\Exception $e) {
            Log::error('Offline data sync failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync offline data'
            ], 500);
        }
    }

    /**
     * Get cache statistics
     */
    public function cacheStats(): JsonResponse
    {
        try {
            $stats = [
                'cache_size' => $this->getCacheSize(),
                'cached_endpoints' => $this->getCachedEndpoints(),
                'offline_items' => $this->getOfflineItemsCount(),
                'last_sync' => $this->getLastSyncTime(),
                'storage_quota' => $this->getStorageQuota()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cache statistics'
            ], 500);
        }
    }

    /**
     * Clear PWA cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            // Clear relevant application caches
            Cache::flush();
            
            // Clear user-specific caches
            $user = auth()->user();
            Cache::forget("user_templates_{$user->id}");
            Cache::forget("user_audits_{$user->id}");
            Cache::forget("user_permissions_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Cache clear failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache'
            ], 500);
        }
    }

    /**
     * Private helper methods
     */
    private function sendPushToSubscription($subscription, $payload): bool
    {
        try {
            // Use a push notification library like web-push
            // This is a simplified example - implement with actual push library
            
            return true; // Return true if successful
        } catch (\Exception $e) {
            Log::error('Push to subscription failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function processOfflineItem($item): array
    {
        try {
            switch ($item['type']) {
                case 'response':
                    return $this->processOfflineResponse($item['payload']);
                case 'draft':
                    return $this->processOfflineDraft($item['payload']);
                case 'attachment':
                    return $this->processOfflineAttachment($item['payload']);
                default:
                    return ['success' => false, 'error' => 'Unknown item type'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function processOfflineResponse($payload): array
    {
        // Process offline audit response
        $response = \App\Models\Response::create([
            'audit_id' => $payload['audit_id'],
            'question_id' => $payload['question_id'],
            'answer' => $payload['answer'],
            'attachment_id' => $payload['attachment_id'] ?? null,
            'created_by' => auth()->id()
        ]);

        return [
            'success' => true,
            'id' => $response->id,
            'type' => 'response'
        ];
    }

    private function processOfflineDraft($payload): array
    {
        // Process offline draft
        // Implementation depends on your draft system
        return ['success' => true, 'type' => 'draft'];
    }

    private function processOfflineAttachment($payload): array
    {
        // Process offline attachment
        // Implementation depends on your attachment system
        return ['success' => true, 'type' => 'attachment'];
    }

    private function getCacheSize(): int
    {
        // Return estimated cache size
        return 0; // Implement actual calculation
    }

    private function getCachedEndpoints(): array
    {
        // Return list of cached API endpoints
        return [];
    }

    private function getOfflineItemsCount(): int
    {
        // Return count of offline items
        return 0;
    }

    private function getLastSyncTime(): ?string
    {
        // Return last sync timestamp
        return Cache::get('last_pwa_sync_' . auth()->id());
    }

    private function getStorageQuota(): array
    {
        // Return storage quota information
        return [
            'quota' => 0,
            'usage' => 0,
            'available' => 0
        ];
    }
}
