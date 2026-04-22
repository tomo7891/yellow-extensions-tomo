<?php
// Short URL extension

class YellowSurl {
    const VERSION = "0.8.21";
    public $yellow;

    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("SurlParam", "s");
        $this->yellow->system->setDefault("SurlList", "yellow-surl.ini");
    }

    public function onParsePageOutput($page, $text) {
        $surlParam = $this->yellow->system->get("SurlParam");
        $surlID = $page->getRequest($surlParam);
        
        if (is_string_empty($surlID)) return $text; // IDがなければ何もしない

        $redirectLocation = null;

        // 1. 各ページのメタデータ(surl: xxx)から探す
        $pages = $this->yellow->content->index()->filter("surl", $surlID);
        if (count($pages) == 1) {
            $redirectLocation = $pages->first()->getLocation();
        }

        // 2. 外部リスト(yellow-surl.ini)から探す
        if (!$redirectLocation) {
            $extensionDirectory = $this->yellow->system->get("coreExtensionDirectory");
            $fileName = $extensionDirectory . $this->yellow->system->get("SurlList");
            if (is_file($fileName)) {
                $list = $this->yellow->toolbox->readFile($fileName);
                foreach (explode("\n", str_replace(["\r\n", "\r"], "\n", $list)) as $line) {
                    if (strpos($line, '||') !== false) {
                        list($id, $loc) = explode('||', $line, 2);
                        if (trim($id) === $surlID) {
                            $redirectLocation = trim($loc);
                            break;
                        }
                    }
                }
            }
        }

        // リダイレクト実行
        if ($redirectLocation) {
            $url = $this->getAbsoluteUrl() . $redirectLocation;
            // すでに出力が始まっていないか確認してリダイレクト
            if (!headers_sent()) {
                header("Location: " . $url, true, 301);
                exit();
            }
        }
        
        return $text;
    }

    public function getAbsoluteUrl() {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $protocol = $isHttps ? "https" : "http";
        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
        return $protocol . '://' . $serverName;
    }
}
