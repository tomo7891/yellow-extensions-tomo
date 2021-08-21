<?php
// from https://github.com/mplavala/webpconverter

class YellowWebp
{
    const VERSION = "0.8.16";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("webpExcludeContentType", "xml");
        $this->yellow->system->setDefault("webpBasePath", "/");
    }

    public function onParsePageOutput($page, $text)
    {
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

public function basic_checks(){
  return function_exists('mime_content_type') and function_exists('imagewebp');
}

public function check_cache_folder(){
  $path = "./media/webp/";
  if (!file_exists($path)) {
    // folder does not exist
    mkdir($path, 0777, true);
  }
  if (!file_exists($path . '.htaccess')) {
    // there is no .htaccess
    file_put_contents($path . '.htaccess', "order deny,allow\nallow from all\n");
  }
}

public function serve_node($node, $atribute){
  if ($node->getAttribute($atribute)) {
    // the attribute exists, we can handle it
    $src = $node->getAttribute($atribute);
    $node->setAttribute($atribute, $this->convert($src));
  }
}

public function get_absolute_path($path, $URI){
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

public function get_webp_filename($fileName){
  return $fileName . '.webp';
}

public function get_webp_path($path){
  // remove assets from path and explode
  $pathArray = explode("/", $path);
  // we start from $cahcePath
  $finalPath = "/media/webp";
  // add folder structure to $cachePath
  foreach ($pathArray as $folder) {
    if (!empty($folder)) {
      $finalPath .= '/' . $folder;
    }
  }
  // return final path, startin with /
  return $finalPath;
}

public function convert($srcIn){
  // check cache folder first
  $this->check_cache_folder();
  $src = rawurldecode($srcIn);
  $bathPath = $this->yellow->system->getHtml("webpBasePath");

  // change relative path to absolute path, starting with
  $src = $this->get_absolute_path($src, $_SERVER['REQUEST_URI']);
  $srcMime = '';
  // file path on server, including file name
  $srcServerFile = './' . ltrim($src, '/');
  $srcServerFile = str_replace($bathPath,"", $srcServerFile);


  if (file_exists($srcServerFile)) {
    // we set the MIME type as variable to test whether it is supported
    // if file_exists evaulates to false, then $srcMime is empty, hence not valid MIME type
    // if MIME type is not valid, then we return the original path later
    $srcMime = mime_content_type($srcServerFile);
  }

  if (in_array($srcMime, ['image/jpeg','image/png'])) {
    $filename = pathinfo($src)['basename'];
    $path = pathinfo($src)['dirname'];
    $path = str_replace($bathPath.'/media', '', $path);

    // create new file name and path
    $webpFileName = $this->get_webp_filename($filename);
    $webpPath = $this->get_webp_path($path);

    // webp absolute path for src
    $webpSrc = $bathPath . $webpPath . '/' . $webpFileName;


    // webp path on server, without file name
    $webpServerPath = './' . ltrim($webpPath, '/');

    // webp path on server, with file name
    $webpServerFile = $webpServerPath . '/' . $webpFileName;

    if (!file_exists($webpServerFile) or (filectime($webpServerFile) < filectime($srcServerFile))) {
      // image does not exist or is outdated
      if ($srcMime == 'image/jpeg') {
        $image =  imagecreatefromjpeg($srcServerFile);
        imagepalettetotruecolor($image);
      }
      if ($srcMime == 'image/png') {
        $image =  imagecreatefrompng($srcServerFile);
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
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
    if (file_exists($webpServerFile) && filesize($webpServerFile) > 0) {
      // make sure the file really exists and that is not a damaged file (size greater than 0)
      return $webpSrc;
    }
  }
  // either unsupported MIME type or file creation failed
  // returning original input
  return $srcIn;
}
}
