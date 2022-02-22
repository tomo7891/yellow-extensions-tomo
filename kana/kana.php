<?php
// Convert Kana extension

class YellowKana {
  const VERSION = "0.8.19";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow) {
    $this->yellow = $yellow;
    $this->yellow->system->setDefault("kanaConvert", "on");
    $this->yellow->system->setDefault("kanaOption", "KasV");
  }

  public function onParsePageOutput($page, $text) {
    if ($this->yellow->system->get("kanaConvert") != "on" || defined("DEBUG") && DEBUG>=1) {
      return $text;
    }
    $text = mb_convert_kana($text, $this->yellow->system->get("kanaConvert"), 'utf-8');
    return $text;
  }
}
