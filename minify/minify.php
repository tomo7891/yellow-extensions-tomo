<?php
// Minify extension

class YellowMinify {
  const VERSION = "0.8.19";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow) {
    $this->yellow = $yellow;
    $this->yellow->system->setDefault("minify", "on");
    $this->yellow->system->setDefault("minifyExcludeContentType", "xml");
  }

  public function onParsePageOutput($page, $text) {
    if (preg_match("/{$this->yellow->system->get("minifyExcludeContentType")}/i", $page->getRequest("page"))) {
      return $text;
    }
    if ($this->yellow->system->get("minify") != "on" || defined("DEBUG") && DEBUG>=1) {
      return $text;
    }

    //html
    $search = array(
      '/\>[^\S ]+/s',      
      '/[^\S ]+\</s',     
      '/(\s)+/s',          
      '/<!--[\s\S]*?-->/s' 
  );
  $replace = array(
      '>',
      '<',
      '\\1',
      ''
  );
    $text = preg_replace($search, $replace, $text);
    return $text;
  }
}
