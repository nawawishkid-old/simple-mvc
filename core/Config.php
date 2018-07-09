<?php

namespace Core;

class Config
{
    private $configDirectory;

    private $configs = [];

    public function __construct()
    {
        $this->configDirectory = APP_ROOT . '/configs';
    }

    public function dir(string $path)
    {
        if (! \file_exists($path) || ! \is_dir($path)) {
            throw new \Exception("Give directory path does not exists or not a directory: $path", 1);
            
        }

        $this->configDirectory = $path;

        return $this;
    }

    public function get(string $configName, $fallback = null)
    {
        $parsedConfig = $this->parseConfig($configName);

        $config = empty($parsedConfig)
                    ? $fallback
                    : $parsedConfig;

        return $config;
    }

    public function loadModule(string $configModule)
    {
        $moduleFile = $this->configDirectory . '/' . $configModule . '.php';

        if (! file_exists($moduleFile)) {
            throw new \Exception("Config module does not exists: $configModule", 1);
            
        }

        $configs = [
            $configModule => include $moduleFile
        ];

        $this->updateConfigs($configs);

        // var_dump($this->configs);

        return $this;
    }

    public function loadFile(string $filePath)
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

        $this->configs = \array_merge($this->configs, $configs);
    }

    private function updateConfigs(array $configs)
    {
        $this->configs = \array_merge($this->configs, $configs);

        return $this;
    }

    private function parseConfig(string $configName)
    {
        $config = $this->configs;

        foreach (explode('.', $configName) as $name) {
            if (empty($config[$name])) {
                return null;
            }

            $config = $config[$name];
        }

        return $config;
    }
}