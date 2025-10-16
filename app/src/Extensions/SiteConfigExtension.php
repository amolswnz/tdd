<?php

namespace App\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;

/**
 * SiteConfigExtension
 *
 * Extends SiteConfig to add FreshService API configuration options
 */
class SiteConfigExtension extends Extension
{

    /**
     * Database fields for this extension
     */
    private static array $db = [
        'FreshServiceAPIEndpoint' => 'Varchar(255)',
        'FreshServiceAPIKey' => 'Varchar(255)',
        'FreshServiceDomain' => 'Varchar(255)',
    ];

    /**
     * Update CMS fields to include FreshService API configuration
     */
    protected function updateCMSFields(FieldList $fields): void
    {
        // Add a new tab for FreshService configuration
        $fields->addFieldsToTab('Root.FreshService', [
            HeaderField::create('FreshServiceHeader', 'FreshService API Configuration'),
            TextField::create('FreshServiceDomain', 'FreshService Domain')
                ->setDescription('Your FreshService domain (e.g., yourcompany.freshservice.com)'),
            TextField::create('FreshServiceAPIEndpoint', 'API Endpoint')
                ->setDescription('The FreshService API endpoint URL'),
            TextField::create('FreshServiceAPIKey', 'API Key')
                ->setDescription('Your FreshService API key for authentication')
                ->setAttribute('type', 'password'),
        ]);
    }

    /**
     * Get the full API URL
     */
    public function getFreshServiceApiUrl(): ?string
    {
        $domain = $this->getOwner()->FreshServiceDomain;
        $endpoint = $this->getOwner()->FreshServiceAPIEndpoint;

        if (!$domain || !$endpoint) {
            return null;
        }

        // Ensure domain has proper protocol
        if (!preg_match('/^https?:\/\//', $domain)) {
            $domain = 'https://' . $domain;
        }

        // Remove trailing slash from domain and leading slash from endpoint if present
        $domain = rtrim($domain, '/');
        $endpoint = ltrim($endpoint, '/');

        return $domain . '/' . $endpoint;
    }

    /**
     * Check if FreshService API is configured
     */
    public function isFreshServiceConfigured(): bool
    {
        $owner = $this->getOwner();

        return ($owner->FreshServiceDomain !== null && $owner->FreshServiceDomain !== '')
            && ($owner->FreshServiceAPIEndpoint !== null && $owner->FreshServiceAPIEndpoint !== '')
            && ($owner->FreshServiceAPIKey !== null && $owner->FreshServiceAPIKey !== '');
    }

}
