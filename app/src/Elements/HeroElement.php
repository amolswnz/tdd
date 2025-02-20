<?php

namespace App\Elements;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CompositeValidator;
use DNADesign\Elemental\Models\BaseElement;

class HeroElement extends BaseElement
{
    private static string $table_name = 'HeroElement';
    
    private static string $singular_name = 'hero element';
    
    private static string $plural_name = 'hero elements';
    
    private static string $description = 'Hero element';
    
    private static bool $inline_editable = false;

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        return $fields;
    }

    public function getCMSCompositeValidator(): CompositeValidator
    {
        $validator = parent::getCMSCompositeValidator();

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
