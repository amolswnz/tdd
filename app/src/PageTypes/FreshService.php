<?php

namespace App\PageTypes;

use Page;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\LiteralField;

/**
 * FreshService page type
 *
 * Custom page type for FreshService functionality
 */
class FreshService extends Page
{
    /**
     * Singular name for this page type
     */
    private static $singular_name = 'FreshService Page';

    /**
     * Plural name for this page type
     */
    private static $plural_name = 'FreshService Pages';

    /**
     * Description shown in the CMS
     */
    private static $class_description = 'A page for FreshService content and functionality';

    /**
     * Table name for this page type
     */
    private static $table_name = 'FreshServicePage';

    /**
     * Database fields for this page type
     */
    private static $db = [
        // Add custom fields here as needed
        // 'CustomField' => 'Varchar(255)',
    ];

    /**
     * Has one relationships
     */
    private static $has_one = [
        // Add has_one relationships here as needed
    ];

    /**
     * Has many relationships
     */
    private static $has_many = [
        // Add has_many relationships here as needed
    ];

    /**
     * Many many relationships
     */
    private static $many_many = [
        // Add many_many relationships here as needed
    ];

    /**
     * CMS Fields for this page type
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Add API status information
        $siteConfig = SiteConfig::current_site_config();
        $apiConfigured = $siteConfig->isFreshServiceConfigured();

        if ($apiConfigured) {
            $statusMessage = '<div class="alert alert-success">FreshService API is configured. Endpoint: ' .
                           $siteConfig->getFreshServiceApiUrl() . '</div>';
        } else {
            $statusMessage = '<div class="alert alert-warning">FreshService API is not fully configured. ' .
                           'Please configure the API settings in <a href="/admin/settings">Site Settings</a>.</div>';
        }

        $fields->addFieldToTab(
            'Root.Main',
            LiteralField::create('APIStatus', $statusMessage),
            'Content'
        );

        return $fields;
    }

    /**
     * Get the FreshService API configuration from SiteConfig
     */
    public function getFreshServiceConfig()
    {
        return SiteConfig::current_site_config();
    }

    /**
     * Get the configured FreshService API URL
     */
    public function getFreshServiceApiUrl()
    {
        $config = $this->getFreshServiceConfig();
        return $config->getFreshServiceApiUrl();
    }

    /**
     * Check if FreshService API is configured
     */
    public function isFreshServiceConfigured()
    {
        $config = $this->getFreshServiceConfig();
        return $config->isFreshServiceConfigured();
    }

    /**
     * Can this page be created in the CMS?
     */
    public function canCreate($member = null, $context = [])
    {
        return parent::canCreate($member, $context);
    }

    /**
     * Can this page be viewed?
     */
    public function canView($member = null)
    {
        return parent::canView($member);
    }

    /**
     * Can this page be edited?
     */
    public function canEdit($member = null)
    {
        return parent::canEdit($member);
    }

    /**
     * Can this page be deleted?
     */
    public function canDelete($member = null)
    {
        return parent::canDelete($member);
    }
}
