<?php

namespace Tests\Blocks;

use App\Elements\HeroElement;
use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\SapphireTest;

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
}
