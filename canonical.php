<?php
// Canonical extension

class YellowCanonical {
    const VERSION = "0.8.18";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }

    public function onParsePageExtra($page, $name)
    {
        $output = null;
        if ($name == 'header') {
          if($this->yellow->page->getHtml("canonical")){
            $cUrl = $this->yellow->page->getHtml("canonical");
          }else{
            $cUrl = $this->yellow->page->getUrl();
          }
          if($this->yellow->extension->get("edit")->editable){
            $cUrl = str_replace("edit/", "", $cUrl);
          }
          $output .= "<link rel=\"canonical\" href=\"{$cUrl}\">\n";
        }
        return $output;
    }
}
