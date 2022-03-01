<?php
// Canonical extension

class YellowCanonical
{
    const VERSION = "0.8.19";
    public $yellow;         // access to API

    // Handle initialization
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("www", "0");
        $this->yellow->system->setDefault("ssl", "1");
    }

    public function onParsePageExtra($page, $name)
    {
        $output = null;
        if ($name == 'header') {
            if ($page->isExisting("canonical")) {
                if (preg_match("/^http/", $page->get("canonical"))) {
                    $canonical = $page->get("canonical");
                } else {
                    $canonical = $this->getAbsoluteUrl() . $page->get("canonical");
                }
            } else {
                $canonical = $this->getAbsoluteUrl() . $_SERVER['REQUEST_URI'];
            }
            $output .= "<link rel=\"canonical\" href=\"{$canonical}\">\n";
        }
        return $output;
    }

    public function getAbsoluteUrl()
    {
        if ($this->yellow->system->get("coreStaticUrl") == "auto") {
            $protocol = ($_SERVER["HTTPS"]) ? "https" : "http";
            $output = $protocol . '://' . $_SERVER['HTTP_HOST'];
        } else {
            $output = $this->yellow->system->get("coreStaticUrl");
        }
        return $output;
    }
}
