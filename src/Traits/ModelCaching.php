<?php
namespace IzuSoft\ModalCache\Traits;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use IzuSoft\ModalCache\CachedQueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait ModelCaching
{
    use MagicMethod;

    /**
     * {@inheritdoc}
     */
    protected function newBaseQueryBuilder()
    {
        /** @var Connection $connection */
        $connection = $this->getConnection();
        if (! $this->isCachableModel()) {
            $this->isCachable = false;

            return $connection->query();
        }

        $cachedQueryBuilder = new CachedQueryBuilder(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor()
        );

        /** @var Model $model */
        $model = $this;
        $cachedQueryBuilder->setModel($model);

        return $cachedQueryBuilder;
    }

    /**
     * @param EloquentBuilder $query
     * @return EloquentBuilder
     */
    public function scopeDisableCache(EloquentBuilder $query) : EloquentBuilder
    {
        if ($this->isCachableModel()) {
            $this->isCachable = false;
        }

        return $query;
    }

    /**
     * @param EloquentBuilder $query
     * @param array|string $tags
     * @return EloquentBuilder
     */
    public function scopeWithCacheTags(EloquentBuilder $query, $tags) : EloquentBuilder
    {
        if (empty($tags) || !$this->isCachableModel()) {
            return $query;
        }
        /** @var CachedQueryBuilder $queryBuilder */
        $queryBuilder = $query->getQuery();
        $queryBuilder->setCustomTags(app('modal-cache-helper')->makeTags($tags));
        return $query;
    }
}
