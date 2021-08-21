<?php
class YellowCrop
{
    const VERSION = "0.8.10";
    public $yellow;         // access to API


    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
    }

    //リサイズ計算
    public function resize($src, $size)
    {
        if (!empty($src)) {
            if (empty($size)) {
                $size = 'original';
            }
            switch ($size) {
          case 'xl':
          case 'll':
          case '2l':
          $length = '1280';
          break;
          case 'lg':
          case 'l':
          $length = '1024';
          break;
          case 'md':
          case 'm':
          $length = '640';
          break;
          case 'sm':
          case 's':
          $length = '400';
          break;
          case 'xs':
          case 'ss':
          $length = '150';
          break;
          case 'thumb':
          case 'th':
          $length = '150';
          break;
        }
            list($w, $h) = $this->yellow->toolbox->detectImageInformation($src);
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
                    $output[]= round($w * $length / $h);
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

    //画像生成
      public function thumb($file, $size='')
      {
          if (empty($file)) {
              return;
          }
          $src= $this->yellow->system->get("coreImageDirectory").$file;
          if (strpos($size, ',')) {
              list($w, $h) = explode(',', $size);
          } else {
              list($w, $h) = $this->resize($src, $size);
          }
          if ($this->yellow->extension->isExisting("image")) {
            list($src, $width, $height) = $this->yellow->extension->get("image")->getImageInformation($src, $w, $h);
          }
          return $src;
      }


}
