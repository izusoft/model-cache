<?php
namespace IzuSoft\ModalCache\Traits;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Trait Caching
 * @package IzuSoft\ModalCache\Traits
 */
trait Caching
{
    protected $isCachable = true;

    /**
     * @param array $tags
     * @return Repository|CacheManager
     */
    public function cache(array $tags = [])
    {
        $cache = app('cache');
        $store = config('modal-cache.store');

        if ($store) {
            $cache = $cache->store($store);
        }

        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            $cache = $cache->tags($tags);
        }

        return $cache;
    }

    /**
     * @return bool
     */
    public function isCachableModel(): bool
    {
        try {
            $enabledCache = config('modal-cache.enabled_cache', false);

            return $enabledCache && $this->isCachable;
        }
        catch (Throwable $throwable) {
            return false;
        }
    }

    /**
     * @param array $tags
     * @return bool
     */
    public function flushCache(array $tags = []): bool
    {
        $model = self::getModel() instanceof Model
            ? self::getModel()
            : $this;

        if (count($tags) === 0) {
            $tags = app('modal-cache-tags')->getTags($model);
        }
        return $this->cache($tags)->flush();
    }
}
