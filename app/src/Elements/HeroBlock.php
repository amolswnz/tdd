<?php

namespace App\Elements;

use SilverStripe\Assets\Image;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\LinkField\Models\Link;
use SilverStripe\Forms\CompositeValidator;
use SilverStripe\LinkField\Form\LinkField;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\AssetAdmin\Forms\UploadField;

class HeroBlock extends BaseElement
{

    private static string $table_name = 'HeroBlock';

    private static string $singular_name = 'Hero Block';

    private static string $plural_name = 'Hero Blocks';

    private static array $db = [
        'Heading' => 'Varchar(255)',
        'Summary' => 'Text',
        'BackgroundColor' => 'Varchar(7)',
        'TextColor' => 'Varchar(7)',
        'ContentAlignment' => 'Enum("Left, Center, Right", "Center")',
    ];

    private static array $has_one = [
        'BackgroundImage' => Image::class,
        'PrimaryLink' => Link::class,
        'SecondaryLink' => Link::class,
    ];

    private static array $owns = [
        'BackgroundImage',
        'PrimaryLink',
        'SecondaryLink',
    ];

    private static bool $inline_editable = false;

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldsToTab(
            'Root.Main',
            [
                TextField::create('Heading', 'Heading'),
                TextareaField::create('Summary', 'Summary'),
                UploadField::create('BackgroundImage', 'BackgroundImage'),

                LinkField::create('PrimaryLinkID', 'Primary Link'),
                LinkField::create('SecondaryLinkID', 'Secondary Link'),

                TextField::create('BackgroundColor', 'Background Color')->setDescription('Hex color code (e.g. #FFFFFF)'),
                TextField::create('TextColor', 'Text Color')->setDescription('Hex color code (e.g. #FFFFFF)'),
                DropdownField::create(
                    'ContentAlignment',
                    'Content Alignment',
                    [
                        'Left' => 'Left',
                        'Center' => 'Center',
                        'Right' => 'Right'
                    ],
                    'Center'
                )->setDescription('Choose the alignment of the content within the hero block.'),
            ]
        );
        return $fields;
    }

    public function getCMSCompositeValidator(): CompositeValidator
    {
        $validator = parent::getCMSCompositeValidator();

        $validator->addValidator(RequiredFields::create([
            'Heading',
            'ContentAlignment'
        ]));

        return $validator;
    }

    public function getType()
    {
        return 'Hero block element';
    }
}
