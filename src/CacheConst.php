<?php
namespace IzuSoft\ModalCache;

interface CacheConst
{
    /** @var int время жизни кеша по умолчанию */
    public const DEFAULT_LIFE_TIME = 60 * 60;
    /** @var string тэг кеша по умолчанию */
    public const DEFAULT_CACHE_TAG = 'sql';
    /** @var string ключ версионности тега */
    public const STATS_PREFIX = '_stats';
    /** @var int максимальное время жизни блокировки */
    public const LOCK_TIMEOUT = 5;
}
