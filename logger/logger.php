<?php
// Logger extension

class YellowLogger
{
    const VERSION = "0.8.20";
    public $yellow;         // access to API

    // Handle initialization
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("loggerLocation", "/system/extensions/");
        $this->yellow->system->setDefault("loggerErrorFile", "yellow-error.log");
        $this->yellow->system->setDefault("loggerAccessFile", "yellow-access.log");
        $this->yellow->system->setDefault("loggerRedirectsFile", "yellow-redirects.ini");
    }

    public function onParsePageOutput($page, $text)
    {
        $loggerLocation = "." . $this->yellow->system->get("loggerLocation");
        //Access Logger
        if (file_exists($loggerLocation . $this->yellow->system->get("loggerAccessFile"))) {
            $h = $l = $u = $t = $r = $s = $b = $ref = $ua = null;
            $h = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "-";
            $l = "-";
            $u = "-";
            $t = date("Y/m/d H:i:s");
            $m = !empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : "-";
            $uri = !empty($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "-";
            $prot = !empty($_SERVER["SERVER_PROTOCOL"]) ? $_SERVER["SERVER_PROTOCOL"] : "-";
            $s = $page->getStatusCode();
            $b = strlen($text);
            $ref = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "-";
            $ua = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "-";
            $line = "{$h} {$l} {$u} [{$t}] \"{$m} {$uri} {$prot}\" {$s} {$b} \"{$ref}\" \"{$ua}\"";
            $this->yellow->toolbox->appendFile($loggerLocation . $this->yellow->system->get("loggerAccessFile"), $line . "\n");
        }
        //404 Logger
        if (file_exists($loggerLocation . $this->yellow->system->get("loggerErrorFile"))) {
            if ($page->getStatusCode() == '404') {
                $list = $this->yellow->toolbox->readFile($loggerLocation . $this->yellow->system->get("loggerErrorFile"));
                $list = str_replace(array("\r\n", "\r", "\n"), "\n", $list);
                $list = explode("\n", $list);
                $line = $page->getLocation();
                if (!in_array($line, $list)) {
                    $this->yellow->toolbox->appendFile($loggerLocation . $this->yellow->system->get("loggerErrorFile"), $line . "\n");
                }
            }
        }
    }

    // Handle page layout
    public function onParsePageLayout($page, $name)
    {
        $loggerLocation = $this->yellow->system->get("loggerLocation");
        if (file_exists($loggerLocation . $this->yellow->system->get("loggerRedirectsFile"))) {
            $list = $this->yellow->toolbox->readFile($loggerLocation . $this->yellow->system->get("loggerRedirectsFile"));
            $list = str_replace(array("\r\n", "\r", "\n"), "\n", $list);
            $list = explode("\n", $list);
            foreach ($list as $l) {
                if (strpos($l, '||')) {
                    $r = explode("||", trim($l));
                    if ($page->getLocation() == trim($r[0])) {
                        $page->error(301, "redirecting....");
                        $url = $this->getAbsoluteUrl() . '/' . ltrim(trim($r[1]), '/');
                        $url = $this->yellow->lookup->normaliseUrl("", "", "", $url);
                        header("Location: " . $url, true, 301);
                        exit();
                    }
                }
            }
        }
    }

    // Handle command
    public function onCommand($command, $text)
    {
        $statusCode = 0;
        $loggerLocation = $this->yellow->system->get("loggerLocation");
        $accessLog = $loggerLocation . $this->yellow->system->get("loggerAccessFile");
        $errorLog = $loggerLocation . $this->yellow->system->get("loggerErrorFile");
        $redirects = $loggerLocation . $this->yellow->system->get("loggerRedirectsFile");
        list($action, $target) = $this->yellow->toolbox->getTextArguments($text);
        if ($command == "logger") {
            if ($action == "clean" || $action == "-c") {
                if ($target == "both" || $target == "-b") {
                    if (file_exists($accessLog)) {
                        file_put_contents($accessLog, '');
                        echo "Yellow $command: Clean your access log\n";
                    }
                    if (file_exists($errorLog)) {
                        file_put_contents($errorLog, '');
                        echo "Yellow $command: Clean your error log\n";
                    }
                    $statusCode = 200;
                } elseif ($target == "access" || $target == "-a") {
                    if (file_exists($accessLog)) {
                        file_put_contents($accessLog, '');
                        echo "Yellow $command: Clean your access log\n";
                        $statusCode = 200;
                    }
                } elseif ($target == "error" || $target == "-e") {
                    if (file_exists($errorLog)) {
                        file_put_contents($errorLog, '');
                        echo "Yellow $command: Clean your error log\n";
                        $statusCode = 200;
                    }
                } else {
                    echo "logger clean access\n";
                    echo "logger clean error\n";
                    echo "logger clean both\n";
                }
            }
            if ($action == "show" || $action == "-s") {
                if ($target == "access" || $target == "-a") {
                    if (file_exists($accessLog)) {
                        echo $this->yellow->toolbox->readFile($accessLog);
                        $statusCode = 200;
                    }
                } elseif ($target == "error" || $target == "-e") {
                    if (file_exists($accessLog)) {
                        echo $this->yellow->toolbox->readFile($errorLog);
                        $statusCode = 200;
                    }
                } elseif ($target == "redirects" || $target == "-r") {
                    if (file_exists($accessLog)) {
                        echo $this->yellow->toolbox->readFile($redirects);
                        $statusCode = 200;
                    }
                } else {
                    echo "logger show access\n";
                    echo "logger show error\n";
                    echo "logger show redirects\n";
                }
            }
        }
        return $statusCode;
    }

    // Handle command help
    public function onCommandHelp()
    {
        $help = "logger [action target]\n";
        return $help;
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
