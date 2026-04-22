<?php
// Lazyload extension

class YellowLazyload {
    const VERSION = "0.8.24";
    public $yellow;

    // 初期化：遅延読み込みの対象とするCSSクラスのデフォルト値を設定します
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("lazyLoadClass", "lazy");
    }

    // ページ出力の最終段階でHTMLタグをスキャンして属性を付加します
    public function onParsePageOutput($page, $output) {
        if (!is_null($output)) {
            $lazyClass = $this->yellow->system->get("lazyLoadClass");
            
            // 正規表現でimgとiframeを抽出し、クラス名を確認して属性を付加します
            $output = preg_replace_callback('/<(iframe|img)([^>]*)>/u', function ($matches) use ($lazyClass) {
                $tag = $matches[1];
                $attributes = $matches[2];

                // 指定されたクラスが含まれているか確認（正規化されたクラス名で比較）
                if (strpos($attributes, 'class=') !== false && strpos($attributes, $lazyClass) !== false) {
                    // 二重付与を防ぐため、既存のloading属性がない場合のみ追加します
                    if (strpos($attributes, 'loading=') === false) {
                        return "<$tag$attributes loading=\"lazy\">";
                    }
                }
                return "<$tag$attributes>";
            }, $output);
        }
        return $output;
    }
}
