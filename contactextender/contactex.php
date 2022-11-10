<?php
// Contact extender extension, https://github.com/datenstrom/yellow-extensions/tree/master/source/contact

include_once("contact.php");
class YellowContactex extends YellowContact
{
    const VERSION = "0.8.21";
    public $yellow;         // access to API


    // Handle page layout
    public function onParsePageLayout($page, $name)
    {
        if ($name == "contactex") {
            if ($this->yellow->isCommandLine()) $page->error(500, "Static website not supported!");
            if (!$page->isRequest("referer")) {
                $page->setRequest("referer", $this->yellow->toolbox->getServer("HTTP_REFERER"));
                $page->setHeader("Last-Modified", $this->yellow->toolbox->getHttpDateFormatted(time()));
                $page->setHeader("Cache-Control", "no-cache, no-store");
            }
            if ($page->getRequest("status") == "send") {
                $status = $this->sendMailex();
                if ($status == "settings") $page->error(500, "Contact page settings not valid!");
                if ($status == "error") $page->error(500, $this->yellow->language->getText("contactStatusError"));
                $page->setHeader("Last-Modified", $this->yellow->toolbox->getHttpDateFormatted(time()));
                $page->setHeader("Cache-Control", "no-cache, no-store");
                $page->set("status", $status);
            } else {
                $page->set("status", "none");
            }
        }
    }

    // Send contact email
    public function sendMailex()
    {
        $status = "send";
        $senderName = trim(preg_replace("/[^\pL\d\-\. ]/u", "-", $this->yellow->page->getRequest("name")));
        $senderEmail = trim($this->yellow->page->getRequest("email"));
        $message = trim($this->yellow->page->getRequest("message"));
        $consent = trim($this->yellow->page->getRequest("consent"));
        $referer = trim($this->yellow->page->getRequest("referer"));
        $sitename = $this->yellow->system->get("logo");
        $siteEmail = $this->yellow->system->get("contactSiteEmail");        
        /* custom */
        $phone = trim($this->yellow->page->getRequest("phone"));
        $post = trim($this->yellow->page->getRequest("post"));
        $address = trim(preg_replace("/[^\pL\d\-\. ]/u", "-", $this->yellow->page->getRequest("address")));
        $subject = trim(preg_replace("/[^\pL\d\-\. ]/u", "-", $this->yellow->page->getRequest("subject")));

        $header = $this->getMailHeader($senderName, $senderEmail);
        $footer = $this->getMailFooter($referer);
        $spamFilter = $this->yellow->system->get("contactSpamFilter");
        $userName = $this->yellow->system->get("author");
        $userEmail = $this->yellow->system->get("email");
        if ($this->yellow->page->isExisting("author") && !$this->yellow->system->get("contactEmailRestriction")) {
            $userName = $this->yellow->page->get("author");
        }
        if ($this->yellow->page->isExisting("email") && !$this->yellow->system->get("contactEmailRestriction")) {
            $userEmail = $this->yellow->page->get("email");
        }
        if ($this->yellow->system->get("contactLinkRestriction") && ($this->checkClickable($address) || $this->checkClickable($subject) || $this->checkClickable($message))) $status = "review";
        if (is_string_empty($senderName) || is_string_empty($senderEmail) || is_string_empty($phone) || is_string_empty($subject) || is_string_empty($message) || is_string_empty($consent)) $status = "incomplete";
        if (!is_string_empty($senderEmail) && !filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) $status = "invalid";
        if (is_string_empty($userEmail) || !filter_var($userEmail, FILTER_VALIDATE_EMAIL)) $status = "settings";
        if ($status == "send") {
            $mailTo = mb_encode_mimeheader("$userName") . " <$userEmail>";
            $mailSubject = mb_encode_mimeheader($this->yellow->page->get("title"));
            $mailHeaders = mb_encode_mimeheader("From: $sitename") . " <$siteEmail>\r\n";
            $mailHeaders .= mb_encode_mimeheader("Reply-To: $senderName") . " <$senderEmail>\r\n";
            $mailHeaders .= mb_encode_mimeheader("X-Referer-Url: " . $referer) . "\r\n";
            $mailHeaders .= mb_encode_mimeheader("X-Request-Url: " . $this->yellow->page->getUrl()) . "\r\n";
            if ($spamFilter != "none" && preg_match("/$spamFilter/i", $message)) {
                $mailSubject = mb_encode_mimeheader($this->yellow->language->getText("contactMailSpam") . " " . $this->yellow->page->get("title"));
                $mailHeaders .= "X-Spam-Flag: YES\r\n";
                $mailHeaders .= "X-Spam-Status: Yes, score=1\r\n";
            }
            $mailHeaders .= "Mime-Version: 1.0\r\n";
            $mailHeaders .= "Content-Type: text/plain; charset=utf-8\r\n";
            $mailMessage = "";
            $message = "件名: {$subject}\r\n\r\n" . $message ."\r\n\r\n--\r\n連絡先情報\r\n\r\n";
            if (!is_string_empty($phone)) $message .= "電話番号: {$phone}\r\n\r\n";
            if (!is_string_empty($post)) $message .= "郵便番号: {$post}\r\n\r\n";
            if (!is_string_empty($address)) $message .= "住所: {$address}";
            $mailMessage = "$header\r\n\r\n$message\r\n-- \r\n$footer";
            $status = mail($mailTo, $mailSubject, $mailMessage, $mailHeaders) ? "done" : "error";
        }
        return $status;
    }

    // Return email footer
    public function getMailFooter($url)
    {
        $footer = $this->yellow->language->getText("contactMailFooter");
        $footer = str_replace("\\n", "\r\n", $footer);
        $footer = preg_replace("/@sitename/i", $this->yellow->system->get("sitename"), $footer);
        $footer = preg_replace("/@title/i", $this->findTitle($url, $this->yellow->page->get("titleContent")) . "[" . $url . "]", $footer);
        return $footer;
    }
}
