<?php
namespace IzuSoft\ModalCache;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Event;
use LogMessage;

/**
 * Class ModalCacheServiceProvider
 * @package IzuSoft\ModalCache
 */
class ModalCacheServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen([
            'eloquent.flush: *',
            'eloquent.saved: *',
            'eloquent.deleted: *',
            'eloquent.forceDeleted: *',
            'eloquent.restored: *',
            'eloquent.belongsToManyAttached: *',
            'eloquent.belongsToManyDetached: *',
        ], static function ($event, $args) {
//            $enabledCache = config('cache-keys.enabled-redis-cache', true);
//            $debug = config('cache-keys.debug-events', false);
//
//            preg_match('/^eloquent\.(.+):\s+(.+)/', $event, $matches);
//            $eventName = $matches[1] ?? null;
//
//            $tag = $matches[2] ?? null;
//            $tagCachable = self::isModelCachable($tag);
//
//            $debugData = [
//                'event' => $event,
//                'event_name' => $eventName,
//                'tag' => $tag,
//                'tag_cachable' => $tagCachable ? 'true' : 'false',
//            ];
//
//            if ($tagCachable === true && $enabledCache === true) {
//                redisCache()->flushTag($tag);
//            }
//
//            $relationTag = null;
//            if ($eventName === 'belongsToManyAttached' || $eventName === 'belongsToManyDetached') {
//                try {
//                    $relation = isset($args[0]) && is_string($args[0]) ? $args[0] : null;
//                    $parentModel = isset($args[1]) && is_object($args[1]) ? $args[1] : null;
//                    if ($relation && $parentModel && method_exists($parentModel, $relation)) {
//                        $relationTag = get_class($parentModel->$relation()->getRelated());
//                    }
//                }
//                catch (\Exception $e) {
//                    $relationTag = null;
//                }
//                $relationTagCachable = self::isModelCachable($relationTag);
//
//                $debugData['relation_tag'] = $relationTag;
//                $debugData['relation_tag_cachable'] = $relationTagCachable ? 'true' : 'false';
//
//                if ($relationTagCachable === true && $enabledCache === true) {
//                    redisCache()->flushTag($relationTag);
//                }
//            }
//
//            if ($debug) {
//                \LogMessage::debug('debug', $debugData);
//            }
        });
    }

    /**
     * @param string|null $tag
     * @return bool
     */
    protected static function isModelCachable(string $tag = null): bool
    {
        $tag = '\\'.$tag;
        $class = class_exists($tag, false) ? new $tag() : null;

        return $class instanceof Model && method_exists($class, 'isCachableModel') && $class->isCachableModel();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Load the default config values
        $configFile = __DIR__ . '/../config/modal-cache.php';
        $this->mergeConfigFrom($configFile, 'modal-cache');
        // Publish the config file
        $this->publishes([
            $configFile => config_path('modal-cache.php'),
        ], 'modal-cache');


        //$this->app->extend('cache', static function ($cache, $app) {
            //return new \CutCode\Cache\DecoratorCacheManager($app);
        //});

        //$this->app->bind('redisService', static function ($app, array $parameters = []) {
            //return new \CutCode\Cache\RedisCacheService($parameters['type'] ?? 'cache');
        //});


        // register commands
        $this->registerCommands();
    }

    /**
     * Commands
     */
    private function registerCommands(): void
    {
        //$this->commands(\CutCode\Cache\Commands\RedisClear::class);
    }
}
