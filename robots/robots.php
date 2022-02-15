<?php
// RObots extension

class YellowRobots {
  const VERSION = "0.8.19";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow) {
    $this->yellow = $yellow;
  }

  public function onParsePageExtra($page, $name) {
    $output = null;
    if ($name == 'header') {
        if ($page->isExisting("robots")) {
            $robots = trim($page->get("robots"));
            $output .= "<meta name=\"robots\" content=\"{$robots}\" />\n";
        }
    }
    return $output;
  }
}
