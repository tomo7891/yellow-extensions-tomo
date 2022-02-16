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
        $extensionLocation = $this->yellow->system->get("coreServerBase") . $this->yellow->system->get("coreExtensionLocation");
        $js = "{$extensionLocation}lazysizes.js";
      $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$js}\"></script>\n";
    }
    return $output;
  }

  public function onParsePageOutput($page, $text) {
    $output = null;
    $text = preg_replace_callback('/<(iframe|img)([^>]*)>/', function ($matches) {
      if (strpos($matches[2], 'lazyload') !== false) {
        $match = str_replace(' src=', ' data-src=', $matches[2]);
        return '<'.$matches[1].' loading="lazy"' . $match . '>';
      }else{
        return '<'.$matches[1].' ' . $matches[2] . '>';
      }
    }, $text);
    return $text;
  }
}
