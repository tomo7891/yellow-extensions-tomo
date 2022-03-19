<?php
// BB Code extension

class YellowBbcode
{
  const VERSION = "0.8.19";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow)
  {
    $this->yellow = $yellow;
  }

  public function onParsePageOutput($page, $text)
  {
    $otuput = null;
    $output = $this->showBBcodes($text);
    return $output;
  }

  /** 
   * A simple PHP BBCode Parser function
   *
   * @author Afsal Rahim
   * @link http://digitcodes.com/create-simple-php-bbcode-parser-function/
   **/

  //BBCode Parser function

  public function showBBcodes($text)
  {

    // NOTE : I had to update this sample code with below line to prevent obvious attacks as pointed out by many users.
    // Always ensure that user inputs are scanned and filtered properly.
    $text  = htmlspecialchars($text, ENT_QUOTES, 'utf-8');

    // BBcode array
    $find = array(
      '~\[b\](.*?)\[/b\]~s',
      '~\[i\](.*?)\[/i\]~s',
      '~\[u\](.*?)\[/u\]~s',
      '~\[quote\](.*?)\[/quote\]~s',
      '~\[size=(.*?)\](.*?)\[/size\]~s',
      '~\[color=(.*?)\](.*?)\[/color\]~s',
      '~\[url\]((?:ftp|https?)://.*?)\[/url\]~s',
      '~\[img\](https?://.*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]~s'
    );

    // HTML tags to replace BBcode
    $replace = array(
      '<b>$1</b>',
      '<i>$1</i>',
      '<span style="text-decoration:underline;">$1</span>',
      '<pre>$1</' . 'pre>',
      '<span style="font-size:$1px;">$2</span>',
      '<span style="color:$1;">$2</span>',
      '<a href="$1">$1</a>',
      '<img src="$1" alt="" />'
    );

    // Replacing the BBcodes with corresponding HTML tags
    return preg_replace($find, $replace, $text);
  }
}
