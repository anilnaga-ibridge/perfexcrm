<?php

namespace App\Plugin\Logging;

use App\Plugin\Storage\PluginStorageManager;
use Psr\Log\LoggerInterface;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use Illuminate\Log\Logger as LaravelLogger;

/**
 * Class PluginLogger
 * 
 * Provides isolated logging channels for each plugin,
 * routing messages to storage/logs/plugins/{alias}/{alias}.log.
 */
class PluginLogger
{
    protected PluginStorageManager $storage;
    protected array $channels = [];

    public function __construct(PluginStorageManager $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Resolve the PSR-3 logger channel for the given plugin alias.
     */
    public function channel(string $pluginAlias): LoggerInterface
    {
        $alias = strtolower($pluginAlias);
        if (isset($this->channels[$alias])) {
            return $this->channels[$alias];
        }

        $logPath = $this->storage->getLogPath($alias) . "/{$alias}.log";

        // Create a custom Monolog logger channel
        $monolog = new Monolog($alias, [
            new StreamHandler($logPath, Monolog::DEBUG)
        ]);

        $this->channels[$alias] = new LaravelLogger($monolog);
        return $this->channels[$alias];
    }

    /**
     * Log an informational message.
     */
    public function info(string $pluginAlias, string $message, array $context = []): void
    {
        $this->channel($pluginAlias)->info($message, $context);
    }

    /**
     * Log a warning message.
     */
    public function warning(string $pluginAlias, string $message, array $context = []): void
    {
        $this->channel($pluginAlias)->warning($message, $context);
    }

    /**
     * Log an error message.
     */
    public function error(string $pluginAlias, string $message, array $context = []): void
    {
        $this->channel($pluginAlias)->error($message, $context);
    }

    /**
     * Log a critical error message.
     */
    public function critical(string $pluginAlias, string $message, array $context = []): void
    {
        $this->channel($pluginAlias)->critical($message, $context);
    }

    /**
     * Log a debug message.
     */
    public function debug(string $pluginAlias, string $message, array $context = []): void
    {
        $this->channel($pluginAlias)->debug($message, $context);
    }
}
