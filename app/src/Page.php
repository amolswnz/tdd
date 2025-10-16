<?php

namespace {

    use SilverStripe\CMS\Model\SiteTree;

    class Page extends SiteTree
    {
        private static $table_name = 'Page';

        private static $db = [];

        private static $has_one = [];
    }
}
