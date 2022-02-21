<?php
// Luminous extension
// https://github.com/imgix/luminous

class YellowLuminous {
  const VERSION = "0.8.19";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow)
  {
    $this->yellow = $yellow;
    $this->yellow->system->setDefault("luminousClass", "zoom");
  }


    public function onParsePageExtra($page, $name) {
        $output = null;    
        $extensionLocation = $this->yellow->system->get("coreServerBase") . $this->yellow->system->get("coreExtensionLocation");    
        if ($name == 'header'){          
            $css = "{$extensionLocation}luminous.css";
            $output .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"{$css}\" />";
        }
        if ($name == 'footer') {          
          $class = $this->yellow->system->get("luminousClass");
          $js = "{$extensionLocation}luminous.js";
          $output .= "<script type=\"text/javascript\" src=\"{$js}\"></script>\n";
          $output .= "<script>";
          $output .= "new LuminousGallery(document.querySelectorAll('.{$class}'));";
          $output .= "</script>";
        } 
        return $output;
    }
}