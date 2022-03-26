<?php
// Download extension

class YellowDownload
{
    const VERSION = "0.8.19";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
    }

    public function download($file_path, $pMimeType = null)
    {
        if (!is_readable($file_path)) {
            die($file_path);
        }
        $mimeType = (isset($pMimeType)) ? $pMimeType
            : (new finfo(FILEINFO_MIME_TYPE))->file($file_path);
        if (!preg_match('/\A\S+?\/\S+/', $mimeType)) {
            $mimeType = 'application/octet-stream';
        }
        header('Content-Type: ' . $mimeType);
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: ' . filesize($file_path));
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Connection: close');
        while (ob_get_level()) {
            ob_end_clean();
        }
        readfile($file_path);
        exit;
    }
}
