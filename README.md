# laravel cache

## Установка
в `composer.json` файл добавить код
```$xslt
"repositories": [
    {
        "type": "vcs",
        "url": "git@gitlab.com:cut_code/laravel-cache.git"
    }
],
"require": {
    "cut_code/laravel-cache": "^1.0"
},
```
Опубликовать конфиги в папку `/config` можно командой   
`php artisan vendor:publish --tag=cache-config`
