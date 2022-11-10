<?php
// Blog List extension

class YellowBloglist {
    const VERSION = "0.8.20";
    public $yellow;         // access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }
    
    public function articles($pages)
    {
        $output = null;
        switch ($this->yellow->page->getParentTop()->getLocation()) {
            case "/document/":
                $output = $this->documentList($pages);
                break;
            case "/photo/":
                $output = $this->photoList($pages);
                break;
            case "/video/":
                $output = $this->videoList($pages);
                break;
            case "/audio/":
                $output = $this->audioList($pages);
                break;
            case "/quote/":
                $output = $this->quoteList($pages);
                break;
            case "/link/":
                $output = $this->linkList($pages);
                break;
            case "/map/":
                $output = $this->mapList($pages);
                break;
            case "/literature/":
                $output = $this->literatureList($pages);
                break;
            case "/collection/":
                $output = $this->collectionList($pages);
                break;
            case "/publication/":
                $output = $this->publicationList($pages);
                break;
            case "/blog/":
                $output = $this->blogList($pages);
                break;
            case "/information/":
                $output = $this->informationList($pages);
                break;
            default:
                $output = $this->defaultList($pages);
                break;
        }
        return $output;
    }

    public function documentList($pages)
    {
        $output = null;
        $i = 0;
        foreach ($pages->paginate($this->yellow->system->get("documentPaginationLimit")) as $page) {
            if($i > 0) $output .= "<hr class=\"uk-divider-icon\">";
            $output .= "<section class=\"uk-section uk-padding-remove-vertical\">";
            $output .= "<h2 class=\"uk-h4\">";
            $output .= "<a href=\"{$page->getLocation(true)}\" title=\"{$page->getHtml("title")}\" class=\"uk-link-heading\">{$page->getHtml("title")}</a>";
            $output .= "</h2>";
            $output .= "<p>" . $page->get("description") . "</p>";
            $output .= "</section>";
            $i++;
        }
        return $output;
    }

    public function photoList($pages)
    {
        $output = null;
        $output .= "<div class=\"uk-grid-small uk-child-width-1-2 uk-child-width-1-3@m\" uk-grid>";
        $i = 1;
        foreach ($pages->paginate($this->yellow->system->get("photoPaginationLimit")) as $page) {
            $image = $page->getHtml("image");
            if (is_string_empty($image)) $image = $this->yellow->system->get("noImage");
            $src = '.' . $this->yellow->system->get("CoreImageLocation") . $image;
            list($src, $width, $height) = $this->yellow->extension->get("image")->getImageInformation($src, "264", "264");
            $output .= "<div>";
            $output .= "<a href=\"{$page->getLocation(true)}\" title=\"{$page->get("title")}\">";
            $output .= "<img src=\"{$src}\" width=\"264\" height=\"264\" alt =\"entry-{$i}\" class=\"lazy\">";
            $output .= "</a>";
            $output .= "<p class=\"uk-text-small uk-text-center uk-text-truncate uk-margin-remove-top\">{$page->get("title")}</p>";
            $output .= "</div>";
            $i++;
        }
        $output .= "</div>";
        return $output;
    }

    public function videoList($pages)
    {
        $output = null;
        $output .= "<div class=\"uk-grid-small uk-child-width-1-2 uk-child-width-1-3@m\" uk-grid>";
        $i = 1;
        foreach ($pages->paginate($this->yellow->system->get("videoPaginationLimit")) as $page) {
            $output .= "<div>";
            $output .= "<a href=\"{$page->getLocation(true)}\" title=\"{$page->get("title")}\">";
            $output .= "<img src=\"//img.youtube.com/vi/{$page->get("video")}/hqdefault.jpg\" width=\"480\" height=\"360\" class=\"lazy\" alt=\"entry-{$i}\">";
            $output .= "</a>";
            $output .= "<p class=\"uk-text-small uk-text-center uk-text-truncate uk-margin-remove-top\">{$page->get("title")}</p>";
            $output .= "</div>";
            $i++;
        }
        $output .= "</div>";
        return $output;
    }

    public function audioList($pages)
    {
        $output = null;
        $output .= "<div class=\"uk-grid-small uk-child-width-1-2 uk-child-width-1-3@m\" uk-grid>";
        $i = 1;
        foreach ($pages->paginate($this->yellow->system->get("audioPaginationLimit")) as $page) {
            $output .= "<div>";
            $output .= "<a href=\"" . $page->getLocation(true) . "\" title=\"" . $page->get("title") . "\">";
            $output .= "<img src=\"//img.youtube.com/vi/{$page->get("audio")}/hqdefault.jpg\" width=\"480\" height=\"360\" class=\"lazy\" alt =\"entry-" . $i . "\" title=\"" . $page->get("title") . "\">";
            $output .= "</a>";
            $output .= "<p class=\"uk-text-small uk-text-center uk-text-truncate uk-margin-remove-top\">{$page->get("title")}</p>";
            $output .= "</div>";
            $i++;
        }
        $output .= "</div>";
        return $output;
    }
    public function quoteList($pages)
    {
        $output = null;
        foreach ($pages->paginate($this->yellow->system->get("quotePaginationLimit")) as $page) {
            $output .= "<section class=\"uk-section uk-section-xsmall\">";
            $output .= "<a href=\"{$page->getLocation(true)}\" class=\"uk-link-reset\">";
            $output .= $page->getContent();
            $output .= "</a>";
            $output .= "</section>";
        }
        return $output;
    }

    public function linkList($pages)
    {
        $output = null;
        $i = 0;
        foreach ($pages->paginate($this->yellow->system->get("linkPaginationLimit")) as $page) {
            if($i > 0) $output .= "<hr class=\"uk-divider-icon\">";
            $output .= "<section class=\"uk-section uk-padding-remove-vertical\">";
            $output .= "<h2 class=\"uk-h4\">";
            $output .= "<a href=\"{$page->getLocation(true)}\" title=\"{$page->getHtml("title")}\" class=\"uk-link-heading\">";
            $output .= $page->get("title");
            $output .= "</a>";
            $output .= "</h2>";
            $output .= "<p>";
            $output .= $page->get("description");
            $output .= "</p>";
            $output .= "</section>";
            $i++;
        }
        return $output;
    }

    public function mapList($pages)
    {
        $output = null;
        $i = 0;
        foreach ($pages->paginate($this->yellow->system->get("mapPaginationLimit")) as $page) {
            if($i > 0) $output .= "<hr class=\"uk-divider-icon\">";
            $output .= "<section class=\"uk-section uk-padding-remove-vertical\">";
            $output .= "<h2 class=\"uk-h4\">";
            $output .= "<a href=\"{$page->getLocation(true)}\" title=\"{$page->getHtml("title")}\" class=\"uk-link-heading\">";
            $output .= $page->get("title");
            $output .= "</a>";
            $output .= "</h2>";
            $output .= "<p>";
            $output .= $page->get("description");
            $output .= "</p>";
            $output .= "</section>";
            $i++;
        }
        return $output;
    }

    public function literatureList($pages)
    {
        $output = null;
        $i = 0;
        foreach ($pages->paginate($this->yellow->system->get("linkPaginationLimit")) as $page) {
            if($i > 0) $output .= "<hr class=\"uk-divider-icon\">";
            $output .= "<section class=\"uk-section uk-padding-remove-vertical\">";
            $output .= "<h2 class=\"uk-h4\">";
            $output .= "<a href=\"{$page->getLocation(true)}\" title=\"{$page->getHtml("title")}\" class=\"uk-link-heading\">";
            $output .= $page->get("title");
            $output .= "</a>";
            $output .= "</h2>";
            $output .= "<p>";
            $output .= $page->get("description");
            $output .= "</p>";
            $output .= "</section>";
            $i++;
        }
        return $output;
    }

    public function collectionList($pages)
    {
        $output = null;
        $output .= "<div class=\"uk-overflow-auto\"><table>";
        $output .= "<thead>";
        $output .= "<tr>";
        $output .= "<th>タイトル</th>";
        $output .= "<th>コントリビューター</th>";
        $output .= "<th>カテゴリ</th>";
        $output .= "</tr>";
        $output .= "</thead>";
        $output .= "<tbody>";
        foreach ($pages->paginate($this->yellow->system->get("collectionPaginationLimit")) as $page) {
            $output .= "<tr>";
            $output .= "<td>";
            $output .= "<a href=\"{$page->getLocation(true)}\" title=\"{$page->getHtml("title")}\">{$page->getHtml("title")}</a>";
            $output .= "</td>";
            $output .= "<td>";
            $output .= $page->getHtml("Contributor");
            $output .= "</td>";
            $output .= "<td>";
            $output .= $page->getHtml("Category");
            $output .= "</td>";
            $output .= "</tr>";
        }
        $output .= "</tbody>";
        $output .= "</table></div>";
        return $output;
    }

    public function publicationList($pages)
    {
        $output = null;
        $output .= "<div class=\"uk-grid-small uk-child-width-1-3 uk-child-width-1-4@s uk-child-width-1-5@m uk-child-width-1-4@l\" uk-grid>";
        $i = 0;
        foreach ($pages->paginate($this->yellow->system->get("publicationPaginationLimit")) as $page) {
            $image = $page->getHtml("image");
            if (is_string_empty($image)) $image = $this->yellow->system->get("publicationNoImage");
            $image = '.' . $this->yellow->system->get("CoreImageLocation") . $image;
            $imageWH = resizeImage($image, "s");
            list($src, $width, $height) = $this->yellow->extension->get("image")->getImageInformation($image, $imageWH[0], $imageWH[1]);
            $output .= "<div>";
            $output .= "<a href=\"{$page->getLocation(true)}\" title=\"{$page->get("title")}\">";
            $output .= "<img src=\"{$src}\" width=\"{$width}\" height=\"{$height}\" alt =\"{$page->getHtml('title')}\" class=\"uk-align-center lazy\">";
            $output .= "</a>";
            $output .= "</div>";
        }
        $output .= "</div>";
        return $output;
    }

    public function blogList($pages)
    {
        $output = null;
        $i = 0;
        foreach ($pages->paginate($this->yellow->system->get("blogPaginationLimit")) as $page) {
            $image = $page->getHtml("image");
            if (is_string_empty($image)) $image = $this->yellow->system->get("noImage");
            $image = '.' . $this->yellow->system->get("CoreImageLocation") . $image;
            list($src, $width, $height) = $this->yellow->extension->get("image")->getImageInformation($image, 600, 400);
            $class = ($i == 0) ? '' : ' loading="lazy"';
            $output .= "<section class=\"uk-section uk-section-xsmall\">";             
            $output .= "<a href=\"{$page->getLocation(true)}\" title=\"{$page->get('title')}\" class=\"uk-link-reset uk-display-block\">";      
                    $output .= "<div class=\"uk-card uk-card-default uk-grid-collapse uk-child-width-1-2@s uk-margin uk-box-shadow-hover-large\" uk-grid>";
                        $output .= "<div class=\"uk-card-media-left uk-cover-container\">";
                            $output .= "<img src=\"{$src}\" width=\"{$width}\" height=\"{$height}\" alt=\"{$page->getHtml('title')}\" class=\"lazy\" uk-cover>";
                            $output .= "<canvas width=\"{$width}\" height=\"{$height}\"></canvas>";
                        $output .= "</div>";
                        $output .= "<div class=\"uk-card-body uk-padding-small\">";
                        $output .= "<h2 class=\"uk-h4\">";                        
                        $output .= "{$page->getHtml("title")}";
                        $output .= "</h2>";
                            $output .= "<p class=\"uk-text-break uk-text-justify\">";
                            $output .= $this->yellow->toolbox->createTextDescription($page->getContent(), 100);
                            $output .= "</p>";
                            $output .= "<p class=\"uk-text-meta\">{$page->getDate("published", "CoreDateFormatMedium")}</p>";
                        $output .= "</div>";
                $output .= "</div>";
                $output .= "</a>";
            $output .= "</section>";
            $i++;
        }
        return $output;
    }

    public function informationList($pages)
    {
        $output = null;
        $i = 0;
        foreach ($pages->paginate($this->yellow->system->get("informationPaginationLimit")) as $page) {
            if($i > 0) $output .= "<hr class=\"uk-divider-icon\">";
            $output .= "<section class=\"uk-section uk-padding-remove-vertical\">";
            $output .= "<h2 class=\"uk-h4\">";
            $output .= "<a href=\"{$page->getLocation(true)}\" title=\"{$page->getHtml("title")}\" class=\"uk-link-heading\">";
            $output .= $page->get("title");
            $output .= "</a>";
            $output .= "</h2>";
            $output .= "<p class=\"summary\">";
            $output .= strip_tags($page->getContent());
            $output .= "</p>";
            $output .= "</section>";
            $i++;
        }
        return $output;
    }

    public function defaultList($pages)
    {
        $output = null;
        $i = 0;
        foreach ($pages->paginate($this->yellow->system->get("searchPaginationLimit")) as $page) {
            if($i > 0) $output .= "<hr class=\"uk-divider-icon\">";
            $output .= "<section class=\"uk-section uk-padding-remove-vertical\">";
            $output .= "<h2 class=\"uk-h4\">";
            $output .='<a href="' . $page->getLocation(true) . '" class=\"uk-link-heading\">' . $page->getHtml("title") . '</a></h2>';
            $output .= '<p class="summary">' . htmlspecialchars($this->yellow->toolbox->createTextDescription($page->getContent(false, 4096), $this->yellow->system->get("searchPageLength"))) . '</p>';
            $output .= '<p class="url"><a href="' . $page->getLocation(true) . '">' . $page->getUrl() . '</a></p>';
            $output .= "</section>";
            $i++;
        }
        return $output;
    }

}
