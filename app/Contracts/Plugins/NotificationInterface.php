<?php

namespace App\Contracts\Plugins;

/**
 * Interface NotificationInterface
 * 
 * Defines custom notification channels (SMS, Webhook, Slack, etc.) registered by plugins.
 */
interface NotificationInterface
{
    /**
     * Get the unique channel name (e.g. "slack", "whatsapp").
     */
    public function getChannelName(): string;

    /**
     * Send the notification payload to the given recipient.
     */
    public function send(string $recipient, string $message, array $extra = []): bool;
}
