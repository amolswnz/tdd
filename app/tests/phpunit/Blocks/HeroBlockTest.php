<?php

namespace Tests\Blocks;

use App\Elements\HeroBlock;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\CompositeValidator;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\LinkField\Form\LinkField;
use SilverStripe\ORM\DataObject;

class HeroBlockTest extends SapphireTest
{

    protected static $fixture_file = 'HeroBlockTest.yml';

    public function test_hero_block_table_name(): void
    {
        $tableName = DataObject::getSchema()->tableName(HeroBlock::class);
        $this->assertEquals('HeroBlock', $tableName);
    }

    public function test_hero_block_type(): void
    {
        $block = singleton(HeroBlock::class);

        $this->assertEquals('Hero block element', $block->getType());
    }

    public function test_cms_fields(): void
    {
        $obj = singleton(HeroBlock::class);
        $fields = $obj->getCMSFields();

        $this->assertInstanceOf(HeroBlock::class, $obj);

        $expectedInstances = [
            'Heading' => TextField::class,
            'Summary' => TextareaField::class,
            'BackgroundImage' => UploadField::class,
            'PrimaryLinkID' => LinkField::class,
            'SecondaryLinkID' => LinkField::class,
            'BackgroundColor' => TextField::class,
            'TextColor' => TextField::class,
            'ContentAlignment' => DropdownField::class,
        ];

        foreach ($expectedInstances as $key => $class) {
            $msg = sprintf('The field %s should be an instance of %s::class', $key, $class);
            $this->assertInstanceOf($class, $fields->dataFieldByName($key), $msg);
        }
    }

    public function test_required_fields(): void
    {
        $block = singleton(HeroBlock::class);

        $validator = $block->getCMSCompositeValidator();
        $this->assertInstanceOf(CompositeValidator::class, $validator);

        $fields = $validator->getValidatorsByType(RequiredFields::class);
        $requiredFields = reset($fields); // Gets the first validator

        $this->assertInstanceOf(RequiredFields::class, $requiredFields);
        $this->assertEquals(['Heading', 'ContentAlignment'], $requiredFields->getRequired());
    }

}
