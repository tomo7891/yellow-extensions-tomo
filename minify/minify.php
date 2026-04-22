<?php
// Minify HTML extension

class YellowMinify {
    const VERSION = "0.8.22";
    public $yellow;

    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("minifyHtml", "1");
    }

    public function onParsePageOutput($page, $output) {
        $isMinify = $this->yellow->system->get("minifyHtml") == "1";
        $isDebug = $this->yellow->system->get("coreDebugMode") >= 1;

        if ($isMinify && !$isDebug && !is_null($output)) {
            // 1. 各行の行頭・行末の空白を削除 [cite: 607, 633]
            $output = preg_replace('/^\s+|\s+$/m', '', $output);
            
            // 2. タグ間の空白を削除 (インライン要素の隙間が詰まるので注意) 
            $output = preg_replace('/>\s+</s', '><', $output);
            
            // 3. 連続する改行を1つの改行に集約 (完全に消さないことでJSの1行コメントを保護)
            $output = preg_replace('/\n+/s', "\n", $output);
        }
        return $output;
    }
}
