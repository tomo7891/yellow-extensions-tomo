<?php
// Hotfix extension,

class YellowCategory
{
    const VERSION = "1.0.0";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
    }

    // Handle page layout
    public function onParsePageLayout($page, $name)
    {
        if ($name == "blogpages") {
            $pages = $this->yellow->extension->get("blog")->getBlogPages($page->location);
            $pagesFilter = array();
            if ($page->isRequest("category")) {
                $pages->filter("category", $page->getRequest("category"));
                array_push($pagesFilter, $pages->getFilter());
            }
            $pages->sort("published");
            $pages->pagination($this->yellow->system->get("blogPaginationLimit"));
            if (!$pages->getPaginationNumber()) $page->error(404);
            if (!empty($pagesFilter)) {
                $text = implode(" ", $pagesFilter);
                $page->set("titleHeader", $text . " - " . $page->get("sitename"));
                $page->set("titleContent", $page->get("title") . ": " . $text);
                $page->set("title", $page->get("title") . ": " . $text);
            } else {
                $page->set("titleContent", "");
            }
            $page->setPages("blog", $pages);
            $page->setLastModified($pages->getModified());
            $page->setHeader("Cache-Control", "max-age=60");
        }
    }
}
