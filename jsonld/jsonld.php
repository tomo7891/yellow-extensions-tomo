<?php
// Jsonld extension

class YellowJsonld {
  const VERSION = "0.8.19";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow) {
    $this->yellow = $yellow;
  }

  public function onParsePageExtra($page, $name) {
    $output = null;
    if ($name == 'header') {
      $list = null;
      $currentLocation = $page->getLocation(true);
      $pages = $this->yellow->content->path($currentLocation, true);
      $i = 1;
      foreach ($pages as $page) {
        $list[] = '{
          "@type": "ListItem",
          "position": '.$i.',
          "name": "'.$page->getHtml("title").'",
          "item": "'.$page->getUrl().'"
        }';
        $i++;
      }
      if($list > 0){
        $output .= '<script type="application/ld+json">{';
        $output .= '"@context": "https://schema.org/",';
        $output .= '"@type": "BreadcrumbList",';
        $output .= '"@name": "パンくずリスト",';
        $output .= '"itemListElement": [';
        $output .= implode(',',$list);
        $output .= ']';
        $output .= '}</script>';
        }
    }
    return $output;
  }
}