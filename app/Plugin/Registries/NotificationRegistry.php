<?php

namespace App\Plugin\Registries;

use App\Contracts\Plugins\NotificationInterface;

/**
 * Class NotificationRegistry
 * 
 * Evolved notification channel registry to store email/sms/slack providers.
 */
class NotificationRegistry
{
    /**
     * Registered notification channels.
     * 
     * @var NotificationInterface[]
     */
    protected array $channels = [];

    /**
     * Register a custom notification channel.
     */
    public function register(NotificationInterface $channel): void
    {
        $this->channels[$channel->getChannelName()] = $channel;
    }

    /**
     * Get a specific notification channel by name.
     */
    public function channel(string $name): ?NotificationInterface
    {
        return $this->channels[$name] ?? null;
    }

    /**
     * Get all registered notification channels.
     */
    public function all(): array
    {
        return $this->channels;
    }
}
