<?php
// Canonical extension

class YellowCanonical {
    const VERSION = "0.8.22";
    public $yellow;

    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }

    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name == "header") {
            // デフォルトではシステムの正規URL（coreServerScheme等に基づく）を取得します
            $canonical = $page->getUrl(true);

            // ページメタデータに "Canonical: /path/to/page" 等の指定がある場合
            if ($page->isExisting("canonical") && !is_string_empty($page->get("canonical"))) {
                $custom = $page->get("canonical");

                // すでに http から始まるフルURLでない場合は、システム設定を使って正規化します
                if (!preg_match("/^\w+:/", $custom)) {
                    $canonical = $this->yellow->lookup->normaliseUrl(
                        $this->yellow->system->get("coreServerScheme"),
                        $this->yellow->system->get("coreServerAddress"),
                        $this->yellow->system->get("coreServerBase"),
                        $custom
                    );
                } else {
                    $canonical = $custom;
                }
            }
            $output = "<link rel=\"canonical\" href=\"" . htmlspecialchars($canonical) . "\" />\n";
        }
        return $output;
    }
}
