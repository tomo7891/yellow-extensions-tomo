<?php
// BB Code extension

class YellowBbcode
{
  const VERSION = "0.8.20";
  public $yellow;         // access to API

  // Handle initialization
  public function onLoad($yellow)
  {
    $this->yellow = $yellow;
  }

  public function onParseContentHtml($page, $text)
  {
    $output = null;
    $output = $this->showBBcodes($text);
    return $output;
  }

  public
  /** 
   * A simple PHP BBCode Parser function
   *
   * @author Afsal Rahim
   * @link http://digitcodes.com/create-simple-php-bbcode-parser-function/
   **/

  //BBCode Parser function

  function showBBcodes($text)
  {

    // NOTE : I had to update this sample code with below line to prevent obvious attacks as pointed out by many users.
    // Always ensure that user inputs are scanned and filtered properly.
    //$text  = htmlspecialchars($text, ENT_QUOTES, 'utf-8');

    // BBcode array
    $find = array(
      '~\[u\](.*?)\[/u\]~s',
      '~\[size=(.*?)\](.*?)\[/size\]~s',
      '~\[color=(.*?)\](.*?)\[/color\]~s'
    );

    // HTML tags to replace BBcode
    $replace = array(
      '<u>$1</u>',
      '<span style="font-size:$1px;">$2</span>',
      '<span style="color:$1;">$2</span>'
    );

    // Replacing the BBcodes with corresponding HTML tags
    return preg_replace($find, $replace, $text);
  }
}
