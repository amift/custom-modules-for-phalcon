<?php

namespace Common\Doctrine\Tool;

use Phalcon\Mvc\User\Component;

class CacheMechanism extends Component
{
    private $_cache;

    public function getDefaultCache()
    {
        $cache = null;

        $cfg = $this->getCacheConfig();

        if (!empty($cfg->cache->redis)) {
            $redis = new \Redis();
            $redis->connect($cfg->cache->redis->host, $cfg->cache->redis->port);
            $redis->setOption(\Redis::OPT_PREFIX, $cfg->cache->redis->opt_prefix);

            $cache = new \Doctrine\Common\Cache\RedisCache();
            $cache->setRedis($redis);
        }

        return $cache;
    }

    public function getCache($cacheKey, $default = null, $fileSystemPath = null)
    {
        $cfg = $this->getCacheConfig();

        $cache = $default;

        if ($cacheKey === 'apc') {
            $cache = new \Doctrine\Common\Cache\ApcCache();

        } elseif ($cacheKey === 'redis') {
            $redis = new \Redis();
            $redis->connect($cfg->cache->redis->host, $cfg->cache->redis->port);
            $redis->setOption(\Redis::OPT_PREFIX, $cfg->cache->redis->opt_prefix);

            $cache = new \Doctrine\Common\Cache\RedisCache();
            $cache->setRedis($redis);

        } elseif ($cacheKey === 'memcached') {
            $memcached = new \Memcached();
            $memcached->addServer($cfg->cache->memcached->host, $cfg->cache->memcached->port);

            $cache = new \Doctrine\Common\Cache\MemcachedCache();
            $cache->setMemcached($memcached);

        } elseif ($cacheKey === 'filesystem') {
            $cache = new \Doctrine\Common\Cache\FilesystemCache($fileSystemPath);

        } elseif ($cacheKey === 'array') {
            $cache = new \Doctrine\Common\Cache\ArrayCache();

        }

        return $cache;
    }

    protected function getCacheConfig()
    {
        if (!$this->_cache || $this->_cache === null) {
            $this->_cache = $this->di->getConfig();
        }

        return $this->_cache;
    }

}