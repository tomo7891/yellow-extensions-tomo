<?php
// Lazysizes extension
//https://github.com/aFarkas/lazysizes
class YellowLazysizes
{
    const VERSION = "0.8.10";
    public $yellow;         // access to API


    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
    }

    public function onParsePageExtra($page, $name)
    {
        $output = null;
        if ($name == 'header') {
                $extensionLocation = $this->yellow->system->get("coreServerBase").$this->yellow->system->get("coreExtensionLocation");
                //$output .= "<script type=\"text/javascript\" src=\"{$extensionLocation}lazysizes.min.js\"></script>\n";
                $output .= "<script type=\"text/javascript\" src=\"https://cdn.jsdelivr.net/npm/lazysizes@5.3.2/lazysizes.min.js\"></script>\n";
        }
        return $output;
    }

    public function onParsePageOutput($page, $text)
    {
        $output = null;
        $text = preg_replace_callback('/<img([^>]*)>/', function ($matches) {
            $match = rtrim($matches[1], '/');
            if(strpos($match,'lazyload') !== false){
            $match = str_replace(' src=', ' data-src=', $match);
          }
            return '<img'. $match .'>';
        }, $text);
        return $text;
      }
}
