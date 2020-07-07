<?php /** @noinspection PhpOptionalBeforeRequiredParametersInspection */

namespace IzuSoft\ModalCache;

use BadMethodCallException;
use Cache;
use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;


class ModelCacheService
{
    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    private $config;
    /**
     * @var CacheManager|Repository
     */
    private $cacheStore;

    public function __construct()
    {
        $this->config = config('modal-cache', []);
        $this->setCacheStore(app('cache'));
    }

    /**
     * @param CacheManager $cache
     */
    public function setCacheStore(CacheManager $cache): void
    {
        $cacheStore = $cache;
        $store = data_get($this->config, 'store');
        if ($store) {
            $cacheStore = $cacheStore->store($store);
        }

        if (!is_subclass_of($cacheStore->getStore(), TaggableStore::class)) {
            throw new BadMethodCallException('This cache store does not support tagging.');
        }

        $this->cacheStore = $cacheStore;
    }


//    public function getTagsVersion(array $tags)
//    {
//        $lock = Cache::lock(implode(',',$tags). '_lock', 10);
//        try {
//            if ($lock->get()) {
//                //Critical section starts
//                Log::info('checkpoint 1'); // if it comes here
//
//                if ($this->cache($tags)->has(CacheConst::TAG_VERSION_KEY)) {
//                    Log::info('checkpoint 2'); // it should also come here.
//                    $post_data = Cache::store('memcached')->get('post_' . $post_id);
//                    //... // updating $post_data..
//                    Cache::put('post_' . $post_id, $post_data, 5 * 60);
//
//                } else {
//                    Cache::store('memcached')->put('post_' . $post_id, $initial, 5 * 60);
//                }
//            }
//            // Critical section ends
//        } finally {
//            $lock->release();
//        }
//
//        try {
//            return $this->cache($tags)->rememberForever(CacheConst::TAG_VERSION_KEY, static function () {
//                return microtime(true);
//            });
//        } catch (InvalidArgumentException $e) {
//            return null;
//        }
//        return $this->get($tags, CacheConst::TAG_VERSION_KEY);
//    }

    /**
     * @param string $key
     * @param array $tags
     * @param Closure $callback
     * @param int|null $ttl
     * @param float|null $beta
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getFromCache(string $key, array $tags = [], Closure $callback, int $ttl = null, ?float $beta = 1.0)
    {
        $t = microtime(true);

        /** test start */
        \LogMessage::debug('debug', 'start '.Carbon::now()->toString());
        $lock = $this->cacheStore->lock($key. '_lock', 10);
        if ($lock->get()) {
            \LogMessage::debug('debug', 'lock-true '.microtime(true));
        } else {
            \LogMessage::debug('debug', 'lock-false '.microtime(true));
        }
        /** test end */

        $cacheItem = $this->getCache($key, $tags);
        $recompute = !$cacheItem->isHit() || INF === $beta;
        if (!$recompute) {
            $recompute = $this->probabilisticEarlyExpiration($cacheItem, $beta);
        }

        if (!$recompute) {
            return $cacheItem->getValue();
        }

        static $save = null;

        /** test start */
        \LogMessage::debug('debug', 'Closure '.Carbon::now()->toString());
        /** test end */

        if (null === $save) {
            $save = Closure::bind(
                static function ($cacheStore, CacheItem $cacheItem, $value, float $startTime, int $ttl = null) {
                    $endTime = microtime(true);
                    $ttl = $ttl > 0 ? $ttl : CacheConst::DEFAULT_LIFE_TIME;
                    $expiry = $endTime + $ttl;
                    $executionTime = (float) sprintf('%.2e', $endTime - $startTime);

                    $cacheStore->setMultiple([
                        $cacheItem->getKeyStats() => [
                            CacheItem::STATS_EXPIRY => $expiry,
                            CacheItem::STATS_EXECUTION_TIME => $executionTime
                        ],
                        $cacheItem->getKeyValue() => $value
                    ]);

                    return $value;
                },
                null,
                CacheItem::class
            );
        }

        return $save($this->cacheStore, $cacheItem, $callback(), $t, $ttl);

//        $cacheLockTimeout = microtime(true) + (float) CacheConst::LOCK_TIMEOUT;
//
//        $lock = Cache::lock($key. '_lock', $cacheLockTimeout);
//        try {
//            do {
//                \LogMessage::info('debug', 'while');
//                if ($lock->get()) {
//                    //Critical section starts
//                    \LogMessage::info('debug', 'lock 1'); // if it comes here
//
//                    //                if ($this->cache($tags)->has(CacheConst::TAG_VERSION_KEY)) {
//                    //                    Log::info('checkpoint 2'); // it should also come here.
//                    //                    $post_data = Cache::store('memcached')->get('post_' . $post_id);
//                    //                    //... // updating $post_data..
//                    //                    Cache::put('post_' . $post_id, $post_data, 5 * 60);
//                    //
//                    //                } else {
//                    //                    Cache::store('memcached')->put('post_' . $post_id, $initial, 5 * 60);
//                    //                }
//                }
//                //заблокироваться не удалось ждем 50-100 мсек
//                /** @noinspection RandomApiMigrationInspection */
//                $sleepTime = rand(50000, 100000);
//                usleep($sleepTime);
//            } while (microtime(true) <= $cacheLockTimeout);
//            // Critical section ends
//        } finally {
//            $lock->release();
//        }

    }

    protected function getCache(string $key, array $tags = []): CacheItem
    {
        $cacheItem = new CacheItem();

        $keyStats = $this->cacheStore->tags($tags)->taggedItemKey($keyStats);
        $keyValue = $this->cacheStore->tags([CacheConst::DEFAULT_CACHE_TAG])->taggedItemKey($keyValue);

        $cacheItem->setKeyStats($keyStats);
        $cacheItem->setKeyValue($keyValue);

        $result = $this->cacheStore->getMultiple([$keyStats, $keyValue]);

        $stats = $result[$keyStats] ?? null;
        $value = $result[$keyValue] ?? null;

        $executionTime = $stats[CacheItem::STATS_EXECUTION_TIME] ?? false;
        $expiry = $stats[CacheItem::STATS_EXPIRY] ?? false;

        $cacheItem->setIsHit($executionTime && $expiry && $expiry > microtime(true));
        $cacheItem->setExpiry($expiry ?: null);
        $cacheItem->setExecutionTime($executionTime ?: null);
        $cacheItem->setValue($value);

        return $cacheItem;
    }

    /**
     * @param CacheItem $cacheItem
     * @param float|null $beta
     * @return bool
     */
    protected function probabilisticEarlyExpiration(CacheItem $cacheItem, ?float $beta = 1.0): bool
    {
        try {
            $beta = $beta ?? 1.0;
            if (0 > $beta) {
                throw new InvalidArgumentException("Argument `$beta` must be a positive number, {$beta} given.");
            }
            $randomFactor = log(random_int(1, PHP_INT_MAX) / PHP_INT_MAX);

            $now = microtime(true);
            $expiry = $cacheItem->getExpiry() ?? $now + (float) CacheConst::DEFAULT_LIFE_TIME;
            return $expiry <= $now - $beta * $cacheItem->getExecutionTime() * $randomFactor;
        }
        catch (Exception $e) {
            return false;
        }
    }
}
