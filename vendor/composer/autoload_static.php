<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit704b74d26da5311f34895fcbb79e741d
{
    public static $prefixLengthsPsr4 = array (
        'U' => 
        array (
            'Uzsoftic\\LaravelTelegramBot\\' => 28,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Uzsoftic\\LaravelTelegramBot\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit704b74d26da5311f34895fcbb79e741d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit704b74d26da5311f34895fcbb79e741d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit704b74d26da5311f34895fcbb79e741d::$classMap;

        }, null, ClassLoader::class);
    }
}
