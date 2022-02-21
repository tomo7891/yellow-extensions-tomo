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

    // Handle command
    public function onCommand($command, $text) {
        $statusCode = 0;
        $extensionDirectory = $this->yellow->system->get("coreExtensionDirectory");
        $accessLog = $extensionDirectory.$this->yellow->system->get("loggerAccessFile");
        $errorLog = $extensionDirectory.$this->yellow->system->get("loggerErrorFile");
        $redirects = $extensionDirectory.$this->yellow->system->get("loggerRedirectsFile");
        list($action, $target) = $this->yellow->toolbox->getTextArguments($text);
        if ($command=="logger") {
            if($action == "clean" || $action == "-c") {
                if($target == "both" || $target == "-b") {
                    if(file_exists($accessLog)) {
                        file_put_contents($accessLog,'');
                        echo "Yellow $command: Clean your access log\n";
                    }
                    if(file_exists($errorLog)) {
                        file_put_contents($errorLog,'');
                        echo "Yellow $command: Clean your error log\n";
                    }
                    $statusCode = 200;
                }
                elseif($target == "access" || $target == "-a") {
                    if(file_exists($accessLog)) {
                        file_put_contents($accessLog,'');
                        echo "Yellow $command: Clean your access log\n";
                        $statusCode = 200;
                    }
                }
                elseif($target == "error" || $target == "-e") {
                    if(file_exists($errorLog)) {
                        file_put_contents($errorLog,'');
                        echo "Yellow $command: Clean your error log\n";
                        $statusCode = 200;
                    }
                }else{
                    echo "logger clean access\n";
                    echo "logger clean error\n";
                    echo "logger clean both\n";

                }
            }
            if($action == "show" || $action == "-s") {
                if($target == "access" || $target == "-a") {
                    if(file_exists($accessLog)) {
                        echo $this->yellow->toolbox->readFile($accessLog);
                        $statusCode = 200;
                    }
                }
                elseif($target == "error" || $target == "-e") {
                    if(file_exists($accessLog)) {
                        echo $this->yellow->toolbox->readFile($errorLog);
                        $statusCode = 200;
                    }
                }
                elseif($target == "redirects" || $target == "-r") {
                    if(file_exists($accessLog)) {
                        echo $this->yellow->toolbox->readFile($redirects);
                        $statusCode = 200;
                    }
                }
                else{
                    echo "logger show access\n";
                    echo "logger show error\n";
                    echo "logger show redirects\n";
                }
            }
        }
        return $statusCode;
    }
    // Handle command help
    public function onCommandHelp() {
        $help = "logger [action target]\n";
        return $help;
    }
}
