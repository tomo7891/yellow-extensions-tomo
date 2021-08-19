<?php
class YellowShortcuts
{
    const VERSION = "0.8.10";
    public $yellow;         // access to API

    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
    }

    public function onParseContentShortcut($page, $name, $text, $type)
    {
        $output = null;
        switch ($name) {
                case "page": $output = $this->scPage($page, $name, $text); break;
                case "pages": case "list":   $output = $this->scPages($page, $name, $text); break;
                case "system": case "setting": $output = $this->scSystem($page, $name, $text); break;
                case "user": case "account": $output = $this->scUser($page, $name, $text); break;
                case "language": case "site":  $output = $this->scLanguage($page, $name, $text); break;
                case "date": $output = $this->scDate($page, $name, $text); break;
                case "url": $output = $this->scUrl($page, $name, $text); break;
            }
        return $output;
    }

    public function onParsePageOutput($page, $text)
    {
        $output = null;
        $text = $this->showBBcodes($text);
        return $text;
      }

    //ショートコード
    //[page key encode]
    public function scPage($page, $name, $text)
    {
        $output = null;
        if (empty($text)) {
            return $output;
        }
        list($key, $encode) = $this->yellow->toolbox->getTextArguments($text);
        if (empty($encode)) {
            $encode='false';
        }
        if ($encode != 'true') {
            $output = $this->yellow->page->get($key);
        } else {
            $output = $this->yellow->page->getHTML($key);
        }
        return $output;
    }

    //[pages path sort dir invisible]
    public function scPages($page, $name, $text)
    {
        $output = null;
        list($path, $sort, $dir, $invisible) = $this->yellow->toolbox->getTextArguments($text);
        if (empty($path) || $path == "-") {
            $path = $this->yellow->page->getLocation();
        }
        if (empty($sort) || $sort == "-") {
            $sort = "title";
        }
        if (empty($dir) || $dir == "-") {
            $sort = false;
        }
        $pages = $this->yellow->content->find($path)->getChildren($invisible)->sort($sort, $dir);
        $this->yellow->page->setLastModified($pages->getModified());
        $output .= "<ul>";
        foreach ($pages as $page) {
            if ($page->getHtml("status") != 'draft') {
                $output .= "<li><a href=\"".$page->getlocation(true)."\">".$page->getHtml("title")."</a></li>";
            }
        }
        $output .= "</ul>";
        return $output;
    }

    //[system key encode]
    public function scSystem($page, $name, $text)
    {
        $output = null;
        list($key, $encode) = $this->yellow->toolbox->getTextArguments($text);
        if (empty($encode)) {
            $encode='false';
        }
        if ($encode != 'true') {
            $output = $this->yellow->system->get($key);
        } else {
            $output = $this->yellow->system->getHTML($key);
        }
        return $output;
    }

    //[user key encode]
    public function scUser($page, $name, $text)
    {
        $output = null;
        list($key, $encode) = $this->yellow->toolbox->getTextArguments($text);
        if (empty($encode)) {
            $encode='false';
        }
        if ($encode != 'true') {
            $output = $this->yellow->user->getUser($key);
        } else {
            $output = $this->yellow->user->getUserHtml($key);
        }
        return $output;
    }

    //[langage key encode]
    public function scLanguage($page, $name, $text)
    {
        $output = null;
        list($key, $encode) = $this->yellow->toolbox->getTextArguments($text);
        if (empty($encode)) {
            $encode='false';
        }
        if ($encode != 'true') {
            $output = $this->yellow->language->getText($key);
        } else {
            $output = $this->yellow->language->getTextHtml($key);
        }
        return $output;
    }

    //[date key format encode]
    public function scDate($page, $name, $text)
    {
        $output = null;
        list($key, $format, $encode) = $this->yellow->toolbox->getTextArguments($text);
        if (empty($encode)) {
            $encode='false';
        }
        if (empty($format)) {
            $format = "CoreDateFormatMedium";
        }
        if ($encode != 'true') {
            $output = $this->yellow->page->getDate($key, $format);
        } else {
            $output = $this->yellow->page->getDateHtml($key, $format);
        }
        return $output;
    }

    //[url path title]
    public function scUrl($page, $name, $text)
    {
      $output = null;
      $hash = null;
      list($path,$title) = $this->yellow->toolbox->getTextArguments($text);
      if(strpos($path,'#'))list($path,$hash) = explode('#',$path);
      $page = $this->yellow->content->find($path);
      if(empty($title) && $hash){
        $title = $hash;
      }elseif(empty($title)){
        $title = $page->getHtml("title");
      }
      $url = $page->getUrl()."#{$hash}";
      $output = "<a href=\"{$url}\" title=\"{$title}\">{$title}</a>";
      return $output;
    }

    //BBcode
    //[bb][/bb]
    public function showBBcodes($text)
    {
    //$text  = htmlspecialchars($text, ENT_QUOTES, 'utf-8');
        // BBcode array
        $find = array(
        '#<p>\[cite\](.*?)\[/cite\]<\/p>#' //[cite]*[/cite]
      );
        // HTML tags to replace BBcode
        $replace = array(
        '<cite">$1</cite>'
      );
        // Replacing the BBcodes with corresponding HTML tags
        return preg_replace($find, $replace, $text);
    }
}
