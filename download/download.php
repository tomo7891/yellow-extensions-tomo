<?php
// Download extension

class YellowDownload
{
    const VERSION = "0.8.19";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("downloadList", "yellow-download.ini");
    }

    public function onParseContentShortcut($page, $name, $text, $type)
    {
        $output = null;
        if ($type == "block" || $type == "inline") {
            switch ($name) {
                case "download":
                    $output = $this->downloadLink($page, $name, $text);
                    break;
            }
        }
        return $output;
    }


    public function downloadLink($page, $name, $text)
    {
        $output = null;
        $hash = null;
        list($path) = $this->yellow->toolbox->getTextArguments($text);
        $path = "." . $path;
        if (file_exists($path)) {
            $this->addDownloadList($path);
            $hash = $this->searchHash($path);
        }
        if ($hash) {
            $url = $page->getLocation(true) . "download" . $this->yellow->toolbox->getLocationArgumentsSeparator() . $hash . "/";
            $title = basename($path);
            $output .= '<a href="' . $url . '">' . $title . '</a>';
        }
        if ($page->getRequest("download")) {
            $this->yellow->extension->get("download")->download($page);
        }
        return $output;
    }

    public function addDownloadList($path)
    {
        $extensionDirectory = $this->yellow->system->get("coreExtensionDirectory");
        $downloadList = './' . $extensionDirectory . $this->yellow->system->get("downloadList");
        if (file_exists($downloadList) && file_exists($path)) {
            if (!$this->searchHash($path)) {
                $line = $path . "||" . uniqid();
                $this->yellow->toolbox->appendFile($downloadList, $line . "\n");
            }
        }
    }

    public function searchPath($hash)
    {
        $extensionDirectory = $this->yellow->system->get("coreExtensionDirectory");
        $downloadList = './' . $extensionDirectory . $this->yellow->system->get("downloadList");
        if (file_exists($downloadList)) {
            $list = $this->yellow->toolbox->readFile($downloadList);
            $list = str_replace(array("\r\n", "\r", "\n"), "\n", $list);
            $list = explode("\n", $list);
            foreach ($list as $l) {
                if (strpos($l, '||')) {
                    $download = explode("||", trim($l));
                    if ($download[1] == $hash) {
                        return $download[0];
                        break;
                    }
                }
            }
        }
    }

    public function searchHash($path)
    {
        $extensionDirectory = $this->yellow->system->get("coreExtensionDirectory");
        $downloadList = './' . $extensionDirectory . $this->yellow->system->get("downloadList");
        if (file_exists($downloadList)) {
            $list = $this->yellow->toolbox->readFile($downloadList);
            $list = str_replace(array("\r\n", "\r", "\n"), "\n", $list);
            $list = explode("\n", $list);
            foreach ($list as $l) {
                if (strpos($l, '||')) {
                    $download = explode("||", trim($l));
                    if ($download[0] == $path) {
                        return $download[1];
                        break;
                    }
                }
            }
        }
    }


    public function searchDownloadList($hash)
    {
        $extensionDirectory = $this->yellow->system->get("coreExtensionDirectory");
        $downloadList = './' . $extensionDirectory . $this->yellow->system->get("downloadList");
        if (file_exists($downloadList)) {
            $list = $this->yellow->toolbox->readFile($downloadList);
            $list = str_replace(array("\r\n", "\r", "\n"), "\n", $list);
            $list = explode("\n", $list);
            foreach ($list as $l) {
                if (strpos($l, '||')) {
                    $download = explode("||", trim($l));
                    if ($download[1] == $hash) {
                        return true;
                        break;
                    }
                }
            }
        }
    }

    public function download($page, $pMimeType = null)
    {
        $hash = $page->getRequest("download");
        if ($this->searchDownloadList($hash)) {
            $file_path =  $this->searchPath($hash);
            if (!is_readable($file_path)) {
                die($file_path);
            }
            $mimeType = (isset($pMimeType)) ? $pMimeType
                : (new finfo(FILEINFO_MIME_TYPE))->file($file_path);
            if (!preg_match('/\A\S+?\/\S+/', $mimeType)) {
                $mimeType = 'application/octet-stream';
            }
            header('Content-Type: ' . $mimeType);
            header('X-Content-Type-Options: nosniff');
            header('Content-Length: ' . filesize($file_path));
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Connection: close');
            while (ob_get_level()) {
                ob_end_clean();
            }
            readfile($file_path);
            exit;
        }
    }
}
