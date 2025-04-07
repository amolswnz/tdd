<?php

namespace App\PageTypes;

use Page;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\TextareaField;
use SilverStripe\AssetAdmin\Forms\UploadField;

class ContentPage extends Page
{
    private static $table_name = 'ContentPage';

    private static $db = [
        'ExtraContent' => 'HTMLText',
    ];

    private static $has_one = [
        'HeaderImage' => Image::class
    ];

    private static $defaults = [
        'ExtraContent' => '<p>Default content goes here.</p>',
    ];

    private static $description = 'A page that contains content.';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        // Add custom fields here
        $fields->addFieldToTab('Root.Content', TextareaField::create('ExtraContent', 'Extra Content'));
        $fields->addFieldToTab('Root.Content', UploadField::create('HeaderImage', 'Header Image')
            ->setFolderName('ContentPage/HeaderImages')
            ->setAllowedFileCategories('image/supported')
            ->setTitle('Upload Header Image'));
        return $fields;
    }
}
// This class represents a content page type in the application.
