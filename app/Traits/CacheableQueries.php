<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheableQueries
{
    /**
     * Cache a query result with automatic invalidation
     */
    public function cacheQuery($key, $ttl, $callback, $tags = [])
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate cache by pattern or specific keys
     */
    public function invalidateCache($pattern)
    {
        if (is_array($pattern)) {
            foreach ($pattern as $key) {
                Cache::forget($key);
            }
        } else {
            Cache::forget($pattern);
        }
    }

    /**
     * Get cache key for model instance
     */
    public function getCacheKey($prefix, $id = null)
    {
        $id = $id ?? $this->id;
        return "{$prefix}_{$id}";
    }
}
