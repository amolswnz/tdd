<?php

namespace App\PageTypes;

use Page;
use SilverStripe\Forms\TextareaField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\TextField;

class ApiPage extends Page
{
    private static $table_name = 'ApiPage';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab('Root.Content', TextField::create('ApiLink', 'API Link'));


        return $fields;
    }
}
