<?php
namespace IzuSoft\ModalCache;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Class CachedQueryBuilder
 * @package IzuSoft\ModalCache
 */
class CachedQueryBuilder extends QueryBuilder
{
    /**
     * The model being queried.
     *
     * @var Model
     */
    protected $model;
    /**
     * @var array
     */
    protected $customTags = [];

    /**
     * {@inheritdoc}
     */
    public function get($columns = ['*'])
    {
        if (!$this->isAvailableCache()) {
            return parent::get($columns);
        }
        /** @var ModelCacheService $cacheService */
        $cacheService = app('modal-cache-service');
        /** @var CacheHelper $cacheHelper */
        $cacheHelper = app('modal-cache-helper');

        $customTags = $this->getCustomTags();
        $tags = array_unique(
            array_merge(
                $cacheHelper->makeTags($this->getModel()),
                $customTags
            )
        );
        $key = $cacheHelper->makeKey($this);
        $ttl = $this->getCacheTime();
        $beta = 1.0;

        return $cacheService->getFromCache($key, $tags, function () use ($columns) {
            return parent::get($columns);
        }, $ttl, $beta);
    }

    /**
     * Set a model instance for the model being queried.
     *
     * @param Model $model
     * @return $this
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return Model|null
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * @param array $customTags
     * @return $this
     */
    public function setCustomTags(array $customTags = []): self
    {
        $this->customTags = $customTags;

        return $this;
    }

    /**
     * @return array
     */
    public function getCustomTags(): array
    {
        return $this->customTags;
    }

    /**
     * @return int
     */
    protected function getCacheTime(): int
    {
        $cacheTimeSeconds = 0;
        $model = $this->getModel();
        if ($model instanceof Model && property_exists($model, 'cacheTimeSeconds')) {
            $cacheTimeSeconds = $model->cacheTimeSeconds;
        }

        return $cacheTimeSeconds > 0 ? $cacheTimeSeconds : CacheConst::DEFAULT_LIFE_TIME;
    }

    /**
     * @return bool
     */
    protected function isAvailableCache(): bool
    {
        return property_exists($this, 'model')
            && $this->model
            && method_exists($this->model, 'isCachableModel')
            && $this->model->isCachableModel();
    }
}
