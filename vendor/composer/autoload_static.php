<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitcdae1acd86f3536931a8e2c7c58cf7ce
{
    public static $files = array (
        '9b77ddcfb14408a32f5aaf74e0a11694' => __DIR__ . '/..' . '/yahnis-elsts/plugin-update-checker/load-v5p1.php',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInitcdae1acd86f3536931a8e2c7c58cf7ce::$classMap;

        }, null, ClassLoader::class);
    }
}
