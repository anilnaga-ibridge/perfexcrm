<?php

namespace App\Exceptions;

use RuntimeException;

class LegacyControllerResolutionException extends RuntimeException
{
    protected string $moduleAlias;
    protected string $controllerName;
    protected string $methodName;
    protected array $searchedPaths;
    protected array $discoveredControllers;
    protected array $discoveredMethods;

    public function __construct(
        string $message,
        string $moduleAlias = '',
        string $controllerName = '',
        string $methodName = '',
        array $searchedPaths = [],
        array $discoveredControllers = [],
        array $discoveredMethods = []
    ) {
        parent::__construct($message);
        $this->moduleAlias = $moduleAlias;
        $this->controllerName = $controllerName;
        $this->methodName = $methodName;
        $this->searchedPaths = $searchedPaths;
        $this->discoveredControllers = $discoveredControllers;
        $this->discoveredMethods = $discoveredMethods;
    }

    public function getModuleAlias(): string { return $this->moduleAlias; }
    public function getControllerName(): string { return $this->controllerName; }
    public function getMethodName(): string { return $this->methodName; }
    public function getSearchedPaths(): array { return $this->searchedPaths; }
    public function getDiscoveredControllers(): array { return $this->discoveredControllers; }
    public function getDiscoveredMethods(): array { return $this->discoveredMethods; }
}
