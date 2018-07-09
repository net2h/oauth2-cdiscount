<?php


namespace Net2h\OauthCdiscount;

use ArrayAccess;
use InvalidArgumentException;


class Configuration implements ArrayAccess
{
    
    protected $config;

    protected static $fileConfig;

    const SOCIAL_CONF = __DIR__ . "/../config/config.conf.ini";

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    
    public function get($key, $default = null): ?array
    {
        $config = $this->config;

        if (is_null($key)) {
            return $config;
        }
        if (isset($config[$key])) {
            return $config[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    
    public function set($key, $value): array
    {
        if (is_null($key)) {
            throw new InvalidArgumentException('Invalid config key.');
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($this->config[$key]) || !is_array($this->config[$key])) {
                $this->config[$key] = [];
            }
            $this->config = &$this->config[$key];
        }

        $this->config[array_shift($keys)] = $value;

        return $this->config;
    }

    
    public function has($key): bool
    {
        return (bool) $this->get($key);
    }

    
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->config);
    }

    
    public function offsetGet($offset): ?array
    {
        return $this->get($offset);
    }

    
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    
    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }

    public static function loadConfig(): array{
        self::$fileConfig = parse_ini_file(self::SOCIAL_CONF, true);
              return ['cdiscount' =>
                self::$fileConfig['cdiscount']
            ];
    }
}
