<?php

namespace Tests\Blocks;

use App\Elements\HeroElement;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\TextField;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\LinkField\Form\LinkField;

class HeroElementTest extends SapphireTest
{
    protected static $fixture_file = 'HeroElementTest.yml';

    public function test_hero_block_table_name(): void
    {
        $tableName = DataObject::getSchema()->tableName(HeroElement::class);
        $this->assertEquals('HeroElement', $tableName);
    }

    public function test_hero_block_type(): void
    {
        $block = singleton(HeroElement::class);

        $this->assertEquals('Hero block element', $block->getType());
    }

    public function test_cms_fields(): void
    {
        $obj = singleton(HeroElement::class);
        $fields = $obj->getCMSFields();

        $this->assertInstanceOf(HeroElement::class, $obj);

        $expectedInstances = [
            'Heading' => TextField::class,
            'Content' => TextField::class,
            'CTALink' => LinkField::class,
        ];

        foreach ($expectedInstances as $key => $class) {
            $msg = sprintf('The field %s should be an instance of %s::class', $key, $class);
            $this->assertInstanceOf($class, $fields->dataFieldByName($key), $msg);
        }
    }
}
