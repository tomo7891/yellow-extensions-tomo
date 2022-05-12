<?php
// Convert Kana extension

class YellowKana
{
  const VERSION = "0.8.20";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow)
  {
    $this->yellow = $yellow;
    $this->yellow->system->setDefault("kanaConvert", "on");
    $this->yellow->system->setDefault("kanaOption", "KasV");
  }

  public function onParsePageOutput($page, $text)
  {
    $output = null;
    if ($this->yellow->system->get("kanaConvert") == "on"  $this->yellow->system->get("coreDebugMode") < 1) {
      $output = mb_convert_kana($text, $this->yellow->system->get("KanaOption"), 'utf-8');
    }
    return $output;
  }
}
