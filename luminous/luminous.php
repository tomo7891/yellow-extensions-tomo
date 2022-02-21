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
            $output .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"{$extensionLocation}luminous.css\" />";
        }
        if ($name == 'footer') {          
          $class = $this->yellow->system->get("luminousClass");
          $output .= "<script type=\"text/javascript\" src=\"{$extensionLocation}luminous.js\"></script>\n";
          $output .= "<script>";
          $output .= "new LuminousGallery(document.querySelectorAll('.{$class}'));";
          $output .= "</script>";
        } 
        return $output;
    }
}