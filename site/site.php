<?php
// Site extension

class YellowSite
{
    const VERSION = "0.8.25";
    public $yellow;         // access to API

    // Handle initialization
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("keywords", "");
        $this->yellow->system->setDefault("logo", "");
    }

    public function onParsePageLayout($page, $name)
    {
        if (!$page->isExisting("keywords") || is_string_empty($page->getHtml("keywords"))) {
            $page->set("keywords", $this->yellow->system->getHtml("keywords"));
        }
        if (!$page->isExisting("logo") || is_string_empty($page->getHtml("logo"))) {
            $page->set("logo", $this->yellow->system->getHtml("logo"));
        }
    }

    public function onParsePageExtra($page, $name)
    {
        $output = null;
        switch ($name) {
            case 'toParent':
                $output = $this->toParent($page);
                break;
        }
        return $output;
    }

    //Site
    public function toParent($page)
    {
        $output = null;
        $getParentTop = $page->getParentTop();
        if ($getParentTop && $page->get("type") != "home") {
            $output .= "<p class=\"toparent\"><a href=\"" . $getParentTop->getLocation(true) . "\">" .
                '← ' . htmlspecialchars($getParentTop->get("titleNavigation")) . "</a></p>";
        }
        return $output;
    }

    //Get Main Content Link
    function getMainContent($content, $location, $width ='', $height = '', $align="", $loading =""){
        $output = null;
        if(is_string_empty($content) || is_string_empty($location)) return $output;        
        $width = (is_string_empty($width)) ? '600' : $width;
        $height = (is_string_empty($height)) ? '400' : $height;
        $align = ($align=="right") ? 'uk-flex-last@s uk-card-media-right' : 'uk-card-media-left';
        $imageEx = $this->yellow->extension->get('image');
        $images = '.' . $this->yellow->system->get('CoreImageLocation');
        list($s, $w, $h) = $imageEx->getImageInformation($images . $content.'.jpg', $width, $height);
        $title = $this->yellow->content->find($location)->get('titleContent');
        $description = $this->yellow->content->find($location)->get("description");
        $link = $this->yellow->page->getBase(true) . $location;
        $output .= '<div>';
        $output .= '<div class="uk-card uk-card-default uk-grid-collapse uk-child-width-1-2@s" uk-grid>';
        $output .= '<div class="'.$align.' uk-cover-container">';        
        $output .= '<a href="'.$link.'">';
        $output .= '<img src="' . $s . '"  width="' . $w . '" height="' . $h . '" alt="'.$title.'" class="lazy" uk-cover>';
        $output .= '<canvas width="' . $w . '" height="' . $h . '"></canvas>';
        $output .= '</a>';
        $output .= '</div>';
        $output .= '<div class="uk-flex uk-flex-middle">';
        $output .= '<a href="'.$link.'" class="uk-link-toggle uk-card-body uk-text-small uk-padding-small">';
        $output .= '<h2 class="uk-card-title uk-margin-remove uk-text-center"><span class="uk-link-heading">'.$title.'</span></h2>';
        $output .= '<p>';
        $output .= $description;
        $output .= '</p>';
        $output .= '</a>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';
        return $output;
    }
}
