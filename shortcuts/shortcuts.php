<?php
// Short Cuts extension

class YellowShortcuts {
  const VERSION = "0.8.19";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow) {
    $this->yellow = $yellow;
  }

  public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if($type=="block" || $type=="inline") {
            switch ($name) {
                case "page":
                    $output = $this->getPageValue($page, $name, $text);
                    break;
                case "system":
                    $output = $this->getSystemValue($page, $name, $text);
                    break;
                case "user":
                    $output = $this->getSystemValue($page, $name, $text);
                    break;
                case "language":
                    $output = $this->getSystemValue($page, $name, $text);
                    break;
                case "date":
                    $output = $this->getPageDate($page, $name, $text);
                    break;
                case "url":
                    $output = $this->getUrlLink($page, $name, $text);
                    break;
                case "pages":
                    $output = $this->getPages($page, $name, $text);
                    break;
            }
        }
        return $output;
    }

  //[$name $key $encode]
  public function getSystemValue($page, $name, $text) {
        $output = null;
        if (empty($text)) {
            return $output;
        }
        list($key, $encode) = $this->yellow->toolbox->getTextArguments($text);
        if($name == 'user') {
            if (empty($encode)) {
                $encode = 'false';
            }
            if ($encode != 'true') {
                $output = $this->yellow->user->getUser("name");
            } else {
                $output = $this->yellow->user->getUserHtml("name");
            }
            return $output;
        } elseif($name == 'language') {
            if (empty($encode)) {
                $encode = 'false';
            }
            if ($encode != 'true') {
                $output = $this->yellow->language->getText($key);
            } else {
                $output = $this->yellow->language->getTextHtml($key);
            }
            return $output;
        } elseif($name == 'system') {
            if (empty($encode)) {
                $encode = 'false';
            }
            if ($encode != 'true') {
                $output = $this->yellow->$name->get($key);
            } else {
                $output = $this->yellow->$name->getHTML($key);
            }
            return $output;
        }
    }

    //[page path key encode]
    public function getPageValue($page, $name, $text) {
        $output = null;
        if (empty($text)) {
            return $output;
        }
        list($path, $key, $encode) = $this->yellow->toolbox->getTextArguments($text);
        if(empty($path)){
            $page = $page;
        }else{
            $page = $this->yellow->content->find($path);
        }
        if (empty($encode) || $encode = 'false') {
            return $page->get($key);
        }
        if ($encode == 'true') {
            return $page->getHTML($key);
        }
    }

    //[date key format encode]
    public function getPageDate($page, $name, $text) {
        $output = null;
        list($key, $format, $encode) = $this->yellow->toolbox->getTextArguments($text);
        if (empty($encode)) {
            $encode = 'false';
        }
        if (empty($format)) {
            $format = "CoreDateFormatMedium";
        }
        if ($encode != 'true') {
            $output = $page->getDate($key, $format);
        } else {
            $output = $page->getDateHtml($key, $format);
        }
        return $output;
    }

    //[url path title]
    public function getUrlLink($page, $name, $text) {
        $output = null;
        $hash = null;
        list($path, $title) = $this->yellow->toolbox->getTextArguments($text);
        if (strpos($path, '#')) list($path, $hash) = explode('#', $path);
        $page = $this->yellow->content->find($path);
        if (empty($title) && $hash) {
            $title = $hash;
        } elseif (empty($title)) {
            $title = $page->getHtml("title");
        }
        if($hash) $hash = "#".$hash;
        $url = $page->getUrl() . $hash;
        $output = "<a href=\"{$url}\" title=\"{$title}\">{$title}</a>";
        return $output;
    }

    //[pages path sort dir limit]
    public function getPages($page, $name, $text) {
        $output = null;
        list($path, $sort, $dir, $limit) = $this->yellow->toolbox->getTextArguments($text);
        if (empty($path) || $path == "-") {
            $path = $page->getLocation();
        }
        if (empty($sort) || $sort == "-") {
            $sort = "title";
        }
        if (empty($dir) || $dir == "-" || $dir == "1" || $dir == "asc") {
            $dir = true;
        }elseif($dir == "0" || $dir == "desc"){
            $dir = false;
        }
        if (empty($limit) || $limit == "-") {
            $limit = 0;
        }
        $pages = $this->yellow->content->find($path)->getChildren()->sort($sort, $dir);
        if($limit > 0){
            $pages->limit($limit);
        }
        $page->setLastModified($pages->getModified());
        $output .= "<ul>";
        foreach ($pages as $page) {
            $output .= "<li><a href=\"" . $page->getlocation(true) . "\">" . $page->getHtml("title") . "</a></li>";
        }
        $output .= "</ul>";
        return $output;
    }
}