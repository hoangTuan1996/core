<?php

namespace MediciVN\Core\Caches;

use Illuminate\Cache\CacheManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

final class Cache implements CacheInterface
{
    protected array $config;

    protected string $group;

    public function __construct(protected CacheManager $cache)
    {
        $this->setConfig();
    }

    public function setGroup(string $group): self
    {
        $this->group = $group;
        return $this;
    }

    private function setConfig()
    {
        $this->config = config('medici.decorator') ?? [
                'cache_time' => 3600,
                'stored_keys' => storage_path('cache_keys.json')];
    }

    public function generateCacheKey(string $key): string
    {
        return md5($this->group) . $key;
    }

    public function storeCacheKey(string $key): bool
    {
        if (file_exists($this->config['stored_keys'])) {
//            $cacheKeys = get_file_data($this->config['stored_keys']);
            $cacheKeys = json_decode(File::get($this->config['stored_keys']));
//            dd($cacheKeys);

            if (! empty($cacheKeys) && !in_array($key, Arr::get($cacheKeys, $this->group, []))) {
                $cacheKeys->{$this->group}[] = $key;
            }
        } else {
            $cacheKeys = [];
            $cacheKeys[$this->group][] = $key;
        }

//        dd($cacheKeys);

//        save_file_data($this->config['stored_keys'], $cacheKeys);
        File::put($this->config['stored_keys'], json_encode($cacheKeys));

        return true;
    }

    public function get(string $key): mixed
    {
        if (! file_exists($this->config['stored_keys'])) {
            return null;
        }

        return $this->cache->get($this->generateCacheKey($key));
    }

    public function put(string $key, $value, bool $minutes = false): bool
    {
        if (! $minutes) {
            $minutes = $this->config['cache_time'];
        }

        $key = $this->generateCacheKey($key);

        $this->storeCacheKey($key);

        $this->cache->put($key, $value, $minutes);

        return true;
    }

    public function has(string $key): bool
    {
        if (!file_exists($this->config['stored_keys'])) {
            return false;
        }

        $key = $this->generateCacheKey($key);

        return $this->cache->has($key);
    }

    public function flush(): bool
    {
        $cacheKeys = [];
        if (file_exists($this->config['stored_keys'])) {
//            $cacheKeys = get_file_data($this->config['stored_keys']);
            $cacheKeys = json_decode(File::get($this->config['stored_keys']));
        }

//        dd($cacheKeys->{$this->group});
        if (! empty($cacheKeys)) {
            $caches = $cacheKeys->{$this->group};
//            dd($caches);
            if ($caches) {
                foreach ($caches as $cache) {
                    $this->cache->forget($cache);
                }
                unset($cacheKeys->{$this->group});
            }
        }

        if (!empty($cacheKeys)) {
//            save_file_data($this->config['stored_keys'], $cacheKeys);
            File::put($this->config['stored_keys'], json_encode($cacheKeys));
        } else {
            File::delete($this->config['stored_keys']);
        }

        return true;
    }
}
