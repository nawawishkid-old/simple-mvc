<?php

namespace Core;

class Config
{
    private static $configDirectory;

    private static $configs = [];

    public static function init()
    {
        self::$configDirectory = APP_ROOT . '/configs';
    }

    public static function dir(string $path)
    {
        if (! \file_exists($path) || ! \is_dir($path)) {
            throw new \Exception("Give directory path does not exists or not a directory: $path", 1);
            
        }

        self::$configDirectory = $path;
    }

    public static function get(string $configName, $fallback = null)
    {
        $parsedConfig = self::parseConfig($configName);

        $config = empty($parsedConfig)
                    ? $fallback
                    : $parsedConfig;

        return $config;
    }

    public static function loadModule(string $configModule)
    {
        $moduleFile = self::$configDirectory . '/' . $configModule . '.php';

        if (! file_exists($moduleFile)) {
            throw new \Exception("Config module does not exists: $configModule", 1);
            
        }

        $configs = [
            $configModule => include $moduleFile
        ];

        self::updateConfigs($configs);
    }

    public static function loadFile(string $filePath)
    {
        if (! file_exists($filePath)) {
            throw new \Exception("Given config file path does not exists: $filePath", 1);
            
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'yml':
            case 'yaml':
                $configs = \yaml_parse_file($filePath);
                break;
            
            default:
                $configs = [];
                break;
        }

        self::$configs = \array_merge(self::$configs, $configs);
    }

    public static function isLoaded(string $moduleName)
    {
        return \in_array($moduleName, self::$configs);
    }

    private static function updateConfigs(array $configs)
    {
        self::$configs = \array_merge(self::$configs, $configs);
    }

    private static function parseConfig(string $configName)
    {
        $config = self::$configs;

        foreach (explode('.', $configName) as $name) {
            if (empty($config[$name])) {
                return null;
            }

            $config = $config[$name];
        }

        return $config;
    }
}