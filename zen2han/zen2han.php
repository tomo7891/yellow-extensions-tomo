<?php
// ZenToHan extension

class YellowZen2han {
  const VERSION = "0.8.19";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow) {
    $this->yellow = $yellow;
  }

  public function onParsePageOutput($page, $text) {
    $text = mb_convert_kana($text, 'Kas', 'utf-8');
    return $text;
  }
}
