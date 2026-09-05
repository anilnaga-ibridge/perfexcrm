<?php

namespace App\Plugin\Runtime;

class RuntimeContext
{
    public string $platformVersion;
    public string $environment;
    public ?string $tenantId;
    public string $locale;

    public function __construct(
        string $platformVersion = '1.0.0',
        string $environment = 'production',
        ?string $tenantId = null,
        string $locale = 'en'
    ) {
        $this->platformVersion = $platformVersion;
        $this->environment = $environment;
        $this->tenantId = $tenantId;
        $this->locale = $locale;
    }
}
