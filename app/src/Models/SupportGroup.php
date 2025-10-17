<?php

namespace App\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Core\Validation\ValidationResult;

/**
 * Class SupportGroup
 * Represents a support group with an ID and name
 *
 * @package App\Models
 * @property string $GroupID
 * @property string $Name
 */
class SupportGroup extends DataObject
{
    private static $table_name = 'SupportGroup';

    private static $db = [
        'GroupID' => 'Varchar(255)',
        'Name' => 'Varchar(255)',
        'AllowedImports' => 'Boolean'
    ];

    private static $summary_fields = [
        'GroupID' => 'Group ID',
        'Name' => 'Name',
        'AllowedImports' => 'Allowed Imports'
    ];

    private static $searchable_fields = [
        'GroupID',
        'Name',
        'AllowedImports'
    ];

    private static array $defaults = [
        'AllowedImports' => false
    ];

    private static $indexes = [
        'GroupID' => true
    ];


    private static $default_sort = 'Name ASC';

    /**
     * Get the CMS fields for this object
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('GroupID', 'Group ID')
                ->setDescription('Unique identifier for the support group'),
            TextField::create('Name', 'Name')
                ->setDescription('Display name for the support group')
        ]);

        return $fields;
    }

    /**
     * Validation
     *
     * @return ValidationResult
     */
    public function validate(): ValidationResult
    {
        $result = parent::validate();

        // Validate required fields
        if (empty($this->GroupID)) {
            $result->addFieldError('GroupID', 'Group ID is required.');
        }

        if (empty($this->Name)) {
            $result->addFieldError('Name', 'Name is required.');
        }

        // Validate GroupID is unique
        if ($this->GroupID) {
            $existing = static::get()->filter('GroupID', $this->GroupID);
            if ($this->ID) {
                $existing = $existing->exclude('ID', $this->ID);
            }
            if ($existing->exists()) {
                $result->addFieldError('GroupID', 'A support group with this Group ID already exists.');
            }
        }

        return $result;
    }

    /**
     * Get the title for this object
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->Name ?: $this->GroupID;
    }
}
