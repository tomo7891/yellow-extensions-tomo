<?php
// webp extension, https://github.com/
// from https://github.com/mplavala/webpconverter

class YellowWebp
{
    const VERSION = "0.8.19";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("webpExcludeContentType", "xml");
        $this->yellow->system->setDEfault("webpDirectory", "webp");
        $this->yellow->system->setDEfault("webpSupportType", "jpeg,png");
    }

    public function onParsePageOutput($page, $text) {
        $output = null;
        $content = $text;
        if(empty($content))return;
        if ((isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false) and $this->basic_checks()) {
          //fix for xml
            if (preg_match("/{$this->yellow->system->getHtml("webpExcludeContentType")}/i", $page->getRequest("page"))) {
                return;
            }
            // webp is supported!
            // and we have all the needed functions
            $dom = new DOMDocument();
            $internalErrors = libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
            // standard image
            foreach ($dom->getElementsByTagName('img') as $node) {
                $this->serve_node($node, 'src');
                $this->serve_node($node, 'data-src');
            }
            //Link
            foreach ($dom->getElementsByTagName('a') as $node) {
                $this->serve_node($node, 'href');
            }
            // video poster
            foreach ($dom->getElementsByTagName('video') as $node) {
                $this->serve_node($node, 'poster');
            }
            // srcset inside picture
            foreach ($dom->getElementsByTagName('source') as $node) {
                $this->serve_node($node, 'srcset');
            }
            $html = $dom->saveHTML();
            if ($html !== false) {
                $output = html_entity_decode($html);
            }
        }
        return $output;
    }

    // Handle command
    public function onCommand($command, $text) {
        $statusCode = 0;
        list($action) = $this->yellow->toolbox->getTextArguments($text);
        $coreMediaDirectory = $this->yellow->system->get("CoreMediaDirectory");
        $imageDir = explode("/",$this->yellow->system->get("CoreImageDirectory"));
        $thumbDir = explode("/",$this->yellow->system->get("ImageThumbnailDirectory"));
        if ($command=="webp") {
            if($action == "convert" || $action == "-c") {
                    $pattern = "(".$imageDir[1]."|".$thumbDir[1].")/.*?.(jpe?g|png)";
                    $files = $this->yellow->media->index(true, true)->match("#$coreMediaDirectory$pattern#");
                    foreach ($files as $file) {
                        $webp = $this->get_webp_path($file->fileName).".webp";
                        if(!file_exists(".$webp")){
                            $this->convert($file->fileName);                        
                            echo "Success: /$file->fileName => $webp\n";
                        }
                    }
                echo "Finished: convert";
                $statusCode = 200;
            }
            elseif($action == "delete" || $action == "-d") {
                $path = "./".$this->yellow->system->get("CoreMediaDirectory").$this->yellow->system->get("webpDirectory");
                if(file_exists($path)){                                 
                    $this->yellow->toolbox->deleteDirectory($path);
                    echo "Success: delete webp Directory";
                } else {
                    echo "Error: no exists webp directory";
                }
                $statusCode = 200;
            }else{
                echo "webp [convert or -c]\n";
                echo "webp [delete or -d]\n";
            }
        }
        return $statusCode;
    }

    // Handle command help
    public function onCommandHelp() {
        return "webp [action]\n";
    }

    public function basic_checks() {
        if (!function_exists('mime_content_type')) {
            $this->yellow->log('error', 'PHP function mime_content_type does not exist.', 'WebP Converter - function does not exist');
            return false;
        }
        if (!function_exists('imagewebp')) {
            $this->yellow->log('error', 'PHP function imagewebp does not exist.', 'WebP Converter - function does not exist');
            return false;
        }
    return true;
    }

    public function check_cache_folder() {
        $path = "./".$this->yellow->system->get("CoreMediaDirectory").$this->yellow->system->get("webpDirectory");
        if (!file_exists($path)) {
            // folder does not exist
            mkdir($path, 0777, true);
        }
        if (!file_exists($path . '/.htaccess')) {
            // there is no .htaccess
            file_put_contents($path . '/.htaccess', "order deny,allow\nallow from all\n");
        }
    }

    public function serve_node($node, $attribute) {
        if ($node->getAttribute('data-webpconverter-exclude') == 1 ||
            $node->getAttribute('data-webpconverter-exclude') == 'on' ||
            $node->getAttribute('data-webpconverter-exclude') == 'yes') {
            // the attribute data-webpconverter-exclude exists and turned on
            return;
        }
        if ($node->getAttribute($attribute)) {
        // the attribute exists, we can handle it
        $src = $node->getAttribute($attribute);
        $node->setAttribute($attribute, $this->convert($src));
        }
    }

    public function get_absolute_path($path, $URI) {
        if ($path[0] != '/') {
            // relative path
            $URIArray = explode('/', $URI);
            array_pop($URIArray);
            $container = implode('/', $URIArray);
            // return absolute path with container
            return $container . '/' . $path;
        } else {
            // absolute path, just return original
            return $path;
        }
    }

    public function get_webp_filename($fileName) {
        return $fileName . '.webp';
    }

    public function get_webp_path($path) {
        // remove media from path and explode
        $path = str_replace('media', '', $path);
        $pathArray = explode('/', $path);

        // we start from $cahcePath
        $finalPath = $this->yellow->system->get("CoreMediaDirectory").$this->yellow->system->get("webpDirectory");

        // add folder structure to $cachePath
        foreach ($pathArray as $folder) {
            if (!empty($folder)) {
                $finalPath .= '/' . $folder;
            }
        }
        // return final path, starting with /
        return '/' . $finalPath;
    }



    public function convert($srcIn){
        // check cache folder first
        $this->check_cache_folder();

        $src = rawurldecode($srcIn);
        // change relative path to absolute path, starting with /
        if (!isset($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
        }
        $src = $this->get_absolute_path($src, $_SERVER['REQUEST_URI']);

        // file path on server, including file name
        $srcServerFile = './' . ltrim($src, '/');

        if (file_exists($srcServerFile)) {
        // we set the MIME type as variable to test whether it is supported
        // if file_exists evaluates to false, then we return original input
            $srcMime = mime_content_type($srcServerFile);
        } else {
            if (defined("DEBUG") && DEBUG>=1) {
                $this->yellow->log('error', 'Image ' . $srcIn . ' does not exist. Either there is no file at ' . $srcServerFile . ', or the path is not accessible.', 'WebP Converter - image does not exist');
                }
            return $srcIn;
        }

        $mime = array();
        $supportType = $this->yellow->system->get("webpSupportType");
        $supportType = explode(",", $supportType);
        foreach($supportType as $key => $val){
            $mime[] = 'image/'.$val;
        }  
        if (!in_array($srcMime, $mime)) {
            // unsupported MIME type or image does not exist
            // returning original input
            if (defined("DEBUG") && DEBUG>=1) {
                if ($srcMime == '') {
                    $this->yellow->log('error', 'The MIME type of image ' . $srcIn . ' cannot be correctly detected.', 'WebP Converter - MIME type cannot be detected');
                } else {
                    $this->yellow->log('error', 'Unsuported MIME type. Image ' . $srcIn . ' has MIME type ' . $srcMime . '. Only ' . implode(', ', $mime) . ' are supported.', 'WebP Converter - unsupported MIME type');
                }
            }
            return $srcIn;
        }

        $filename = pathinfo($src)['basename'];
        $path = pathinfo($src)['dirname'];

        // create new file name and path
        $webpFileName = $this->get_webp_filename($filename);
        $webpPath = $this->get_webp_path($path);
        // webp absolute path for src
        $webpSrc = $webpPath . '/' . $webpFileName;
        $webpServerPath = './' . ltrim($webpPath, '/');
        $webpServerFile = $webpServerPath . '/' . $webpFileName;

        if (!file_exists($webpServerFile) or (filectime($webpServerFile) < filectime($srcServerFile))) {
            // image does not exist or is outdated
            switch ($srcMime) {
                case 'image/jpeg':
                    $image = @imagecreatefromjpeg($srcServerFile);
                    imagepalettetotruecolor($image);
                    break;
                case 'image/png':
                    $image = @imagecreatefrompng($srcServerFile);
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                    break;
                default:
                    // despite our best effort, we somehow get unsupported MIME type
                    // returning original input
                    return $srcIn;
            }

            if (!file_exists($webpServerPath)) {
                // folder does not exist
                mkdir($webpServerPath, 0777, true);
            }
            // create webp image
            imagewebp($image, $webpServerFile);
            // free up memory
            imagedestroy($image);
        }

        if (file_exists($webpServerFile) && filesize($webpServerFile) > 0 ) {
            // make sure the file really exists and that it is not a damaged
            if (filesize($webpServerFile) < filesize($srcServerFile)) {
                // the WebP image is smaller
                return $webpSrc;
            } else {
                // the original image is smaller
                if (defined("DEBUG") && DEBUG>=1) {
                    $this->yellow->log('error', 'Image ' . $srcIn . ' was supposed to be replaced by ' . $webpServerFile . ', but the WebP version is larger than original.', 'WebP Converter - WebP image larger than original');
                }
                return $srcIn;
            }
        }
        // either unsupported MIME type or file creation failed
        // returning original input
        if (defined("DEBUG") && DEBUG>=1) {
            $this->yellow->log('error', 'Image ' . $srcIn . ' was supposed to be replaced by ' . $webpServerFile . ', but the WebP version does not exist, or is corrupted.', 'WebP Converter - WebP image failure');
        }
        return $srcIn;
    }
}