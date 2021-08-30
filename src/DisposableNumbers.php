<?php

namespace Tagmood\LaravelDisposablePhone;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Str;

class DisposableNumbers
{
    /**
     * The storage path to retrieve from and save to.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * Array of retrieved disposable numbers.
     *
     * @var array
     */
    protected $numbers = [];

    /**
     * The cache repository.
     *
     * @var \Illuminate\Contracts\Cache\Repository|null
     */
    protected $cache;

    /**
     * The cache key.
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * Disposable constructor.
     *
     * @param \Illuminate\Contracts\Cache\Repository|null $cache
     */
    public function __construct(Cache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Loads the numbers from cache/storage into the class.
     *
     * @return $this
     */
    public function bootstrap()
    {
        $numbers = $this->getFromCache();

        if (! $numbers) {
            $this->saveToCache(
                $numbers = $this->getFromStorage()
            );
        }

        $this->numbers = $numbers;

        return $this;
    }

    /**
     * Get the numbers from cache.
     *
     * @return array|null
     */
    protected function getFromCache()
    {
        if ($this->cache) {
            $numbers = $this->cache->get($this->getCacheKey());

            // @TODO: Legacy code for bugfix. Remove me.
            if (is_string($numbers) || empty($numbers)) {
                $this->flushCache();
                return null;
            }

            return $numbers;
        }

        return null;
    }

    /**
     * Save the numbers in cache.
     *
     * @param  array|null  $numbers
     */
    public function saveToCache(array $numbers = null)
    {
        if ($this->cache && ! empty($numbers)) {
            $this->cache->forever($this->getCacheKey(), $numbers);
        }
    }

    /**
     * Flushes the cache if applicable.
     */
    public function flushCache()
    {
        if ($this->cache) {
            $this->cache->forget($this->getCacheKey());
        }
    }

    /**
     * Get the numbers from storage, or if non-existent, from the package.
     *
     * @return array
     */
    protected function getFromStorage()
    {
        $numbers = is_file($this->getStoragePath())
            ? file_get_contents($this->getStoragePath())
            : file_get_contents(__DIR__.'/../number-list.json');

        return json_decode($numbers, true);
    }

    /**
     * Save the numbers in storage.
     *
     * @param  array  $numbers
     */
    public function saveToStorage(array $numbers)
    {        
        $saved = file_put_contents($this->getStoragePath(), json_encode($numbers));

        if ($saved) {
            $this->flushCache();
        }

        return $saved;
    }

    /**
     * Flushes the source's list if applicable.
     */
    public function flushStorage()
    {
        if (is_file($this->getStoragePath())) {
            @unlink($this->getStoragePath());
        }
    }

    /**
     * Checks whether the given phone address' number matches a disposable phone service.
     *
     * @param string $phone
     * @return bool
     */
    public function isDisposable($phone)
    {
        if ($phone = Str::replace('+', '', $phone)) {
            return array_key_exists($phone, $this->numbers);
        }

        // Just ignore this validator if the value doesn't even resemble an phone or number.
        return false;
    }

    /**
     * Checks whether the given phone address' number doesn't match a disposable phone service.
     *
     * @param string $phone
     * @return bool
     */
    public function isNotDisposable($phone)
    {
        return ! $this->isDisposable($phone);
    }

    /**
     * Alias of "isNotDisposable".
     *
     * @param string $phone
     * @return bool
     */
    public function isIndisposable($phone)
    {
        return $this->isNotDisposable($phone);
    }

    /**
     * Get the list of disposable numbers.
     *
     * @return array
     */
    public function getNumbers()
    {
        return $this->numbers;
    }

    /**
     * Get the storage path.
     *
     * @return string
     */
    public function getStoragePath()
    {
        return $this->storagePath;
    }

    /**
     * Set the storage path.
     *
     * @param string $path
     * @return $this
     */
    public function setStoragePath($path)
    {
        $this->storagePath = $path;

        return $this;
    }

    /**
     * Get the cache key.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * Set the cache key.
     *
     * @param string $key
     * @return $this
     */
    public function setCacheKey($key)
    {
        $this->cacheKey = $key;

        return $this;
    }
}
