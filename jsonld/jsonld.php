<?php
// Jsonld extension

class YellowJsonld {
    const VERSION = "0.8.21";
    public $yellow;

    // 初期化
    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }

    // ヘッダーにJSON-LD形式の構造化データを出力します
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name == 'header') {
            $currentLocation = $page->getLocation(true);
            $pages = $this->yellow->content->path($currentLocation, true);
            
            $items = array();
            $i = 1;
            foreach ($pages as $p) {
                $items[] = array(
                    "@type" => "ListItem",
                    "position" => $i,
                    "name" => $p->getHtml("titleContent"),
                    "item" => $p->getUrl()
                );
                $i++;
            }

            if (!empty($items)) {
                $data = array(
                    "@context" => "https://schema.org/",
                    "@type" => "BreadcrumbList",
                    "itemListElement" => $items
                );
                
                // JSONを整形して出力（Unicode文字はそのまま、スラッシュはエスケープしない）
                $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                $output = "<script type=\"application/ld+json\">\n" . $json . "\n</script>\n";
            }
        }
        return $output;
    }
}
