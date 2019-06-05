<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInited86654456b4759dad3bb97628fd8e0a
{
    public static $files = array (
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Polyfill\\Mbstring\\' => 26,
        ),
        'M' => 
        array (
            'Michelf\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Michelf\\' => 
        array (
            0 => __DIR__ . '/..' . '/michelf/php-markdown/Michelf',
        ),
    );

    public static $prefixesPsr0 = array (
        'T' => 
        array (
            'Twig_' => 
            array (
                0 => __DIR__ . '/..' . '/twig/twig/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInited86654456b4759dad3bb97628fd8e0a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInited86654456b4759dad3bb97628fd8e0a::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInited86654456b4759dad3bb97628fd8e0a::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}