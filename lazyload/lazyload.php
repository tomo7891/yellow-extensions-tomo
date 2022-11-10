<?php
class YellowLazyload
{
  const VERSION = "0.8.22";
  public $yellow;         // access to API


  public function onLoad($yellow)
  {
    $this->yellow = $yellow;
  }

  public function onParsePageOutput($page, $text)
  {
    $output = null;
    $text = preg_replace_callback('/<(iframe|img)([^>]*)>/', function ($matches) {
      if (strpos($matches[2], 'class=' ) !== false && strpos($matches[2], 'lazy') !== false) {
        return '<' . $matches[1] . $matches[2] . ' loading="lazy"' . '>';
      } else {
        return '<' . $matches[1] . ' ' . $matches[2] . '>';
      }
    }, $text);
    return $text;
  }
}
