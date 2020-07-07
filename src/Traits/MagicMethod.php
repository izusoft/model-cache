<?php
namespace IzuSoft\ModalCache\Traits;

/**
 * Trait MagicMethod
 * @package IzuSoft\ModalCache\Traits
 */
trait MagicMethod
{
    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($key)
    {
        if ($key === 'cacheTimeSeconds') {
            return $this->$key ?? 300; // 5 мин
        }

        return parent::__get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($key, $value)
    {
        if ($key === 'cacheTimeSeconds') {
            $this->cacheTimeSeconds = $value;
        }

        parent::__set($key, $value);
    }
}
