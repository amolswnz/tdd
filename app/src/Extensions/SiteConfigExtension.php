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
    ];

    /**
     * Update CMS fields to include FreshService API configuration
     */
    protected function updateCMSFields(FieldList $fields): void
    {
        // Add a new tab for FreshService configuration
        $fields->addFieldsToTab('Root.FreshService', [
            HeaderField::create('FreshServiceHeader', 'FreshService API Configuration'),
            TextField::create('FreshServiceAPIEndpoint', 'API Endpoint')
                ->setDescription('The FreshService API endpoint URL (e.g., https://yourcompany.freshservice.com/api/v2)'),
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
        $endpoint = $this->getOwner()->FreshServiceAPIEndpoint;
        if (!$endpoint) {
            return null;
        }
        // Ensure endpoint has proper protocol
        if (!preg_match('/^https?:\/\//', $endpoint)) {
            $endpoint = 'https://' . ltrim($endpoint, '/');
        }
        // Remove trailing slash
        $endpoint = rtrim($endpoint, '/');
        return $endpoint;
    }

    /**
     * Check if FreshService API is configured
     */
    public function isFreshServiceConfigured(): bool
    {
        $owner = $this->getOwner();
        return ($owner->FreshServiceAPIEndpoint !== null && $owner->FreshServiceAPIEndpoint !== '')
            && ($owner->FreshServiceAPIKey !== null && $owner->FreshServiceAPIKey !== '');
    }

}
