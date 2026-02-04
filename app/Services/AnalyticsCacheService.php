<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class AnalyticsCacheService
{
    /**
     * Cache TTL configurations (in seconds)
     */
    protected const TTL_REALTIME = 300; // 5 minutes

    protected const TTL_HOURLY = 3600; // 1 hour

    protected const TTL_DAILY = 86400; // 1 day

    protected const TTL_WEEKLY = 604800; // 7 days

    /**
     * Remember analytics data with automatic cache key prefixing
     */
    public function remember(string $key, int $ttl, Closure $callback): mixed
    {
        return Cache::remember($this->getCacheKey($key), $ttl, $callback);
    }

    /**
     * Remember with real-time TTL (5 minutes)
     */
    public function rememberRealtime(string $key, Closure $callback): mixed
    {
        return $this->remember($key, self::TTL_REALTIME, $callback);
    }

    /**
     * Remember with hourly TTL (1 hour)
     */
    public function rememberHourly(string $key, Closure $callback): mixed
    {
        return $this->remember($key, self::TTL_HOURLY, $callback);
    }

    /**
     * Remember with daily TTL (1 day)
     */
    public function rememberDaily(string $key, Closure $callback): mixed
    {
        return $this->remember($key, self::TTL_DAILY, $callback);
    }

    /**
     * Remember with weekly TTL (7 days)
     */
    public function rememberWeekly(string $key, Closure $callback): mixed
    {
        return $this->remember($key, self::TTL_WEEKLY, $callback);
    }

    /**
     * Get analytics data from cache
     */
    public function get(string $key): mixed
    {
        return Cache::get($this->getCacheKey($key));
    }

    /**
     * Store analytics data in cache
     */
    public function put(string $key, mixed $value, int $ttl): bool
    {
        return Cache::put($this->getCacheKey($key), $value, $ttl);
    }

    /**
     * Forget specific analytics cache
     */
    public function forget(string $key): bool
    {
        return Cache::forget($this->getCacheKey($key));
    }

    /**
     * Flush all analytics cache or by pattern
     */
    public function flush(?string $pattern = null): void
    {
        if ($pattern === null) {
            // Flush all analytics cache
            $this->flushByPattern('analytics.*');
        } else {
            $this->flushByPattern("analytics.{$pattern}");
        }
    }

    /**
     * Flush CRM analytics cache
     */
    public function flushCRM(): void
    {
        $this->flushByPattern('analytics.crm.*');
    }

    /**
     * Flush Project analytics cache
     */
    public function flushProject(): void
    {
        $this->flushByPattern('analytics.project.*');
    }

    /**
     * Get cache key with analytics prefix
     */
    protected function getCacheKey(string $key): string
    {
        return "analytics.{$key}";
    }

    /**
     * Flush cache by pattern
     */
    protected function flushByPattern(string $pattern): void
    {
        // For Redis driver
        if (config('cache.default') === 'redis') {
            $keys = Cache::getRedis()->keys($pattern);
            if (! empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        } else {
            // For other drivers, we'll need to track keys manually
            // This is a limitation of non-Redis cache drivers
            Cache::flush();
        }
    }

    /**
     * Check if cache exists
     */
    public function has(string $key): bool
    {
        return Cache::has($this->getCacheKey($key));
    }

    /**
     * Get or set cache with default TTL
     */
    public function rememberForever(string $key, Closure $callback): mixed
    {
        return Cache::rememberForever($this->getCacheKey($key), $callback);
    }

    /**
     * Increment cache value
     */
    public function increment(string $key, int $value = 1): int|bool
    {
        return Cache::increment($this->getCacheKey($key), $value);
    }

    /**
     * Decrement cache value
     */
    public function decrement(string $key, int $value = 1): int|bool
    {
        return Cache::decrement($this->getCacheKey($key), $value);
    }
}
