<?php
// Figure extension

class YellowFigure
{
    const VERSION = "0.8.13";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
        $this->yellow->language->setDefault("figureDefaultAlt");
    }

    // Handle page content of shortcut
    public function onParseContentShortcut($page, $name, $text, $type)
    {
        $output = null;
        if ($name == "figure" && ($type == "block" || $type == "inline")) {
            list($name, $alt, $figStyle, $imgStyle, $width, $height) = $this->yellow->toolbox->getTextArguments($text);
            if ($this->yellow->extension->isExisting("image")) {
                if (!preg_match("/^\w+:/", $name)) {
                    if (empty($alt)) $alt = $this->yellow->language->getText("figureDefaultAlt");
                    if (empty($width)) $width = "100%";
                    if (empty($height)) $height = $width;
                    list($src, $width, $height) = $this->yellow->extension->get("image")->getImageInformation($this->yellow->system->get("coreImageDirectory") . $name, $width, $height);
                } else {
                    if (empty($alt)) $alt = $this->yellow->language->getText("figureDefaultAlt");
                    $src = $this->yellow->lookup->normaliseUrl("", "", "", $name);
                    $width = $height = 0;
                }
                $output = "<figure";
                if (!empty($figStyle)) $output .= " class=\"" . htmlspecialchars($figStyle) . "\"";
                $output .= "><img src=\"" . htmlspecialchars($src) . "\"";
                if ($width && $height) $output .= " width=\"" . htmlspecialchars($width) . "\" height=\"" . htmlspecialchars($height) . "\"";
                if (!empty($alt)) $output .= " alt=\"" . htmlspecialchars($alt) . "\" title=\"" . htmlspecialchars($alt) . "\"";
                if (!empty($imgStyle)) $output .= " class=\"" . htmlspecialchars($imgStyle) . "\"";
                $output .= " />";
                if (!empty($alt)) $output .= "<figcaption>" . htmlspecialchars($alt) . "</figcaption>";
                $output .= "</figure>";
            } else {
                $page->error(500, "Figure requires 'image' extension!");
            }
        }
        return $output;
    }
}
