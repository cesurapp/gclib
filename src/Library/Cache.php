<?php

namespace App\Library;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Cache
{
    /**
     * @var FilesystemAdapter
     */
    private $adapter;

    /**
     * Cache constructor.
     */
    public function __construct()
    {
        $this->adapter = new FilesystemAdapter('', 3600, __DIR__ . '/../../var/cache');
    }

    /**
     * Get|Set Cache Item
     *
     * @param string $key
     * @param callable $callback
     * @param float|null $beta
     * @param array|null $metadata
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @return mixed
     */
    public function get(string $key, callable $callback, float $beta = null, array &$metadata = null)
    {
        return $this->adapter->get($key, $callback, $beta, $metadata);
    }

    /**
     * Save Cache Item
     *
     * @param string $key
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @return bool
     */
    public function delete(string $key): bool
    {
        return $this->adapter->delete($key);
    }

    /**
     * Get Caching Directory
     *
     * @return string
     */
    public static function getDir(): string
    {
        return __DIR__ . '/../../var/cache';
    }
}