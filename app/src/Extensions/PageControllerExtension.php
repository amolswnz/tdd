<?php

namespace App\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * PageControllerExtension
 *
 * Extends PageController to provide FreshService API access in templates
 */
class PageControllerExtension extends Extension
{
    /**
     * Get the FreshService configuration for use in templates
     */
    public function getFreshServiceConfig()
    {
        return SiteConfig::current_site_config();
    }

    /**
     * Get the FreshService API URL for use in templates
     */
    public function getFreshServiceApiUrl()
    {
        $config = SiteConfig::current_site_config();
        return $config->getFreshServiceApiUrl();
    }

    /**
     * Check if FreshService is configured for use in templates
     */
    public function isFreshServiceConfigured()
    {
        $config = SiteConfig::current_site_config();
        return $config->isFreshServiceConfigured();
    }
}
