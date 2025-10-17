<?php

namespace App\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Member;

/**
 * Ticket DataObject
 *
 * Represents a FreshService ticket in the local database
 */
class Ticket extends DataObject
{
    /**
     * Database table name
     */
    private static string $table_name = 'Ticket';

    /**
     * Singular name for this model
     */
    private static string $singular_name = 'Ticket';

    /**
     * Plural name for this model
     */
    private static string $plural_name = 'Tickets';

    /**
     * Database fields
     */
    private static array $db = [
        'FreshServiceID' => 'BigInt',        // External FreshService ticket ID
        'DepartmentID' => 'BigInt',          // department_id
        'GroupID' => 'BigInt',               // group_id
        'Priority' => 'Int',                 // priority (1-4: Low, Medium, High, Urgent)
        'Type' => 'Varchar(100)',            // type
        'Category' => 'Varchar(255)',        // category
        'SubCategory' => 'Varchar(255)',     // sub_category
        'Subject' => 'Varchar(500)',         // subject
        'Status' => 'Int',                   // status (2: Open, 3: Pending, 4: Resolved, 5: Closed)
        'AssignedAgentID' => 'BigInt',       // assigned agent ID
        'CreatedAt' => 'DBDatetime',         // created_at
        'UpdatedAt' => 'DBDatetime',         // updated_at
        'ResolvedAt' => 'DBDatetime',        // resolved_at
        'ClosedAt' => 'DBDatetime',          // closed_at
        'LastSyncedAt' => 'DBDatetime',      // when this record was last synced from FreshService
    ];

    /**
     * Indexes for better performance
     */
    private static array $indexes = [
        'FreshServiceID' => true,
        'Status' => true,
        'Priority' => true,
        'CreatedAt' => true,
        'UpdatedAt' => true,
    ];

    /**
     * Default values
     */
    private static array $defaults = [
        'Priority' => 1, // Low priority by default
        'Status' => 2,   // Open status by default
    ];

    /**
     * Summary fields for GridField
     */
    private static array $summary_fields = [
        'FreshServiceID' => 'ID',
        'Subject' => 'Subject',
        'PriorityName' => 'Priority',
        'StatusName' => 'Status',
        'CreatedAt.Nice' => 'Created',
        'UpdatedAt.Nice' => 'Updated',
    ];

    /**
     * Searchable fields
     */
    private static array $searchable_fields = [
        'FreshServiceID',
        'Subject',
        'Category',
        'SubCategory',
        'Priority',
        'Status',
    ];

    /**
     * Default sort order
     */
    private static string $default_sort = 'UpdatedAt DESC, CreatedAt DESC';

    /**
     * CMS Fields
     */
    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        // Remove the default fields and rebuild with custom structure
        $fields->removeByName([
            'FreshServiceID', 'DepartmentID', 'GroupID', 'Priority', 'Type',
            'Category', 'SubCategory', 'Subject', 'Status',
            'AssignedAgentID', 'CreatedAt', 'UpdatedAt',
            'ResolvedAt', 'ClosedAt', 'LastSyncedAt'
        ]);

        // Basic ticket information
        $fields->addFieldsToTab('Root.Main', [
            ReadonlyField::create('FreshServiceID', 'FreshService ID')
                ->setDescription('External FreshService ticket ID'),
            TextField::create('Subject', 'Subject')
                ->setMaxLength(500),
        ]);

        // Categorization
        $fields->addFieldsToTab('Root.Classification', [
            DropdownField::create('Priority', 'Priority', $this->getPriorityOptions()),
            DropdownField::create('Status', 'Status', $this->getStatusOptions()),
            TextField::create('Type', 'Type'),
            TextField::create('Category', 'Category'),
            TextField::create('SubCategory', 'Sub Category'),
        ]);

        // Assignment
        $fields->addFieldsToTab('Root.Assignment', [
            NumericField::create('DepartmentID', 'Department ID'),
            NumericField::create('GroupID', 'Group ID'),
            NumericField::create('AssignedAgentID', 'Assigned Agent ID'),
        ]);

        // Timestamps
        $fields->addFieldsToTab('Root.Timestamps', [
            ReadonlyField::create('CreatedAt', 'Created At'),
            ReadonlyField::create('UpdatedAt', 'Updated At'),
            ReadonlyField::create('ResolvedAt', 'Resolved At'),
            ReadonlyField::create('ClosedAt', 'Closed At'),
            ReadonlyField::create('LastSyncedAt', 'Last Synced At'),
        ]);

        return $fields;
    }

    /**
     * Get priority options for dropdown
     */
    public function getPriorityOptions(): array
    {
        return [
            1 => 'Low',
            2 => 'Medium',
            3 => 'High',
            4 => 'Urgent',
        ];
    }

    /**
     * Get status options for dropdown
     */
    public function getStatusOptions(): array
    {
        return [
            2 => 'Open',
            3 => 'Pending',
            4 => 'Resolved',
            5 => 'Closed',
        ];
    }

    /**
     * Get human-readable priority name
     */
    public function getPriorityName(): string
    {
        $options = $this->getPriorityOptions();
        return $options[$this->Priority] ?? 'Unknown';
    }

    /**
     * Get human-readable status name
     */
    public function getStatusName(): string
    {
        $options = $this->getStatusOptions();
        return $options[$this->Status] ?? 'Unknown';
    }

    /**
     * Get priority CSS class for styling
     */
    public function getPriorityCSSClass(): string
    {
        switch ($this->Priority) {
            case 1: return 'priority-low';
            case 2: return 'priority-medium';
            case 3: return 'priority-high';
            case 4: return 'priority-urgent';
            default: return 'priority-unknown';
        }
    }

    /**
     * Get status CSS class for styling
     */
    public function getStatusCSSClass(): string
    {
        switch ($this->Status) {
            case 2: return 'status-open';
            case 3: return 'status-pending';
            case 4: return 'status-resolved';
            case 5: return 'status-closed';
            default: return 'status-unknown';
        }
    }

    /**
     * Check if ticket is open (not resolved or closed)
     */
    public function isOpen(): bool
    {
        return in_array($this->Status, [2, 3]); // Open or Pending
    }

    /**
     * Check if ticket is resolved
     */
    public function isResolved(): bool
    {
        return $this->Status === 4;
    }

    /**
     * Check if ticket is closed
     */
    public function isClosed(): bool
    {
        return $this->Status === 5;
    }

    /**
     * Validation
     */
    public function validate(): ValidationResult
    {
        $result = parent::validate();

        // Validate FreshService ID is unique
        if ($this->FreshServiceID) {
            $existing = Ticket::get()->filter('FreshServiceID', $this->FreshServiceID);
            if ($this->ID) {
                $existing = $existing->exclude('ID', $this->ID);
            }
            if ($existing->exists()) {
                $result->addFieldError('FreshServiceID', 'A ticket with this FreshService ID already exists.');
            }
        }

        // Validate required fields
        if (empty($this->Subject)) {
            $result->addFieldError('Subject', 'Subject is required.');
        }

        // Validate priority range
        if (!in_array($this->Priority, [1, 2, 3, 4])) {
            $result->addFieldError('Priority', 'Priority must be between 1 and 4.');
        }

        // Validate status range
        if (!in_array($this->Status, [2, 3, 4, 5])) {
            $result->addFieldError('Status', 'Status must be 2 (Open), 3 (Pending), 4 (Resolved), or 5 (Closed).');
        }

        return $result;
    }

    /**
     * Before writing to database
     */
    public function onBeforeWrite(): void
    {
        parent::onBeforeWrite();

        // Set LastSyncedAt when creating or updating
        $this->LastSyncedAt = date('Y-m-d H:i:s');

        // Auto-set resolved/closed timestamps
        if ($this->isChanged('Status')) {
            if ($this->Status === 4 && !$this->ResolvedAt) {
                $this->ResolvedAt = date('Y-m-d H:i:s');
            } elseif ($this->Status === 5 && !$this->ClosedAt) {
                $this->ClosedAt = date('Y-m-d H:i:s');
            }
        }
    }

    /**
     * Permissions
     */
    public function canView($member = null): bool
    {
        return Permission::check('CMS_ACCESS_LeftAndMain', 'any', $member);
    }

    public function canEdit($member = null): bool
    {
        return Permission::check('CMS_ACCESS_LeftAndMain', 'any', $member);
    }

    public function canDelete($member = null): bool
    {
        return Permission::check('CMS_ACCESS_LeftAndMain', 'any', $member);
    }

    public function canCreate($member = null, $context = []): bool
    {
        return Permission::check('CMS_ACCESS_LeftAndMain', 'any', $member);
    }

    /**
     * Static method to create/update ticket from FreshService API data
     */
    public static function createFromFreshServiceData(array $data): Ticket
    {
        // Find existing ticket by FreshService ID or create new one
        $ticket = Ticket::get()->filter('FreshServiceID', $data['id'])->first();
        if (!$ticket) {
            $ticket = Ticket::create();
        }

        // Map FreshService data to our fields
        $ticket->FreshServiceID = $data['id'];
        $ticket->DepartmentID = $data['department_id'] ?? null;
        $ticket->GroupID = $data['group_id'] ?? null;
        $ticket->Priority = $data['priority'] ?? 1;
        $ticket->Type = $data['type'] ?? null;
        $ticket->Category = $data['category'] ?? null;
        $ticket->SubCategory = $data['sub_category'] ?? null;
        $ticket->Subject = $data['subject'] ?? '';
        $ticket->Status = $data['status'] ?? 2;
        $ticket->AssignedAgentID = $data['responder_id'] ?? null;

        // Parse timestamps
        if (!empty($data['created_at'])) {
            $ticket->CreatedAt = date('Y-m-d H:i:s', strtotime($data['created_at']));
        }
        if (!empty($data['updated_at'])) {
            $ticket->UpdatedAt = date('Y-m-d H:i:s', strtotime($data['updated_at']));
        }

        $ticket->write();
        return $ticket;
    }
}
