<?php
// Logger extension

class YellowLogger {
    const VERSION = "0.8.19";
     public $yellow;         // access to API

    // Handle initialization
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("noImage", "noimage.png");
        $this->yellow->system->setDefault("loggerRedirectsFile", "yellow-redirects.ini");
        $this->yellow->system->setDefault("loggerErrorFile", "yellow-error.log");
        $this->yellow->system->setDefault("loggerAccessFile", "yellow-access.log");
    }

    public function onParsePageOutput($page, $text) {
        $extensionDirectory = $this->yellow->system->get("coreExtensionDirectory");
        //Access Logger
        if (file_exists($extensionDirectory.$this->yellow->system->get("loggerAccessFile"))) {
            $h = $l = $u = $t = $r = $s = $b = $ref = $ua = null;
            $h = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR']: "-";
            $l = "-";
            $u = "-";
            $t = date("Y/m/d H:i:s");
            $m = $_SERVER['REQUEST_METHOD'];
            $uri = $_SERVER["REQUEST_URI"];
            $prot = $_SERVER["SERVER_PROTOCOL"];
            $s = $page->getStatusCode();
            $b = strlen($text);
            $ref = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "-";
            $ua = $_SERVER['HTTP_USER_AGENT'];
            $line = "{$h} {$l} {$u} [{$t}] \"{$m} {$uri} {$prot}\" {$s} {$b} \"{$ref}\" \"{$ua}\"";
            $this->yellow->toolbox->appendFile($extensionDirectory.$this->yellow->system->get("loggerAccessFile"), $line."\n");
        }
        //404 Logger
        if(file_exists($extensionDirectory.$this->yellow->system->get("loggerErrorFile"))) {
            if($page->getStatusCode() == '404'){
                $list = $this->yellow->toolbox->readFile($extensionDirectory.$this->yellow->system->get("loggerErrorFile"));
                $list = str_replace(array("\r\n", "\r", "\n"), "\n", $list);
                $list = explode("\n", $list);
                $line = $page->getLocation();
                if(!in_array($line, $list)){
                    $this->yellow->toolbox->appendFile($extensionDirectory.$this->yellow->system->get("loggerErrorFile"), $line."\n");
                }
            }
        }
        //301Redirect    
        if(file_exists($extensionDirectory.$this->yellow->system->get("loggerRedirectsFile"))) {
            $list = $this->yellow->toolbox->readFile($extensionDirectory.$this->yellow->system->get("loggerRedirectsFile"));
            $list = str_replace(array("\r\n", "\r", "\n"), "\n", $list);
            $list = explode("\n", $list);
            foreach( $list as $l ){
                if(strpos($l, '||')){
                    $r = explode("||",trim($l));
                    if($page->getLocation() == trim($r[0])){                        
                        $url = $this->yellow->system->get("coreStaticUrl").'/'.ltrim(trim($r[1]),'/');
                        $url = $this->yellow->lookup->normaliseUrl("", "", "", $url);
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
        list($target) = $this->yellow->toolbox->getTextArguments($text);
        if ($command=="reset") {
            if($target == "accesslog") {
                if(file_exists($extensionDirectory.$this->yellow->system->get("loggerAccessFile"))) {
                    file_put_contents($extensionDirectory.$this->yellow->system->get("loggerAccessFile"),'');
                    echo "Yellow $command: Reset your access log\n";
                    $statusCode = 200;
                }
            }
            if($target == "errorlog") {
                if(file_exists($extensionDirectory.$this->yellow->system->get("loggerErrorFile"))) {
                    file_put_contents($extensionDirectory.$this->yellow->system->get("loggerErrorFile"),'');
                    echo "Yellow $command: Reset your error log\n";
                    $statusCode = 200;
                }
            }
        }
        return $statusCode;
    }
}
