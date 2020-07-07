<?php
namespace IzuSoft\ModalCache;

class CacheItem
{
    /**
     * References the Unix timestamp stating when the item will expire, as integer.
     */
    public const STATS_EXPIRY = 1;
    /**
     * References the time the item took to be created, as float.
     */
    public const STATS_EXECUTION_TIME = 2;

    /** @var bool */
    protected bool $isHit = false;
    /** @var float */
    protected float $extractionAt;
    /** @var mixed */
    protected $value;
    /** @var float */
    protected ?float $expiry;
    /** @var float */
    protected ?float $executionTime;
    /** @var string */
    protected string $keyStats;
    /** @var string */
    protected string $keyValue;

    public function __construct()
    {
        $this->extractionAt = microtime(true);
    }

    /**
     * @return bool
     */
    public function isHit(): bool
    {
        return $this->isHit;
    }

    /**
     * @param bool $isHit
     * @return CacheItem
     */
    public function setIsHit(bool $isHit): CacheItem
    {
        $this->isHit = $isHit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return CacheItem
     */
    public function setValue($value): CacheItem
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getExpiry(): ?float
    {
        return $this->expiry;
    }

    /**
     * @param float|null $expiry
     * @return CacheItem
     */
    public function setExpiry(?float $expiry): CacheItem
    {
        $this->expiry = $expiry;
        return $this;
    }

    /**
     * @return float
     */
    public function getExecutionTime(): float
    {
        return $this->executionTime ?? microtime(true) - $this->extractionAt;
    }

    /**
     * @param float|null $executionTime
     * @return CacheItem
     */
    public function setExecutionTime(?float $executionTime): CacheItem
    {
        $this->executionTime = $executionTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getKeyStats(): string
    {
        return $this->keyStats;
    }

    /**
     * @param string $keyStats
     * @return CacheItem
     */
    public function setKeyStats(string $keyStats): CacheItem
    {
        $this->keyStats = $keyStats;
        return $this;
    }

    /**
     * @return string
     */
    public function getKeyValue(): string
    {
        return $this->keyValue;
    }

    /**
     * @param string $keyValue
     * @return CacheItem
     */
    public function setKeyValue(string $keyValue): CacheItem
    {
        $this->keyValue = $keyValue;
        return $this;
    }
}
