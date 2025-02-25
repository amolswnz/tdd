<?php

namespace App\Elements;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\LinkField\Models\Link;
use SilverStripe\Forms\CompositeValidator;
use SilverStripe\LinkField\Form\LinkField;
use DNADesign\Elemental\Models\BaseElement;

class HeroElement extends BaseElement
{
    private static string $table_name = 'HeroElement';
    
    private static string $singular_name = 'hero element';
    
    private static string $plural_name = 'hero elements';
    
    private static string $description = 'Hero element';
    
    private static bool $inline_editable = false;

    private static $db = [
        'Heading' => 'Varchar',
        'Content' => 'Varchar',
    ];

    private static array $has_one = [
        'CTALink' =>  Link::class
    ];

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab('Root.Main', LinkField::create('CTALink', 'CTA Link'));

        return $fields;
    }

    public function getCMSCompositeValidator(): CompositeValidator
    {
        $validator = parent::getCMSCompositeValidator();

        $validator->addValidator(RequiredFields::create([
            'Heading',
            'Content'
        ]));

        return $validator;
    }

    public function getSummary(): string
    {
        return 'This is page hero block element';
    }

    public function getType(): string
    {
        return 'Hero block element';
    }
}
