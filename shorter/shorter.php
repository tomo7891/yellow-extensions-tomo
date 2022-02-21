<?php
// Short URL extension

class YellowShorter {
    const VERSION = "0.8.19";
     public $yellow;         // access to API

    // Handle initialization
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("ShorterParam", "s");
        $this->yellow->system->setDefault("ShorterList", "yellow-shorter.ini");
    }

    public function onParsePageOutput($page, $text) {
        $extensionDirectory = $this->yellow->system->get("coreExtensionDirectory");        
        //Short URL redirects    
        $redirectLocation = null;
        $surlID = $page->getRequest( $this->yellow->system->get("ShorterParam"));
        $pages = $this->yellow->content->index()->filter("shorter", $surlID);
        $this->yellow->page->setLastModified($pages->getModified());
        if(count($pages)){
            foreach($pages as $page){
                $redirectLocation = $page->getLocation();
            }
        }
        if($redirectLocation){
            $url = $this->yellow->system->get("coreStaticUrl").$redirectLocation;
            header("Location: ".$url, true, 301);
            exit();
        }
        if(file_exists($extensionDirectory.$this->yellow->system->get("ShorterList"))) {
            $list = $this->yellow->toolbox->readFile($extensionDirectory.$this->yellow->system->get("ShorterList"));
            $list = str_replace(array("\r\n", "\r", "\n"), "\n", $list);
            $list = explode("\n", $list);
            foreach( $list as $l ){
                if(strpos($l, '||')){
                    $r = explode('||',trim($l));
                    if($surlID == trim($r[0])){                        
                        $url = $this->yellow->system->get("coreStaticUrl").($r[1]);
                        header("Location: ".$url, true, 301);
                        exit();
                    }
                }   
            }
        }
    }
}
