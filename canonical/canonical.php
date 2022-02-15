<?php
// Canonical extension

class YellowCanonical {
    const VERSION = "0.8.19";
    public $yellow;         // access to API

    // Handle initialization
    public function onLoad($yellow) {
        $this->yellow = $yellow;    
        $this->yellow->system->setDefault("www", "0");
        $this->yellow->system->setDefault("ssl", "1");
    }

    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name == 'header') {
            if ($page->isExisting("canonical")) { 
                if(preg_match("/^http/", $page->get("canonical"))){
                    $canonical = $page->get("canonical");
                }else{
                    $canonical = $this->getAbsoluteUrl() . $page->getHtml("canonical");
                }
            }else{
                $canonical = $this->getAbsoluteUrl() . $_SERVER['REQUEST_URI'];
            } 
            $output .= "<link rel=\"canonical\" href=\"{$canonical}\">\n";
        }
        return $output;
    }

    public function onParsePageOutput($page, $text) {
        if (PHP_SAPI != "cli") {
            if($this->yellow->system->isExisting("www") || $this->yellow->system->isExisting("ssl")) {
                $canonical = $this->getAbsoluteUrl() . $_SERVER['REQUEST_URI'];
                if ($this->yellow->system->get("www")=="0" && preg_match("/www/", $canonical)) {
                    $canonical = str_replace('www.', '', $canonical);
                    header("HTTP/1.1 301 Moved Permanently");
                    header("Location: {$canonical}");
                    exit;
                }    
                if ($this->yellow->system->get("ssl")=="1" && empty($_SERVER["HTTPS"])) {
                    $canonical = str_replace('http://', 'https://', $canonical);
                    header("HTTP/1.1 301 Moved Permanently");
                    header("Location: {$canonical}");
                    exit;
                }
            }
        }
    }

    public function getAbsoluteUrl() {
        if($this->yellow->system->get("coreStaticUrl") == "auto"){
            $protocol = ($_SERVER["HTTPS"]) ? "https" : "http"; 
            $output = $protocol.'://' . $_SERVER['HTTP_HOST'];
        } else {
            $output = $this->yellow->system->get("coreStaticUrl");
        }
        return $output;
    }
}
