<?php

class YellowPhpinfo
{
    const VERSION = "0.8.19";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
    }

    // Handle page meta data
    public function onParseMeta($page)
    {
        if ($page->get("layout") == "phpinfo") $page->visible = false;
    }

    // Handle page layout
    public function onParsePageLayout($page, $name)
    {
        if ($this->yellow->page->get("layout") == "phpinfo") {
            if ($this->yellow->user->getUserHtml("status") != "active") {
                $this->yellow->page->error(404);
            }
        }
    }
}
