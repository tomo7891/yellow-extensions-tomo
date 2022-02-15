<?php
// Debug extension

class YellowDebug {
  const VERSION = "0.8.19";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow) {
    $this->yellow = $yellow;
    $this->yellow->system->setDefault("debugMode", "0");
    define("DEBUG", $this->yellow->system->get("debugMode")); //1, 2, or 3
  }
}
