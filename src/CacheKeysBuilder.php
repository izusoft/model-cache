<?php
namespace IzuSoft\ModalCache;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use IzuSoft\ModalCache\Traits\CachePrefixing;

class CacheKeysBuilder
{
    use CachePrefixing;

    /**
     * @var array
     */
    private $config;

    public function __construct()
    {
        $this->config = config('modal-cache', []);
    }

    /**
     * @param $builder
     * @return string
     */
    public function make($builder): string
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getQueryBuilder($builder);
        if ($queryBuilder === null) {
            return '';
        }
        return $this->getCachePrefix($this->config)
            . sha1($queryBuilder->toSql().serialize($queryBuilder->getBindings()));
    }

    /**
     * @param $builder
     * @return QueryBuilder|null
     */
    protected function getQueryBuilder($builder): ?QueryBuilder
    {
        if ($builder instanceof EloquentBuilder) {
            return $builder->getQuery();
        }
        if ($builder instanceof QueryBuilder) {
            return $builder;
        }
        return null;
    }
}
