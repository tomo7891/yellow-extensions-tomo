<?php
// Webp extension, refined for Yellow Core

class YellowWebp {
    const VERSION = "0.8.26";
    public $yellow;

    // 初期化：デフォルト設定の登録
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("webpQuality", "100");
        $this->yellow->system->setDefault("webpDirectory", "webp");
        $this->yellow->system->setDefault("webpSupportType", "jpeg,jpg,png");
    }

    // ページ出力時にHTML内の画像URLをWebPに置換
    public function onParsePageOutput($page, $output) {
        // WebP対応ブラウザかつ、コマンドライン実行でない場合のみ処理
        $isWebpSupported = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
        
        if (!is_null($output) && $isWebpSupported && !$this->yellow->lookup->isCommandLine()) {
            // img, source, a, video タグの対象属性をスキャン 
            $output = preg_replace_callback('/<(img|source|a|video)([^>]+)>/u', function($matches) {
                $tag = $matches[1];
                $attr = $matches[2];
                $targets = array('src', 'srcset', 'href', 'poster', 'data-src');

                foreach ($targets as $target) {
                    if (preg_match('/' . $target . '=["\']([^"\']+\.(?:jpe?g|png))["\']/i', $attr, $m)) {
                        $webpUrl = $this->convert($m[1]);
                        if ($webpUrl) $attr = str_replace($m[1], $webpUrl, $attr);
                    }
                }
                return "<$tag$attr>";
            }, $output);
        }
        return $output;
    }

    // コマンドライン操作
    public function onCommand($command, $text) {
        $statusCode = 0;
        if ($command == "webp") {
            list($action) = $this->yellow->toolbox->getTextArguments($text);
            if ($action == "convert") {
                $statusCode = $this->commandConvert();
            } elseif ($action == "clean") {
                $statusCode = $this->commandClean();
            } else {
                echo "Yellow webp [action]\n";
                echo "Actions: convert, clean\n";
                $statusCode = 200;
            }
        }
        return $statusCode;
    }

    public function onCommandHelp() {
        return "webp [action]";
    }

    // WebP生成ロジック（場所の維持）
    public function convert($srcIn) {
        if (!$this->basic_checks()) return $srcIn;

        $url = rawurldecode($srcIn);
        // メディアロケーションから実際のファイルパスを特定
        $fileName = $this->yellow->lookup->findFileFromMediaLocation($url);
        if (is_string_empty($fileName) || !is_file($fileName)) return $srcIn;

        // 保存先パスの構築（元のディレクトリ構造を維持）
        $pathInfo = pathinfo($fileName);
        $webpDir = $pathInfo['dirname'] . '/' . $this->yellow->system->get("webpDirectory");
        $webpFile = $webpDir . '/' . $pathInfo['basename'] . '.webp';

        // すでに存在し、元ファイルより新しい場合は生成をスキップ
        if (is_file($webpFile) && filemtime($webpFile) >= filemtime($fileName)) {
            return $this->yellow->lookup->findMediaLocationFromFile($webpFile);
        }

        // 生成処理
        if (!is_dir($webpDir)) @mkdir($webpDir, 0777, true);
        
        $image = null;
        $mimeType = $this->yellow->toolbox->getMimeContentType($fileName); 
        if ($mimeType == "image/jpeg") $image = @imagecreatefromjpeg($fileName);
        if ($mimeType == "image/png") $image = @imagecreatefrompng($fileName);

        if ($image) {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            imagewebp($image, $webpFile, (int)$this->yellow->system->get("webpQuality"));
            if (PHP_VERSION_ID < 80500) @imagedestroy($image);

            // WebPの方がサイズが大きい場合は元ファイルを優先 
            if (is_file($webpFile) && filesize($webpFile) >= filesize($fileName)) {
                @unlink($webpFile);
                return $srcIn;
            }
            return $this->yellow->lookup->findMediaLocationFromFile($webpFile);
        }

        return $srcIn;
    }

    private function commandConvert() {
        echo "Converting images to WebP...\n";
        $files = $this->yellow->media->index(true, true);
        $count = 0;
        foreach ($files as $file) {
            if (preg_match('/\.(?:jpe?g|png)$/i', $file->fileName)) {
                if ($this->convert($this->yellow->lookup->findMediaLocationFromFile($file->fileName)) != $file->fileName) {
                    $count++;
                }
            }
        }
        echo "Done. $count images processed.\n";
        return 200;
    }

    private function commandClean() {
        // メディアディレクトリの絶対パスを取得
        $path = $this->yellow->system->get("coreMediaDirectory");
        $webpDirName = $this->yellow->system->get("webpDirectory");
        
        // 再帰レベルを 0 に指定して、全階層のディレクトリを取得します
        $directories = $this->yellow->toolbox->getDirectoryEntriesRecursive($path, "/.*/", true, true, true, 0);
        
        $count = 0;
        foreach ($directories as $dir) {
            // 取得したディレクトリの末尾が設定した webp フォルダ名と一致するか確認
            if (basename($dir) == $webpDirName) {
                if ($this->yellow->toolbox->deleteDirectory($dir)) {
                    echo "Deleted: $dir\n";
                    $count++;
                }
            }
        }
        
        if ($count == 0) {
            echo "No webp directories found in $path\n";
        } else {
            echo "Success: $count webp directories deleted.\n";
        }
        
        return 200;
    }

    private function basic_checks() {
        return function_exists('imagewebp');
    }
}
