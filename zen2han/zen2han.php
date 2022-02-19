<?php
// ZenToHan extension

class YellowZen2han {
  const VERSION = "0.8.19";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow) {
    $this->yellow = $yellow;
    $this->yellow->system->setDefault("zen2han", "on");
  }

  public function onParsePageOutput($page, $text) {
    if ($this->yellow->system->get("zen2han") != "on" || defined("DEBUG") && DEBUG>=1) {
      return $text;
    }
    $text = mb_convert_kana($text, 'KasV', 'utf-8');
    return $text;
  }
}
