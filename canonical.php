<?php
// Canonical extension

class YellowCanonical
{
  const VERSION = "0.8.18";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow)
  {
    $this->yellow = $yellow;
  }

  public function onParsePageExtra($page, $name)
  {
    $output = null;

    if ($name == 'header') {
      //set canonical url
      $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
      if ($this->yellow->page->getHtml("canonical")) {
        $url = $this->yellow->page->getHtml("canonical");
      } else {
        if ($this->yellow->extension->get("edit")->editable) {
          $url = str_replace("edit/", "", $url);
        }
      }
      $output .= "<link rel=\"canonical\" href=\"{$url}\">\n";
    }
    return $output;
  }
}

if (PHP_SAPI != "cli") {
  $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

  //normalization
  if (strpos($url, 'www') && !strpos($url, 'localhost')) {
    $url = str_replace('www.', '', $url);
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: {$url}");
    exit;
  }

  if (empty($_SERVER['HTTPS']) && !strpos($url, 'localhost')) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: {$url}");
    exit;
  }
}
