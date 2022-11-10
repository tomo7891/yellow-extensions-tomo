<?php
// UIKit extension

class YellowUikit
{
    const VERSION = "0.8.19";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
    }

    // Handle page content of shortcut
    public function onParseContentShortcut($page, $name, $text, $type)
    {
        $output = null;
        if ($name == "gallery" && ($type == "block" || $type == "inline")) {
            $output .= $this->gallery($page, $name, $text, $type);
        }
        if ($name == "youtube" && ($type == "block" || $type == "inline")) {
            $output .= $this->youtube($page, $name, $text, $type);
        }
        if ($name == "map" && ($type == "block" || $type == "inline")) {
            $output .= $this->map($page, $name, $text, $type);
        }
        return $output;
    }

    //Components
    function gallery($page, $name, $text, $type) {
        $output = null;
            list($pattern, $sorting, $size) = $this->yellow->toolbox->getTextArguments($text);
            if (is_string_empty($sorting)) $sorting = $this->yellow->system->get("gallerySorting");
            if (is_string_empty($size)) $size = "100%";
            if (is_string_empty($pattern)) {
                $pattern = "unknown";
                $files = $this->yellow->media->clean();
            } else {
                $images = $this->yellow->system->get("coreImageLocation");
                $files = $this->yellow->media->index()->match("#$images$pattern#");
                if ($sorting == "modified") $files->sort("modified", false);
                elseif ($sorting == "size") $files->sort("size", false);
            }
            if ($this->yellow->extension->isExisting("image")) {
                if (!is_array_empty($files)) {
                    $page->setLastModified($files->getModified());
                    $output .= "<div class=\"gallery grid uk-child-width-1-2 uk-child-width-1-3@m\" uk-grid uk-lightbox>";
                    foreach ($files as $file) {
                        list($src, $width, $height) = $this->yellow->extension->get("image")->getImageInformation($file->fileName, $size, $size);
                        list($widthInput, $heightInput) = $this->yellow->toolbox->detectImageInformation($file->fileName);
                        if (!$widthInput || !$heightInput) $widthInput = $heightInput = "500";
                        $caption = $this->yellow->language->isText($file->fileName) ? $this->yellow->language->getText($file->fileName) : "";
                        $alt = is_string_empty($caption) ? basename($file->getLocation(true)) : $caption;
                        $output .= "<div class=\"uk-text-center\"><a href=\"" . $file->getLocation(true) . "\"";
                        $output .= "class=\"uk-inline\"";
                        $output .= " data-caption=\"" . htmlspecialchars($caption) . "\"";
                        $output .= ">";
                        $output .= "<img src=\"" . htmlspecialchars($src) . "\"";
                        if ($width && $height) $output .= " width=\"300\" height=\"300\"";
                        $output .= " class=\"uk-object-cover lazy\" alt=\"" . htmlspecialchars($alt) . "\" title=\"" . htmlspecialchars($alt) . "\"  style=\"aspect-ratio: 1 / 1\" />";
                        $output .= "</a></div>\n";
                    }
                    $output .= "</div>";
                } else {
                    $page->error(500, "Gallery '$pattern' does not exist!");
                }
            } else {
                $page->error(500, "Gallery requires 'image' extension!");
            }
        return $output;
    }

    function youtube($page, $name, $text, $type){
        $output = null;
        list($id, $style, $width, $height) = $this->yellow->toolbox->getTextArguments($text);
            if (is_string_empty($style)) $style = $this->yellow->system->get("youtubeStyle");
            $language = $page->get("language");
            $output = "<div class=\"".htmlspecialchars($style)."\" style=\"max-width:32rem;\">";
            $output .= "<iframe class=\"lazy\" src=\"https://www.youtube.com/embed/".rawurlencode($id)."?hl=".rawurlencode($language)."\" frameborder=\"0\" allow=\"fullscreen\"";
            if ($width && $height) $output .= " width=\"".htmlspecialchars($width)."\" height=\"".htmlspecialchars($height)."\" uk-responsive uk-video style=\"aspect-ratio: 16 / 9;\"";
            $output .= "></iframe></div>";
        return $output;
    }

    function map($page, $name, $text, $type){
        $output = null;
        list($address, $zoom, $style, $width, $height) = $this->yellow->toolbox->getTextArguments($text);
            if (is_string_empty($zoom)) $zoom = $this->yellow->system->get("googlemapZoom");
            if (is_string_empty($style)) $style = $this->yellow->system->get("googlemapStyle");
            $language = $page->get("language");
            $output = "<div class=\"".htmlspecialchars($style)."\" style=\"max-width:32rem;\">";
            $output .= "<iframe class=\"lazy\" src=\"https://maps.google.com/maps?q=".rawurlencode($address)."&amp;ie=UTF8&amp;t=m&amp;z=".rawurlencode($zoom)."&amp;hl=$language&amp;iwloc=near&amp;num=1&amp;output=embed\" frameborder=\"0\"";
            if ($width && $height) $output .= " width=\"".htmlspecialchars($width)."\" height=\"".htmlspecialchars($height)."\"";
            $output .= " uk-responsive style=\"aspect-ratio: 16 / 9;\"></iframe></div>";
            return $output;
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name)
    {
        $output = null;
        if ($name == "header") {
            $extensionLocation = $this->yellow->system->get("coreServerBase").$this->yellow->system->get("coreExtensionLocation");
            //$output .= "<script type=\"text/javascript\" src=\"{$extensionLocation}uikit.js\"></script>\n";
            $output .= "<script src=\"https://cdn.jsdelivr.net/npm/uikit@3.15.12/dist/js/uikit-core.min.js\"></script>\n";
            $output .= "<script src=\"https://cdn.jsdelivr.net/npm/uikit@3.15.12/dist/js/components/lightbox-panel.min.js\"></script>\n";
            $output .= "<script src=\"https://cdn.jsdelivr.net/npm/uikit@3.15.12/dist/js/components/lightbox.min.js\"></script>\n";
            $output .= "<script src=\"https://cdn.jsdelivr.net/npm/uikit@3.15.12/dist/js/uikit-icons.min.js\" defer></script>\n";            
        }
        if($name == "footer") {
            $output .= "<script>\n";
            $output .= "UIkit.scroll('.footnote-backref, .footnote-ref, .toc li a', {offset: 0});\n";
            $output .= "UIkit.grid('.grid');\n";
            $output .= "UIkit.lightbox('.gallery, .lightbox');\n";
            $output .= "</script>";
        }
        return $output;
    }
}
