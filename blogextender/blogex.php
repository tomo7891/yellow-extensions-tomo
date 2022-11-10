<?php
// Blog Extender extension

include_once("blog.php");
class YellowBlogex extends YellowBlog
{
    const VERSION = "0.8.21";
    public $yellow;         // access to API

    // Handle initialization
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("blogStartLocation", "auto");
        $this->yellow->system->setDefault("blogNewLocation", "@title");
        $this->yellow->system->setDefault("blogEntriesMax", "5");
        $this->yellow->system->setDefault("blogPaginationLimit", "5");
        $this->yellow->system->setDefault("documentPaginationLimit", "10");
        $this->yellow->system->setDefault("photoPaginationLimit", "10");
        $this->yellow->system->setDefault("videoPaginationLimit", "10");
        $this->yellow->system->setDefault("audioPaginationLimit", "10");
        $this->yellow->system->setDefault("quotePaginationLimit", "10");
        $this->yellow->system->setDefault("linkPaginationLimit", "10");
        $this->yellow->system->setDefault("mapPaginationLimit", "10");
        $this->yellow->system->setDefault("literaturePaginationLimit", "10");
        $this->yellow->system->setDefault("collectionPaginationLimit", "10");
        $this->yellow->system->setDefault("publicationPaginationLimit", "10");
        $this->yellow->system->setDefault("informationPaginationLimit", "10");
    }

    public function onParseContentShortcut($page, $name, $text, $type)
    {
        $output = null;
        if (substru($name, 0, 4) == "blog" && ($type == "block" || $type == "inline")) {
            switch ($name) {
                case "blogauthors":
                    $output = $this->getShorcutBlogauthors($page, $name, $text);
                    break;
                case "blogpages":
                    $output = $this->getShorcutBlogpages($page, $name, $text);
                    break;
                case "blogchanges":
                    $output = $this->getShorcutBlogchanges($page, $name, $text);
                    break;
                case "blogrelated":
                    $output = $this->getShorcutBlogrelated($page, $name, $text);
                    break;
                case "blogtags":
                    $output = $this->getShorcutBlogtags($page, $name, $text);
                    break;
                case "blogyears":
                    $output = $this->getShorcutBlogyears($page, $name, $text);
                    break;
                case "blogmonths":
                    $output = $this->getShorcutBlogmonths($page, $name, $text);
                    break;
                case "blogcategories":
                    $output = $this->getShortcutBlogcategories($page, $name, $text);
                    break;
                case "blogcontributors":
                    $output = $this->getShortcutBlogcontributors($page, $name, $text);
                    break;
                case "blogby":
                    $output = $this->getShortcutBlogby($page, $name, $text);
                    break;
                case "blogfrom":
                    $output = $this->getShortcutBlogfrom($page, $name, $text);
                    break;
                case "blogpublishers":
                    $output = $this->getShortcutBlogpublishers($page, $name, $text);
                    break;
            }
        }
        return $output;
    }

    // Return blogcategories shortcut
    public function getShortcutBlogcategories($page, $name, $text)
    {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("blogStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("blogEntriesMax");
        $blogStart = $this->yellow->content->find($startLocation);
        $pages = $this->getBlogPages($startLocation);
        $page->setLastModified($pages->getModified());
        $categories = $this->getMeta($pages, "category");
        if (!is_array_empty($categories)) {
            $categories = $this->yellow->lookup->normaliseUpperLower($categories);
            if ($entriesMax != 0 && count($categories) > $entriesMax) {
                uasort($categories, "strnatcasecmp");
                $categories = array_slice($categories, -$entriesMax, $entriesMax, true);
            }
            uksort($categories, "strnatcasecmp");
            $output = "<div class=\"" . htmlspecialchars($name) . "\">\n";
            $output .= "<ul>\n";
            foreach ($categories as $key => $value) {
                $output .= "<li><a href=\"" . $blogStart->getLocation(true) . $this->yellow->toolbox->normaliseArguments("category:$key") . "\">";
                $output .= htmlspecialchars($key) . "</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Blogcategories '$startLocation' does not exist!");
        }
        return $output;
    }

    // Return blogcontributors shortcut
    public function getShortcutBlogcontributors($page, $name, $text)
    {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("blogStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("blogEntriesMax");
        $blogStart = $this->yellow->content->find($startLocation);
        $pages = $this->getBlogPages($startLocation);
        $page->setLastModified($pages->getModified());
        $contributors = $this->getMeta($pages, "contributor");
        if (!is_array_empty($contributors)) {
            $contributors = $this->yellow->lookup->normaliseUpperLower($contributors);
            if ($entriesMax != 0 && count($contributors) > $entriesMax) {
                uasort($contributors, "strnatcasecmp");
                $contributors = array_slice($contributors, -$entriesMax, $entriesMax, true);
            }
            uksort($contributors, "strnatcasecmp");
            $output = "<div class=\"" . htmlspecialchars($name) . "\">\n";
            $output .= "<ul>\n";
            foreach ($contributors as $key => $value) {
                $output .= "<li><a href=\"" . $blogStart->getLocation(true) . $this->yellow->toolbox->normaliseArguments("contributor:$key") . "\">";
                $output .= htmlspecialchars($key) . "</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Blogcontributors '$startLocation' does not exist!");
        }
        return $output;
    }

    // Return blogby shortcut
    public function getShortcutBlogby($page, $name, $text)
    {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("blogStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("blogEntriesMax");
        $blogStart = $this->yellow->content->find($startLocation);
        $pages = $this->getBlogPages($startLocation);
        $page->setLastModified($pages->getModified());
        $by = $this->getMeta($pages, "by");
        if (!is_array_empty($by)) {
            $by = $this->yellow->lookup->normaliseUpperLower($by);
            if ($entriesMax != 0 && count($by) > $entriesMax) {
                uasort($by, "strnatcasecmp");
                $by = array_slice($by, -$entriesMax, $entriesMax, true);
            }
            uksort($by, "strnatcasecmp");
            $output = "<div class=\"" . htmlspecialchars($name) . "\">\n";
            $output .= "<ul>\n";
            foreach ($by as $key => $value) {
                $output .= "<li><a href=\"" . $blogStart->getLocation(true) . $this->yellow->toolbox->normaliseArguments("by:$key") . "\">";
                $output .= htmlspecialchars($key) . "</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Blogby '$startLocation' does not exist!");
        }
        return $output;
    }

    // Return blogfrom shortcut
    public function getShortcutBlogfrom($page, $name, $text)
    {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("blogStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("blogEntriesMax");
        $blogStart = $this->yellow->content->find($startLocation);
        $pages = $this->getBlogPages($startLocation);
        $page->setLastModified($pages->getModified());
        $from = $this->getMeta($pages, "from");
        if (!is_array_empty($from)) {
            $from = $this->yellow->lookup->normaliseUpperLower($from);
            if ($entriesMax != 0 && count($from) > $entriesMax) {
                uasort($from, "strnatcasecmp");
                $from = array_slice($from, -$entriesMax, $entriesMax, true);
            }
            uksort($from, "strnatcasecmp");
            $output = "<div class=\"" . htmlspecialchars($name) . "\">\n";
            $output .= "<ul>\n";
            foreach ($from as $key => $value) {
                $output .= "<li><a href=\"" . $blogStart->getLocation(true) . $this->yellow->toolbox->normaliseArguments("from:$key") . "\">";
                $output .= htmlspecialchars($key) . "</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Blogfrom '$startLocation' does not exist!");
        }
        return $output;
    }

    // Return blogpublishers shortcut
    public function getShortcutBlogpublishers($page, $name, $text)
    {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("blogStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("blogEntriesMax");
        $blogStart = $this->yellow->content->find($startLocation);
        $pages = $this->getBlogPages($startLocation);
        $page->setLastModified($pages->getModified());
        $publisher = $this->getMeta($pages, "publisher");
        if (!is_array_empty($publisher)) {
            $publisher = $this->yellow->lookup->normaliseUpperLower($publisher);
            if ($entriesMax != 0 && count($publisher) > $entriesMax) {
                uasort($publisher, "strnatcasecmp");
                $publisher = array_slice($publisher, -$entriesMax, $entriesMax, true);
            }
            uksort($publisher, "strnatcasecmp");
            $output = "<div class=\"" . htmlspecialchars($name) . "\">\n";
            $output .= "<ul>\n";
            foreach ($publisher as $key => $value) {
                $output .= "<li><a href=\"" . $blogStart->getLocation(true) . $this->yellow->toolbox->normaliseArguments("publisher:$key") . "\">";
                $output .= htmlspecialchars($key) . "</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Blogpublishers '$startLocation' does not exist!");
        }
        return $output;
    }

    // Handle page layout
    public function onParsePageLayout($page, $name)
    {
        if ($name == "blog-start") {
            $pages = $this->getBlogPages($page->location);
            $pagesFilter = array();
            if ($page->isRequest("tag")) {
                $pages->filter("tag", $page->getRequest("tag"));
                array_push($pagesFilter, $pages->getFilter());
            }
            if ($page->isRequest("author")) {
                $pages->filter("author", $page->getRequest("author"));
                array_push($pagesFilter, $pages->getFilter());
            }
            if ($page->isRequest("published")) {
                $pages->filter("published", $page->getRequest("published"), false);
                array_push($pagesFilter, $this->yellow->language->normaliseDate($pages->getFilter()));
            }
            if ($page->isRequest("category")) {
                $pages->filter("category", $page->getRequest("category"));
                array_push($pagesFilter, $pages->getFilter());
            }
            if ($page->isRequest("contributor")) {
                $pages->filter("contributor", $page->getRequest("contributor"));
                array_push($pagesFilter, $pages->getFilter());
            }
            if ($page->isRequest("by")) {
                $pages->filter("by", $page->getRequest("by"));
                array_push($pagesFilter, $pages->getFilter());
            }
            if ($page->isRequest("from")) {
                $pages->filter("from", $page->getRequest("from"));
                array_push($pagesFilter, $pages->getFilter());
            }
            if ($page->isRequest("publisher")) {
                $pages->filter("publisher", $page->getRequest("publisher"));
                array_push($pagesFilter, $pages->getFilter());
            }
            if ($this->yellow->page->isExisting("sortKey")) {
                if ($this->yellow->page->get("sortAsc") == "1") {
                    $sort = $this->yellow->page->get("sortKey");
                    $asc = true;
                } else {
                    $sort = $this->yellow->page->get("sortKey");
                    $asc = false;
                }
            } else {
                $sort = "published";
                $asc = false;
            }
            $pages->sort($sort, $asc);
            if (!is_string_empty($pagesFilter)) {
                $text = implode(" ", $pagesFilter);
                $page->set("titleHeader", $text . " - " . $page->get("sitename"));
                if (!$page->isRequest("tag") && !$page->isRequest("author") && !$page->isRequest("published")) {
                    if($text){
                        $page->set("titleContent", $page->get("title") . ": " . $text);
                        $page->set("title", $page->get("title") . ": " . $text);
                    }
                }
                $page->set("blogWithFilter", true);
            }
            $page->setPages("blogex", $pages);
            $page->setLastModified($pages->getModified());
            $page->setHeader("Cache-Control", "max-age=60");
        }
        if ($name == "blog") {
            $blogStartLocation = $this->yellow->system->get("blogStartLocation");
            if ($blogStartLocation != "auto") {
                $blogStart = $this->yellow->content->find($blogStartLocation);
            } else {
                $blogStart = $page->getParent();
            }
            $page->setPage("blogStart", $blogStart);
        }
    }

    public function onParsePageExtra($page, $name)
    {
        $output = null;
        if ($name == "taxonomy") {
            if ($page->isExisting("tag")) {
                $output .= '<p>' . $this->getTaxonomyTag($page) . '</p>';
            }
            if ($page->isExisting("category")) {
                $output .= '<p>' . $this->getTaxonomyCategory($page) . '</p>';
            }
            if ($page->isExisting("contributor")) {
                $output .= '<p>' . $this->getTaxonomyContributor($page) . '</p>';
            }
            if ($page->isExisting("by")) {
                $output .= '<p>' . $this->getTaxonomyBy($page) . '</p>';
            }
            if ($page->isExisting("from")) {
                $output .= '<p>' . $this->getTaxonomyFrom($page) . '</p>';
            }
            if ($page->isExisting("published")) {
                $output .= '<p>' . $this->getTaxonomyPublished($page) . '</p>';
            }
            if ($page->isExisting("modified")) {
                $output .= '<p>' . $this->getTaxonomyModified($page) . '</p>';
            }
        }
        return $output;
    }

    public function getTaxonomyTag($page)
    {
        $output = null;
        $tagCounter = 0;
        $output .= $this->yellow->language->getTextHtml("tag") . " ";
        foreach (preg_split("/\s*,\s*/", $page->get("tag")) as $tag) {
            if (++$tagCounter > 1) {
                $output .= ", ";
            }
            $output .= "<a href=\"" . $page->getPage("blogStart")->getLocation(true) . $this->yellow->toolbox->normaliseArguments("tag:$tag") . "\">";
            $output .= htmlspecialchars($tag);
            $output .= "</a>";
        }
        return $output;
    }

    public function getTaxonomyCategory($page)
    {
        $output = null;
        $tagCounter = 0;
        $output .= $this->yellow->language->getTextHtml("category") . " ";
        $output .= "<a href=\"" . $page->getPage("blogStart")->getLocation(true) . $this->yellow->toolbox->normaliseArguments("category:" . $page->get("category")) . "\">";
        $output .= htmlspecialchars($this->yellow->page->get("category"));
        $output .= "</a>";
        return $output;
    }

    public function getTaxonomyContributor($page)
    {
        $output = null;
        $contributorCounter = 0;
        $output .= $this->yellow->language->getTextHtml("contributor") . " ";
        foreach (preg_split("/\s*,\s*/", $page->get("contributor")) as $contributor) {
            if (++$contributorCounter > 1) {
                $output .= ", ";
            }
            $output .= "<a href=\"" . $page->getPage("blogStart")->getLocation(true) . $this->yellow->toolbox->normaliseArguments("contributor:$contributor") . "\">";
            $output .= htmlspecialchars($contributor);
            $output .= "</a>";
        }
        return $output;
    }

    public function getTaxonomyBy($page)
    {
        $output = null;
        $byCounter = 0;
        $output .= $this->yellow->language->getTextHtml("by") . " ";
        foreach (preg_split("/\s*,\s*/", $page->get("by")) as $by) {
            if (++$byCounter > 1) {
                $output .= ", ";
            }
            $output .= "<a href=\"" . $page->getPage("blogStart")->getLocation(true) . $this->yellow->toolbox->normaliseArguments("by:$by") . "\">";
            $output .= htmlspecialchars($by);
            $output .= "</a>";
        }
        return $output;
    }

    public function getTaxonomyFrom($page)
    {
        $output = null;
        $tagCounter = 0;
        $output .= $this->yellow->language->getTextHtml("from") . " ";
        $output .= "<a href=\"" . $page->getPage("blogStart")->getLocation(true) . $this->yellow->toolbox->normaliseArguments("from:" . $page->get("from")) . "\">";
        $output .= htmlspecialchars($this->yellow->page->get("from"));
        $output .= "</a>";
        return $output;
    }

    public function getTaxonomyPublished($page)
    {
        $output = null;
        $output .= '<span>';
        $output .= $this->yellow->language->getTextHtml("published") . " ";
        $output .= '<time datetime="' . $this->yellow->page->getDateHtml("published", "CoreDateFormatMedium") . '" itemprop="dateCreated datepublished">';
        $output .= $this->yellow->page->getDateHtml("published", "CoreDateFormatMedium");
        $output .= '</time></span>';
        return $output;
    }

    public function getTaxonomyModified($page)
    {
        $output = null;
        $output .= '<span>';
        $output .= $this->yellow->language->getTextHtml("modified") . " ";
        $output .= '<time datetime="' . $this->yellow->page->getDateHtml("modified", "CoreDateFormatMedium") . '" itemprop="dateModified">';
        $output .= $this->yellow->page->getDateHtml("modified", "CoreDateFormatMedium");
        $output .= '</time></span>';
        return $output;
    }
}
