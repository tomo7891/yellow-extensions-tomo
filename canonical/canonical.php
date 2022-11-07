<?php
// Canonical extension

class YellowCanonical
{
    const VERSION = "0.8.20";
    public $yellow;         // access to API

    // Handle initialization
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
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
                $canonical = $this->getAbsoluteUrl() . $page->getLocation();
            }
            $output .= "<link rel=\"canonical\" href=\"{$canonical}\">\n";
        }
        return $output;
    }

    public function getAbsoluteUrl()
    {
		$protocol = ($_SERVER["HTTPS"]) ? "https" : "http";
		$output = $protocol . '://' . $_SERVER['SERVER_NAME'];
        return $output;
    }
}
