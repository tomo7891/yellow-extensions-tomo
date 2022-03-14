<?php
// Minify HTML extension

class YellowMinify
{
  const VERSION = "0.8.19";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow)
  {
    $this->yellow = $yellow;
    $this->yellow->system->setDefault("minify", "on");
    $this->yellow->system->setDefault("minifyExcludeContentType", "xml");
  }

  public function onParsePageOutput($page, $text)
  {
    if (preg_match("/{$this->yellow->system->get("minifyExcludeContentType")}/i", $page->getRequest("page"))) {
      return $text;
    }
    if ($this->yellow->system->get("minify") != "on" || $this->yellow->system->get("coreDebugMode") >= 1) {
      return $text;
    }

    //html
    $search = array(
      '/^\\s+|\\s+$/m'
    );
    $replace = array(
      ''
    );
    $text = preg_replace($search, $replace, $text);
    return $text;
  }
}
