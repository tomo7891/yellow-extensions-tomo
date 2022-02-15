<?php
// https://github.com/aFarkas/lazysizes
class YellowLazysizes {
  const VERSION = "0.8.19";
  public $yellow;         // access to API


  public function onLoad($yellow) {
    $this->yellow = $yellow;
  }

  public function onParsePageExtra($page, $name) {
    $output = null;
    if ($name == 'header') {
      if(isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER["HTTP_HOST"], ".dev") || strpos($_SERVER["HTTP_HOST"], "localhost"))){
        $extensionLocation = $this->yellow->system->get("coreServerBase") . $this->yellow->system->get("coreExtensionLocation");
        $js = "{$extensionLocation}lazysizes.js";
      }else{
        $js = "//cdn.jsdelivr.net/npm/lazysizes@5.3.2/lazysizes.min.js";
      }      
      $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$js}\"></script>\n";
    }
    return $output;
  }

  public function onParsePageOutput($page, $text) {
    $output = null;
    $text = preg_replace_callback('/<img([^>]*)>/', function ($matches) {
      if (strpos($matches[1], 'lazyload') !== false) {
        $match = str_replace(' src=', ' data-src=', $matches[1]);
        return '<img loading="lazyload"' . $match . '>';
      } else {
        if (strpos($matches[1], 'nolazy') !== false) {
          return '<img' . $matches[1] . '>';
        } else {
          return '<img loading="lazy"' . $matches[1] . '>';
        }
      }
    }, $text);
    return $text;
  }
}
