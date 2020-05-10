<?php

/* IzuSoft\ModalCache helpers function
-------------------------------------------------------- */

use Illuminate\Support\Collection;


if (! function_exists('cache_keys')) {
    /**
     * @param string $keyName
     * @param array $replace
     * @param string $configName
     * @return \Illuminate\Config\Repository|mixed
     */
    function cache_keys(string $keyName, array $replace = [], string $configName = 'cache-keys')
    {
        $keyString = config($configName.'.'.$keyName, '');

        if (empty($replace)) {
            return $keyString;
        }

        $replace = (new Collection($replace))->sortBy(static function ($value, $key) {
            return mb_strlen($key) * -1;
        })->all();

        foreach ($replace as $key => $value) {
            $keyString = str_replace(
                ['{' . $key . '}', '{' . \Str::upper($key) . '}', '{' . \Str::ucfirst($key) . '}'],
                [$value, \Str::upper($value), \Str::ucfirst($value)],
                $keyString
            );
        }

        return $keyString;
    }
}

