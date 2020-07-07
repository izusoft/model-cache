<?php

/* IzuSoft\ModalCache helpers function
-------------------------------------------------------- */

use IzuSoft\ModalCache\CacheHelper;

if (! function_exists('modelTags')) {
    /**
     * @param array|string|null $classes
     * @return array
     */
    function modelTags($classes)
    {
        return app(CacheHelper::class)->getCacheTagsByClass($classes);
    }
}

