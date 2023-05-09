<?php

namespace Splash\Connectors\Shopify\Models;

trait ConnectorScopesTrait
{
    /**
     * Get Shopify Access Scope from API
     *
     * @return bool
     */
    public function fetchAccessScopes(): bool
    {
        return $this->scopesManagers->fetchAccessScopes($this);
    }

    /**
     * Get Shopify Access Scope from Parameters
     *
     * @return string[]
     */
    public function getAccessScopes(): array
    {
        return $this->scopesManagers->getAccessScopes($this);
    }

    /**
     * Get List of Missing Access Scopes.
     *
     * @return string[]
     */
    public function getMissingScopes() : array
    {
        return $this->scopesManagers->getMissingScopes($this);
    }
}