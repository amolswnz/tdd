<?php

namespace {

    use SilverStripe\CMS\Model\SiteTree;

    class Page extends SiteTree
    {

        private static string $table_name = 'Page';

        private static array $db = [];

        private static array $has_one = [];

    }
}
