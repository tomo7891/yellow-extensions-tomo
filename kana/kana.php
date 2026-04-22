<?php
// Convert Kana extension

class YellowKana {
    const VERSION = "0.8.22";
    public $yellow;

    // 初期化：システム設定のデフォルト値を登録します
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("kanaConvert", "1");
        $this->yellow->system->setDefault("kanaOption", "KasV");
    }

    // ページ出力の最終段階で文字変換を実行します
    public function onParsePageOutput($page, $output) {
        $isConvert = $this->yellow->system->get("kanaConvert") == "1";
        $isDebug = $this->yellow->system->get("coreDebugMode") >= 1;

        // 設定が有効かつデバッグモードが無効な場合に変換を実行
        if ($isConvert && !$isDebug && !is_null($output)) {
            // システム標準の mbstring エクステンションを利用します
            $output = mb_convert_kana($output, $this->yellow->system->get("kanaOption"), "UTF-8");
        }
        return $output;
    }
}
