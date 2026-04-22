<?php
// Cloak extension with Counter and Expiry support

class YellowCloak {
    const VERSION = "0.9.3";
    public $yellow;

    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("cloakList", "yellow-cloak.ini");
        $this->yellow->system->setDefault("cloakDirectory", "media/downloads/cloak/");
        $this->yellow->system->setDefault("cloakLocation", "/cloak/");
        
        $this->yellow->language->setDefaults(array(
            "Language: en",
            "CloakNoFile: no file...",
            "CloakExpired: link expired...",
            "Language: ja",
            "CloakNoFile: ファイルが存在しません。",
            "CloakExpired: リンクの有効期限が切れています。"
        ));
    }

    public function onRequest($scheme, $address, $base, $location, $fileName) {
        $cloakLocation = $this->yellow->system->get("cloakLocation");
        if (substru($location, 0, strlenu($cloakLocation)) == $cloakLocation) {
            $hash = trim(substru($location, strlenu($cloakLocation)), "/");
            // PHP 8.5 では戻り値を明示的に
            $result = $this->processDownload($hash);
            return ($result === 0) ? 0 : $result;
        }
        return 0;
    }

    public function onParseContentElement($page, $name, $text, $attributes, $type) {
        if ($name == "cloak") {
            return $this->cloakLink($page, $text);
        }
        if ($name == "cloaklist") {
            return $this->cloakList($page, $text);
        }
        return null;
    }

    public function cloakLink($page, $text) {
        list($filename, $title, $days) = $this->yellow->toolbox->getTextArguments($text);
        $cloakDirectory = $this->yellow->system->get("cloakDirectory");
        $path = $cloakDirectory . $filename;

        if (!is_file($path)) return $this->yellow->language->getText("cloakNoFile");

        // 日数計算の厳密化
        $expiry = !is_string_empty($days) ? time() + ((int)$days * 86400) : 0;
        $hash = $this->getHash($path, $expiry);
        
        $url = $this->yellow->system->get("coreServerBase") . $this->yellow->system->get("cloakLocation") . $hash . "/";
        $title = !is_string_empty($title) ? $title : basename($path);

        return '<a href="' . $url . '">' . htmlspecialchars($title) . '</a>';
    }

    public function cloakList($page, $text) {
        list($class) = $this->yellow->toolbox->getTextArguments($text);
        $cloakListFile = $this->yellow->system->get("coreExtensionDirectory") . $this->yellow->system->get("cloakList");
        $data = $this->loadCloakData($cloakListFile);
        
        $output = '<ul class="' . $this->yellow->lookup->normaliseClass($class) . '">';
        foreach ($data as $path => $info) {
            $url = $this->yellow->system->get("coreServerBase") . $this->yellow->system->get("cloakLocation") . $info['hash'] . "/";
            $output .= '<li><a href="' . $url . '">' . htmlspecialchars(basename((string)$path)) . '</a>';
            $output .= ' (Downloads: ' . (int)$info['count'] . ')</li>';
        }
        $output .= '</ul>';
        return $output;
    }

    private function getHash($path, $expiry) {
        $cloakListFile = $this->yellow->system->get("coreExtensionDirectory") . $this->yellow->system->get("cloakList");
        $data = $this->loadCloakData($cloakListFile);

        if (isset($data[$path])) return $data[$path]['hash'];

        // current_time() を time() に、uniqid の引数を安全に変更
        $hash = sha1(uniqid((string)time(), true));
        $line = "$path || $hash || 0 || $expiry\n";
        $this->yellow->toolbox->appendFile($cloakListFile, $line);
        return $hash;
    }

    private function processDownload($hash) {
        $cloakListFile = $this->yellow->system->get("coreExtensionDirectory") . $this->yellow->system->get("cloakList");
        $data = $this->loadCloakData($cloakListFile);
        
        foreach ($data as $path => $info) {
            if ($info['hash'] === $hash) {
                if ($info['expiry'] > 0 && time() > $info['expiry']) {
                    return $this->yellow->page->error(403, $this->yellow->language->getText("cloakExpired"));
                }

                $data[$path]['count']++;
                $this->saveCloakData($cloakListFile, $data);

                $this->downloadFile($path);
                exit;
            }
        }
        return 404;
    }

    private function downloadFile($path) {
        if (is_readable($path)) {
            header("Content-Type: " . $this->yellow->toolbox->getMimeContentType($path));
            header("Content-Disposition: attachment; filename=\"" . basename($path) . "\"");
            header("Content-Length: " . $this->yellow->toolbox->getFileSize($path));
            header("X-Content-Type-Options: nosniff"); // セキュリティ強化
            while (ob_get_level()) ob_end_clean();
            readfile($path);
        }
    }

    private function loadCloakData($fileName) {
        $data = array();
        if (is_file($fileName)) {
            $content = $this->yellow->toolbox->readFile($fileName);
            $lines = $this->yellow->toolbox->getTextLines($content);
            foreach ($lines as $line) {
                $parts = explode(" || ", trim($line));
                if (count($parts) >= 2) {
                    $data[$parts[0]] = array(
                        'hash'   => $parts[1],
                        'count'  => isset($parts[2]) ? (int)$parts[2] : 0,
                        'expiry' => isset($parts[3]) ? (int)$parts[3] : 0
                    );
                }
            }
        }
        return $data;
    }

    private function saveCloakData($fileName, $data) {
        $output = "";
        foreach ($data as $path => $info) {
            $output .= "$path || {$info['hash']} || {$info['count']} || {$info['expiry']}\n";
        }
        $this->yellow->toolbox->writeFile($fileName, $output);
    }
}