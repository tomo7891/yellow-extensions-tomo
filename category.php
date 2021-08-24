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

    // Handle page content of shortcut
    public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if (substru($name, 0, 4)=="blog" && ($type=="block" || $type=="inline")) {
            switch($name) {
                case "blogcategories": $output = $this->getShorcutBlogcategories($page, $name, $text); break;
            }
        }
        return $output;
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

    // Return blogcategories shortcut
    public function getShorcutBlogcategories($page, $name, $text) {
        $output = null;
        list($location, $pagesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (empty($location)) $location = $this->yellow->system->get("blogLocation");
        if (empty($location)) $location = "unknown";
        if (strempty($pagesMax)) $pagesMax = $this->yellow->system->get("blogPagesMax");
        $blog = $this->yellow->content->find($location);
        $pages = $this->yellow->extension->get("blog")->getBlogPages($location);
        $page->setLastModified($pages->getModified());
        $categories = $this->yellow->extension->get("blog")->getMeta($pages, "category");
        if (count($categories)) {
            $categories = $this->yellow->lookup->normaliseUpperLower($categories);
            if ($pagesMax!=0 && count($categories)>$pagesMax) {
                uasort($categories, "strnatcasecmp");
                $categories = array_slice($categories, -$pagesMax);
            }
            uksort($categories, "strnatcasecmp");
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<ul>\n";
            foreach ($categories as $key=>$value) {
                $output .= "<li><a href=\"".$blog->getLocation(true).$this->yellow->toolbox->normaliseArguments("category:$key")."\">";
                $output .= htmlspecialchars($key)."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Blogcategories '$location' does not exist!");
        }
        return $output;
    }
}
