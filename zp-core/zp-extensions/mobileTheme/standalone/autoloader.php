<?php
$dir = dirname(__FILE__);

spl_autoload_register(function ($class) use ($dir) {
    $classMap = [
        // "mobiledetect/mobiledetectlib"
        "Detection\Cache\Cache" => $dir . "/../src/Cache/Cache.php",
        "Detection\Cache\CacheException" => $dir . "/../src/Cache/CacheException.php",
        "Detection\Cache\CacheItem" => $dir . "/../src/Cache/CacheItem.php",
        "Detection\Exception\MobileDetectException" => $dir . "/../src/Exception/MobileDetectException.php",
        "Detection\MobileDetect" => $dir . "/../src/MobileDetect.php",

        // "psr/simple-cache"
        "Psr\SimpleCache\CacheException" => $dir . "/deps/simple-cache/src/CacheException.php",
        "Psr\SimpleCache\CacheInterface" => $dir . "/deps/simple-cache/src/CacheInterface.php",
        "Psr\SimpleCache\InvalidArgumentException" => $dir . "/deps/simple-cache/src/InvalidArgumentException.php",

        // "psr/cache"
        "Psr\Cache\CacheException" => $dir . "/deps/cache/src/CacheException.php",
        "Psr\Cache\CacheItemInterface" => $dir . "/deps/cache/src/CacheItemInterface.php",
        "Psr\Cache\CacheItemPoolInterface" => $dir . "/deps/cache/src/CacheItemPoolInterface.php",
        "Psr\Cache\InvalidArgumentException" => $dir . "/deps/cache/src/InvalidArgumentException.php",
    ];

    $fileFound = $classMap[$class] ?? false;

    if ($fileFound) {
        require $fileFound;
        return true;
    }

    return false;
});
