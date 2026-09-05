<?php

namespace App\Plugin\Runtime;

use App\Plugin\Kernel\PluginDescriptor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PluginContext
{
    public PluginDescriptor $descriptor;
    public RuntimeContext $runtimeContext;
    public $container = null;

    public function __construct(PluginDescriptor $descriptor, RuntimeContext $runtimeContext)
    {
        $this->descriptor = $descriptor;
        $this->runtimeContext = $runtimeContext;
    }

    /**
     * Get isolated plugin filesystem disk storage.
     */
    public function storage(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        $alias = $this->descriptor->alias;
        return Storage::build([
            'driver' => 'local',
            'root'   => storage_path("app/plugins/{$alias}"),
        ]);
    }

    /**
     * Get prefix-isolated plugin cache repository.
     */
    public function cache(): \Illuminate\Contracts\Cache\Repository
    {
        $alias = $this->descriptor->alias;
        return Cache::repository(
            new \Illuminate\Cache\PrefixValuesStore(
                Cache::driver()->getStore(),
                "plugin_{$alias}_"
            )
        );
    }

    /**
     * Get isolated plugin logger instance.
     */
    public function logger(): \Psr\Log\LoggerInterface
    {
        $alias = $this->descriptor->alias;
        return Log::build([
            'driver' => 'single',
            'path'   => storage_path("logs/plugin_{$alias}.log"),
        ]);
    }
}
