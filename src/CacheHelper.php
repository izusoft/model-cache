<?php
namespace IzuSoft\ModalCache;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Str;

class CacheHelper
{
    /**
     * @var array
     */
    private $config;

    /**
     * CacheTagsBuilder constructor.
     */
    public function __construct()
    {
        $this->config = config('modal-cache', []);
    }

    /**
     * @param array|string|object|null $classes
     * @return array
     */
    public function makeTags($classes): array
    {
        if (empty($classes)) {
            return [];
        }
        $arrClasses = $classes;
        if (!is_array($classes)) {
            $arrClasses = is_string($classes) ? $this->convertToArray($classes) : [$classes];
        }

        $tags = [];
        foreach ($arrClasses as $class) {
            if (is_subclass_of($class, Model::class)) {
                $tags[] = $this->getCachePrefix()
                    . Str::slug(is_object($class) ? get_class($class) : $class);
            }
        }
        return array_unique($tags);
    }

    /**
     * @param $builder
     * @return string
     */
    public function makeKey($builder): string
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getQueryBuilder($builder);
        if ($queryBuilder === null) {
            return '';
        }
        return $this->getCachePrefix()
            . sha1($queryBuilder->toSql().serialize($queryBuilder->getBindings()));
    }

    /**
     * @return string
     */
    public function getCachePrefix() : string
    {
        $cachePrefix = data_get($this->config, 'prefix');
        return !empty($cachePrefix) && is_string($cachePrefix) ? $cachePrefix.':' : '';
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

    /**
     * @param string $pipeString
     * @return array|string
     */
    protected function convertToArray(string $pipeString)
    {
        $pipeString = trim($pipeString);

        if (false === strpos($pipeString, '|')) {
            return [$pipeString];
        }

        $quoteCharacter = $pipeString[0];
        $endCharacter = substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return explode('|', $pipeString);
        }

        if (! in_array($quoteCharacter, ["'", '"'])) {
            return explode('|', $pipeString);
        }

        return explode('|', trim($pipeString, $quoteCharacter));
    }
}
