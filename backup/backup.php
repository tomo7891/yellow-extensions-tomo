<?php
// Backup extension
// TODO: default settings
// TODO: zip archive
// TODO: exclude files
// TODO: backup List
// TODO: backup Limit

class YellowBackup
{
    const VERSION = "0.8.19";
    public $yellow;            //access to API

    // Handle initialisation
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
    }

    // Handle page meta data
    public function onParseMeta($page)
    {
        if ($page->get("layout") == "backup") $page->visible = false;
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name)
    {
        $output = null;
        if ($name == "header" && $page->get("layout") == "backup") {
            $extensionLocation = $this->yellow->system->get("coreServerBase") . $this->yellow->system->get("coreExtensionLocation");
            $output .= "<style>#backupform label {cursor:pointer;}</style>";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$extensionLocation}backup.js\"></script>\n";
        }
        return $output;
    }

    public function onParsePageLayout($page, $name)
    {
        if ($name = "backup") {
            if (!$this->yellow->user->isExisting($this->yellow->user->getUserHtml("email"))) {
                $this->yellow->page->error(404);
            }
            if ($page->getRequest("content") || $page->getRequest("media") || $page->getRequest("system")) {
                $backupDirectory = "./system/backup";
                $backupDirectory = $backupDirectory . "/" . date("Y-m-d-h-i-s");
                if ($page->getRequest("content")) {
                    $backupDirectory = $backupDirectory . "_c";
                }
                if ($page->getRequest("media")) {
                    $backupDirectory = $backupDirectory . "_m";
                }
                if ($page->getRequest("system")) {
                    $backupDirectory = $backupDirectory . "_s";
                }

                if ($page->getRequest("content")) {
                    $path = "./content";
                    $contents = $this->yellow->toolbox->getDirectoryEntriesRecursive($path, "/.*/", false, false);
                    foreach ($contents as $content) {
                        $this->yellow->toolbox->copyFile($content, $backupDirectory . "/" . ltrim($content, './'), true);
                    }
                }
                if ($page->getRequest("media")) {
                    $path = "./media";
                    $medias = $this->yellow->toolbox->getDirectoryEntriesRecursive($path, "/.*/", false, false);
                    foreach ($medias as $media) {
                        $this->yellow->toolbox->copyFile($media, $backupDirectory . "/" . ltrim($media, './'), true);
                    }
                }
                if ($page->getRequest("system")) {
                    $path = "./system";
                    $systems = $this->yellow->toolbox->getDirectoryEntriesRecursive($path, "/.*/", false, false);
                    foreach ($systems as $system) {
                        $this->yellow->toolbox->copyFile($system, $backupDirectory . "/" . ltrim($system, './'), true);
                    }
                }
            }
        }
    }
}
