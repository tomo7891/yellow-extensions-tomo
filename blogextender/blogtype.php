<?php
// Blog Type extension

class YellowBlogtype
{
    const VERSION = "0.8.20";
    public $yellow;         // access to API

    // Handle initialization
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("noImage", "noimage.png");
        $this->yellow->system->setDefault("documentDirectory", "document/");
        $this->yellow->system->setDefault("photoDirectory", "photo/");
        $this->yellow->system->setDefault("videoDirectory", "video/");
        $this->yellow->system->setDefault("linkDirectory", "link/");
        $this->yellow->system->setDefault("literatureDirectory", "literature/");
        $this->yellow->system->setDefault("publicationDirectory", "publication/");
        $this->yellow->system->setDefault("publicationNoImage", "publication/noimage.jpg");
    }

    public function onParseContentHtml($page, $text)
    {
        if ($page->get("layout") == "blog") {
            $text = null;
            $type = $page->get("type");
            if ($type) {
                $text .= $this->$type($page);
            } else {
                $text .= $this->blog($page);
            }
            return $text;
        }
    }

    //Document
    public function document($page)
    {
        $output = null;
        if (strpos($this->yellow->page->fileName, 'page.md') !== false) {
            $pages = $this->yellow->page->getChildren();
            $pages->prepend($page);
        } else {
            $pages = $this->yellow->page->getSiblings();
            $pages->prepend($page->getParent());
        }
        $this->yellow->page->setLastModified($pages->getModified());
        $pager = null;
        if ($pages) {
            $pager .= '<ul class="uk-pagination uk-flex-center">';
            foreach ($pages as $key => $val) {
                $key++;
                if ($val->getLocation() == $page->getLocation()) {
                    $pager .= '<li class="uk-active"><span>' . $key . '</span></li>';
                } else {
                    $pager .= '<li><a href="' . $val->getLocation() . '">' . $key . '</a></li>';
                }
            }
            $pager .= '</ul>';
        }
        if ($page->getChildren() && strpos($this->yellow->page->fileName, 'page.md') !== false) {
            $i = 1;
            $output .= $page->getContent();
            if (!is_array_empty($page->getChildren())) {
                $output .= '<ul class="toc-link">';
                foreach ($pages as $child) {
                    $output .= '<li><a href="' . $child->getLocation(true) . '">' .$i. '. '. $child->get("title") . '</a></li>';
                    $i++;
                }
                $output .= "</ul>";
                $output .= $pager;
            }
        } else {
            $output .= $page->getContent();
            $output .= $pager;
        }
        return $output;
    }

    //Photo
    public function photo($page)
    {
        $output = null;
        $output .= $page->getContent();
        $output .= $this->getPhoto($page);
        return $output;
    }

    public function getPhoto($page)
    {
        $name = "gallery";
        $text = $this->yellow->system->get("photoDirectory") . $page->get("gallery") . " name 50%";
        $type = "block";
        $uikit = $this->yellow->extension->get("uikit");
        $output = null;
        $output = $uikit->onParseContentShortcut($page, $name, $text, $type);
        return $output;
    }

    //Video
    public function video($page)
    {
        $output = null;
        $output .= $page->getContent();
        $output .= $this->getVideo($page);
        return $output;
    }

    public function getVideo($page)
    {
        $name = "youtube";
        $text = $page->get("video") . ' "uk-margin-auto uk-text-center" 560';
        $type = "block";
        $uikit = $this->yellow->extension->get("uikit");
        $output = null;
        $output = $uikit->onParseContentShortcut($page, $name, $text, $type);
        return $output;
    }

    //Audio
    public function audio($page)
    {
        $output = null;
        $output .= $this->yellow->page->getContent();
        $output .= $this->getAudio($page);
        return $output;
    }

    public function getAudio($page)
    {
        $name = "youtube";
        $text = $page->get("audio") . ' "uk-margin-auto uk-text-center" 560';
        $type = "block";
        $uikit = $this->yellow->extension->get("uikit");
        $output = null;
        $output = $uikit->onParseContentShortcut($page, $name, $text, $type);
        return $output;
    }

    //Quote
    public function quote($page)
    {
        $output = null;
        $quote =  $page;
        $output .= $this->getQuote($quote);
        return $output;
    }

    public function getQuote($page)
    {
        $output = null;
        $publisher = null;
        $pp = null;
        if ($page->get("publisher")) {
            $publisher = ", " . $page->get("publisher");
        }
        if ($page->get("pages")) {
            if (strpos($page->get("pages"), "-")) {
                $pp = ", pp. " . $page->get("pages") . ".";
            } else {
                $pp = ", p. " . $page->get("pages") . ".";
            }
        }
        $output .= "<blockquote>";
        $output .= $page->getContent();
        $output .= '<footer>' . $page->getHtml("by") . ' in <cite>' . $page->getHtml("from") . $publisher . $pp . '</cite></footer>';
        $output .= "</blockquote>";
        return $output;
    }

    //Link
    public function link($page)
    {
        $output = null;
        $output .= $this->yellow->page->getContent();
        $output .= $this->getLink($page);
        return $output;
    }

    public function getLink($page)
    {
        $dir = $this->yellow->system->get("CoreDownloadLocation");
        $dir = $dir . $this->yellow->system->get("linkDirectory");
        $csv = "." . $dir . $page->getHtml("link") . '.csv';
        $fileName = $this->yellow->toolbox->normalisePath($csv);
        $fileData = $this->yellow->toolbox->readFile($fileName);
        if (!is_string_empty($fileData)) {
            $output = csv2list($fileData); //functions.php
        } else {
            $this->yellow->page->error(500, "CSV '$fileName' does not exist!");
        }
        return $output;
    }

    //Map
    public function map($page)
    {
        $output = null;
        $output .= $this->yellow->page->getContent();
        $output .= $this->getMap($page);
        return $output;
    }

    //Open Street Map
    public function getMap($page)
    {
        $name = "map";
        $zoom = "13";
        if ($page->get("zoom")) $zoom = $page->get("zoom");
        $text = $page->get("map") . " " . $zoom . ' "uk-margin-auto uk-text-center" 560 315';
        $type = "block";
        $uikit = $this->yellow->extension->get("uikit");
        $output = null;
        $output = $uikit->onParseContentShortcut($page, $name, $text, $type);
        return $output;
    }

    //Literature
    public function literature($page)
    {
        $output = null;
        $output .= $this->yellow->page->getContent();
        $output .= '<div class="uk-overflow-auto">' . $this->getLiterature($page) . '</div>';
        return $output;
    }

    public function getLiterature($page)
    {
        $output = null;
        $activeHeader = '1';
        $csv = $page->getHtml("literature");
        $dir = $this->yellow->system->get("CoreDownloadLocation");
        $dir = '.' . $dir . $this->yellow->system->get("literatureDirectory");
        $fileName = $this->yellow->toolbox->normalisePath($dir . $csv . '.csv');
        $fileData = $this->yellow->toolbox->readFile($fileName);
        if (!is_string_empty($fileData)) {
            $output .= csv2table($fileData, $activeHeader);
        } else {
            $this->yellow->page->error(500, "CSV '$fileName' does not exist!");
        }
        return $output;
    }

    //Collection
    public function collection($page)
    {
        $output = null;
        $output .= '<div class="uk-overflow-auto">';
        $output .= '<table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-text-nowrap uk-text-small">';
        $output .= '<tbody>';
        $output .= '<tr>';
        $output .= '<th>タイトル<br></th>';
        $output .= '<td colspan="3">' . $this->yellow->page->getHtml("title") . '</td>';
        $output .= '</tr><tr>';
        $output .= '<th>サブタイトル<br></th>';
        $output .= '<td colspan="3">' . $this->yellow->page->getHtml("subtitle") . '</td>';
        $output .= '</tr><tr>';
        $output .= '<th>コントリビューター<br></th>';
        $output .= '<td colspan="3">' . $this->yellow->page->getHtml("contributor") . '</td>';
        $output .= '</tr><tr>';
        $output .= '<th>収録物<br></th>';
        $output .= '<td colspan="3">' . $this->yellow->page->getHtml("publication") . '</td>';
        $output .= '</tr><tr>';
        $output .= '<th>巻<br></th>';
        $output .= '<td>' . $this->yellow->page->getHtml("volume") . '</td>';
        $output .= '<th>号<br></th>';
        $output .= '<td>' . $this->yellow->page->getHtml("issue") . '</td>';
        $output .= '</tr><tr>';
        $output .= '<th>公表日<br></th>';
        $output .= '<td>' . $this->yellow->page->getHtml("publishedDate") . '</td>';
        $output .= '<th>分類<br></th>';
        $output .= '<td>' . $this->yellow->page->getHtml("category") . '</td>';
        $output .= '</tr><tr>';
        $output .= '<th>版<br></th>';
        $output .= '<td>' . $this->yellow->page->getHtml("edition") . '</td>';
        $output .= '<th>出版社<br></th>';
        $output .= '<td>' . $this->yellow->page->getHtml("publisher") . '</td>';
        $output .= '</tr><tr>';
        $output .= '<th>ISBN<br></th>';
        $output .= '<td>' . $this->yellow->page->getHtml("isbn") . '</td>';
        $output .= '<th>参照ID<br></th>';
        $output .= '<td>' . $this->yellow->page->getHtml("refId") . '</td>';
        $output .= '</tr><tr>';
        $output .= '<th>前書き<br></th>';
        $output .= '<td>' . $this->yellow->page->getHtml("forewords") . '</td>';
        $output .= '<th>前所有者<br></th>';
        $output .= '<td>' . $this->yellow->page->getHtml("previousOwner") . '</td>';
        $output .= '</tr><tr>';
        $output .= '<th>コメント<br></th>';
        $output .= '<td colspan="3">' . $this->yellow->page->getContent(true) . '</td>';
        $output .= '</tr>';
        if ($this->yellow->page->getHtml("image")) {
            $image = '.' . $this->yellow->system->get("CoreImageLocation") . $this->yellow->page->getHtml("image");
            list($src, $width, $height) = $this->yellow->extension->get("image")->getImageInformation($image, "60%", "60%");
            $output .= '<tr>';
            $output .= '<td colspan="4">';
            $output .= '<img src="' . $src . '" width="' . $width . '" height="' . $height . '" class="lazy" alt="' . $this->yellow->page->getHtml("title") . '">';
            $output .= '</td>';
            $output .= '</tr>';
        }
        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</div>';
        return $output;
    }

    //Publication
    public function publication($page)
    {
        $output = null;
        $output .= $this->yellow->page->getContent();
        $output .= '<div class="uk-overflow-auto">';
        $output .= '<table>';
        $output .= '<tbody>';
        $output .= '<tr>';
        $output .= '<th>発行日</th>';
        $output .= '<td>';
        if ($this->yellow->page->get("publishedDate")) {
            $this->yellow->page->getDateHtml("publishedDate", "CoreDateFormatMedium");
        } else {
            $output .= '未定';
        }
        $output .= '</td>';
        $output .= '</tr>';
        if ($this->yellow->page->getHtml("edition")) {
            $output .= '<tr>';
            $output .= '<th>版</th>';
            $output .= '<td>';
            $output .= $this->yellow->page->getHtml("edition");
            $output .= '</td>';
            $output .= '</tr>';
        }
        if ($this->yellow->page->getHtml("sample")) {
            $pattern = $page->get("sample");
            $output .= '<tr>';
            $output .= '<th>サンプル</th>';
            $output .= '<td class="uk-child-width-1-2 uk-child-width-1-4@m uk-padding-small" uk-grid>';
            $output .= $this->getSample($pattern);
            $output .= '</td>';
            $output .= '</tr>';
        }
        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</div>';
        $output .= $this->getErrata($page);
        return $output;
    }

    public function getSample($pattern)
    {
        $output = null;
        if (is_string_empty($pattern)) {
            $pattern = "unknown";
            $files = $this->yellow->media->clean();
        } else {
            $images =  $this->yellow->system->get("CoreImageLocation");
            $images = $images . $this->yellow->system->get("publicationDirectory");
            $files = $this->yellow->media->index(true, true)->match("#$images$pattern#");
        }
        foreach ($files as $file) {
            $src = $file->fileName;
            $caption = $this->yellow->language->isText($file->fileName) ? $this->yellow->language->getText($file->fileName) : "";
            $alt = is_string_empty($caption) ? basename($file->getLocation(true)) : $caption;
            $imageWH = resizeImage($src, "s");
            list($imageS, $ImageW, $ImageH) = $this->yellow->extension->get("image")->getImageInformation($src, $imageWH[0], $imageWH[1]);
            $output .= '<div><img src="' . $imageS . '" width="' . $ImageW . '" height="' . $ImageH . '" alt="' . $alt . '" loading=\"lazy\"></div>';
        }
        return $output;
    }

    public function getErrata($page)
    {
        $output = null;
        if ($page->isExisting("errata")) {
            $output .= "<h2>正誤表</h2>";
            $output .= '<div class="uk-overflow-auto">';
            $activeHeader = '1';
            $csv = $page->getHtml("errata");
            $dir = $this->yellow->system->get("CoreDownloadLocation");
            $dir = '.' . $dir . $this->yellow->system->get("publicationDirectory");
            $fileName = $this->yellow->toolbox->normalisePath($dir . $csv . '.csv');
            $fileData = $this->yellow->toolbox->readFile($fileName);
            if (!is_string_empty($fileData)) {
                $output .= csv2table($fileData, $activeHeader);
            } else {
                $this->yellow->page->error(500, "CSV '$fileName' does not exist!");
            }
            $output .= "</div>";
        }
        return $output;
    }

    //Blog
    public function blog($page)
    {
        $imageEx = $this->yellow->extension->get('image');
        $images = '.' . $this->yellow->system->get('CoreImageLocation');
        list($s, $w, $h) = $imageEx->getImageInformation($images . $this->yellow->page->get('image'), '300', '150');
        $output = null;
        $output .= '<img src="' . $s . '" class="uk-align-center uk-object-contain uk-border-rounded lazy" width="300" height="150" loading="lazy" style="aspect-ratio: 1 / 0.5;">';
        $output .= $page->getContent();
        $output .= '<div class="ref"><h2 class="uk-h4">レッスン情報</h2>';
        $output .= '<ul>';
        $output .= '<li><a href="/school/">教室について</a></li>';
        $output .= '<li><a href="/school/lesson">レッスンについて</a></li>';
        $output .= '<li><a href="/school/event">イベント情報</a></li>';
        $output .= '</ul>';
        $output .= '<h2 class="uk-h4">関連記事</h2>';
        $output .= $this->yellow->extension->get("blog")->getShorcutBlogrelated($this->yellow->page, "blogRelated", "/blog/ 5");
        $output .= '</div>';
        return $output;
    }

    //Information
    public function information($page)
    {
        $output = null;
        $output .= $page->getContent();
        return $output;
    }
}
