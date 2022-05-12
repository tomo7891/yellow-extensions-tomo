<?php
// Short URL extension

class YellowSurl
{
    const VERSION = "0.8.20";
    public $yellow;         // access to API

    // Handle initialization
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("SurlParam", "s");
        $this->yellow->system->set("SurlList", "yellow-surl.ini");
    }

    public function onParsePageOutput($page, $text)
    {
        $extensionDirectory = $this->yellow->system->get("coreExtensionDirectory");
        //Short URL redirects    
        $redirectLocation = null;
        $surlID = $page->getRequest($this->yellow->system->get("SurlParam"));
        $pages = $this->yellow->content->index()->filter("surl", $surlID);
        $this->yellow->page->setLastModified($pages->getModified());
        if (count($pages) == 1) {
            foreach ($pages as $page) {
                $redirectLocation = $page->getLocation();
            }
        }
        if ($redirectLocation) {
            $url = $this->getAbsoluteUrl() . $redirectLocation;
            header("Location: " . $url, true, 301);
            exit();
        }
        if (file_exists($extensionDirectory . $this->yellow->system->get("SurlList"))) {
            $list = $this->yellow->toolbox->readFile($extensionDirectory . $this->yellow->system->get("SurlList"));
            $list = str_replace(array("\r\n", "\r", "\n"), "\n", $list);
            $list = explode("\n", $list);
            foreach ($list as $l) {
                if (strpos($l, '||')) {
                    $r = explode('||', trim($l));
                    if ($surlID == trim($r[0])) {
                        $url = $this->getAbsoluteUrl() . ($r[1]);
                        header("Location: " . $url, true, 301);
                        exit();
                    }
                }
            }
        }
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
