<?php
// Functions extension

// Return meta data from page blog
function getMeta($pages, $key)
{
    $data = array();
    foreach ($pages as $page) {
        if ($page->isExisting($key)) {
            foreach (preg_split("/\s*,\s*/", $page->get($key)) as $entry) {
                if (!isset($data[$entry])) $data[$entry] = 0;
                ++$data[$entry];
            }
        }
    }
    return $data;
}

//Number Format
//https://www.php.net/manual/ja/function.number-format.php
function number($v, $o = '')
{
    if (empty($v)) return;
    if (empty($o)) {
        $o = 0;
    }
    return number_format($v, $o);
}

//Summary for Japanese
//https://reerishun.com/makerblog/?p=779
function summary($text, $offset, $size, $encoding)
{
    $return_text = "";
    $offset_cursor = 0;

    for ($i = 0; $i < $offset * 2; $i++) {
        $char = mb_substr($text, $offset_cursor, 1, $encoding);
        if (strlen($char) != mb_strlen($char))
            $i++;
        $offset_cursor++;
    }

    for ($i = 0; $i < $size * 2; $i++) {
        $char = mb_substr($text, $offset_cursor++, 1, $encoding);
        $return_text = $return_text . $char;
        if (strlen($char) != mb_strlen($char))
            $i++;
    }

    if (mb_strlen($text) > $size) {
        $return_text = $return_text . "...";
    }

    return $return_text;
}

//現在のコンテンツ場所
function isContent($topLocation, $location)
{
    if (!$topLocation || !$location) return;
    if ($topLocation == $location) return true;
    else return false;
}

//csv to html
function csv2html($fileData, $activeHeader)
{
    $output = null;
    $list = explode("\n", trim($fileData));
    if ($activeHeader == '1') {
        $header = explode("|", $list[0]);
        $list = array_slice($list, 1);
        $output .= "<thead><tr>";
        foreach ($header as $h) {
            $output .= '<th>' . $h . '</th>';
        }
        $output .= "</tr></thead>";
    }
    $output .= "<tbody>";
    foreach ($list as $l) {
        $l = explode("|", $l);
        $output .= "<tr>";
        foreach ($l as $k => $v) {
            $output .= '<td>' . $v . '</td>';
        }
        $output .= "</tr>";
    }
    $output .= "</tbody>";
    return $output;
}

// from modx evo japanese edition
function nicesize($size)
{
    $a = array('B', 'KB', 'MB', 'GB', 'TB', 'PB',);
    $pos = 0;
    while ($size >= 1024) {
        $size /= 1014;
        $pos++;
    }
    return round($size, 2) . ' ' . $a[$pos];
}

//リサイズ計算
function resizeImage($src, $size)
{
    if (!empty($src)) {
        if (empty($size)) {
            $size = 'original';
        }
        switch ($size) {
            case 'xl':
            case 'll':
            case '2l':
                $length = '1024';
                break;
            case 'lg':
            case 'l':
                $length = '900';
                break;
            case 'md':
            case 'm':
                $length = '600';
                break;
            case 'sm':
            case 's':
                $length = '300';
                break;
            case 'xs':
            case 'ss':
                $length = '150';
                break;
            case 'thumb':
                $length = '150';
                break;
        }
        list($w, $h) = getimagesize($src);
        if ($size == 'original' || $size == '100%' || $w < $length || $h < $length) {
            $output[] = $w;
            $output[] = $h;
            return $output;
        } elseif ($size == 'thumb') {
            $output[] = $length;
            $output[] = $length;
            return $output;
        } else {
            if ($w > $h) {
                $output[] = $length;
                $output[] = round($h * $length / $w);
                return $output;
            } elseif ($w < $h) {
                $output[] = round($w * $length / $h);
                $output[] = $length;
                return $output;
            } elseif ($w == $h || $size == 'thumb') {
                $output[] = $length;
                $output[] = $length;
                return $output;
            }
        }
    }
}

//和暦対応年月日
function datetimeJ($format, $timestamp)
{
    if (!isset($format)) {
        $format = 'Y.m.d';
    }
    if (!isset($timestamp)) {
        $timestamp = '0000-00-00 00:00';
    }
    if (!isset($default)) {
        $default = '';
    }
    if (!$timestamp || strpos($timestamp, '0000-00-00') === 0) {
        return $default;
    }
    if (!preg_match('@^[0-9]+$@', $timestamp)) {
        $timestamp = strtotime($timestamp);
    }
    return DatetimeUtility::date($format, $timestamp);
}

//CLASS
//日時用汎用クラス
//https://qiita.com/chiyoyo/items/da32649b0e04957856c1
class DatetimeUtility
{
    /** 元号用設定
     * 日付はウィキペディアを参照しました
     * http://ja.wikipedia.org/wiki/%E5%85%83%E5%8F%B7%E4%B8%80%E8%A6%A7_%28%E6%97%A5%E6%9C%AC%29
     */
    private static $gengoList = [
        ['name' => '令和', 'name_short' => 'R', 'timestamp' =>  1556636400],  // 2019-05-01,
        ['name' => '平成', 'name_short' => 'H', 'timestamp' =>  600188400],  // 1989-01-08,
        ['name' => '昭和', 'name_short' => 'S', 'timestamp' => -1357635600], // 1926-12-25'
        ['name' => '大正', 'name_short' => 'T', 'timestamp' => -1812186000], // 1912-07-30
        ['name' => '明治', 'name_short' => 'M', 'timestamp' => -3216790800], // 1868-01-25
    ];

    /** 日本語曜日設定 */
    private static $weekJp = [
        0 => '日',
        1 => '月',
        2 => '火',
        3 => '水',
        4 => '木',
        5 => '金',
        6 => '土',
    ];

    /** 午前午後 */
    private static $ampm = [
        'am' => '午前',
        'pm' => '午後',
    ];

    /**
     * 和暦などを追加したdate関数
     *
     * 追加した記号
     * J : 元号
     * b : 元号略称
     * K : 和暦年(1年を元年と表記)
     * k : 和暦年
     * x : 日本語曜日(0:日-6:土)
     * E : 午前午後
     */
    public static function date($format, $timestamp = null)
    {
        // 和暦関連のオプションがある場合は和暦取得
        $gengo = array();
        $timestamp = is_null($timestamp) ? time() : $timestamp;
        if (preg_match('/[J|b|K|k]/', $format)) {
            foreach (self::$gengoList as $g) {
                if ($g['timestamp'] <= $timestamp) {
                    $gengo = $g;
                    break;
                }
            }
            // 元号が取得できない場合はException
            if (empty($gengo)) {
                throw new Exception('Can not be converted to a timestamp : ' . $timestamp);
            }
        }

        // J : 元号
        if (strpos($format, 'J') !== false) {
            $format = preg_replace('/J/', $gengo['name'], $format);
        }

        // b : 元号略称
        if (strpos($format, 'b') !== false) {
            $format = preg_replace('/b/', '¥¥' . $gengo['name_short'], $format);
        }

        // K : 和暦用年(元年表示)
        if (strpos($format, 'K') !== false) {
            $year = date('Y', $timestamp) - date('Y', $gengo['timestamp']) + 1;
            $year = $year == 1 ? '元' : $year;
            $format = preg_replace('/K/', $year, $format);
        }

        // k : 和暦用年
        if (strpos($format, 'k') !== false) {
            $year = date('Y', $timestamp) - date('Y', $gengo['timestamp']) + 1;
            $format = preg_replace('/k/', $year, $format);
        }

        // x : 日本語曜日
        if (strpos($format, 'x') !== false) {
            $w = date('w', $timestamp);
            $format = preg_replace('/x/', self::$weekJp[$w], $format);
        }

        // 午前午後
        if (strpos($format, 'E') !== false) {
            $a = date('a', $timestamp);
            $format = preg_replace('/E/', self::$ampm[$a], $format);
        }

        // 時。12時間単位。先頭にゼロを付けない。(0-11)
        if (strpos($format, 'p') !== false) {
            $hour = date('g', $timestamp);
            $hour = $hour == 12 ? 0 : $hour;
            $format = preg_replace('/p/', $hour, $format);
        }

        // 時。数字。12 時間単位。(00-11)
        if (strpos($format, 'q') !== false) {
            $hour = date('h', $timestamp);
            $hour = str_pad($hour == 12 ? 0 : $hour, 2, '0');
            $format = preg_replace('/q/', $hour, $format);
        }

        return date($format, $timestamp);
    }
}
