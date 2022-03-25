<?php
// Backup extension
// TODO: default settings
// TODO: zip archive
// TODO: exclude files
// TODO: backup List
// TODO: backup Limit
// TODO: select Directory

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
            $output .= "<style>#backupform label {cursor:pointer;}.directory {margin-top:1rem;font-weight:bold;}.subdirectory{display:inline-block;padding-right:1rem;}.submit{margin-top:1rem;}</style>";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$extensionLocation}backup.js\"></script>\n";
        }
        return $output;
    }

    public function onParsePageLayout($page, $name)
    {
        if ($name == "backup") {
            if ($this->yellow->user->isExisting($this->yellow->user->getUserHtml("email"))) {
                $request = $this->yellow->toolbox->getServer("REQUEST_URI");
                $request = str_replace("/manager/backup/", "", $request);
                if ($request) {
                    $request = explode("/", trim($request, "/"));
                    $content = $media = $system = array();
                    $backupDirectory = "./system/backup/" . date("Y-m-d-h-i-s") . "/";
                    $k = $v = null;
                    foreach ($request as $key => $value) {
                        list($k, $v) = explode("=", $value);
                        $k = str_replace("_", "/", $k);
                        if (substru($k, 0, 7) == "content") {
                            $content[] = "./" . $k;
                        }
                        if (substru($k, 0, 5) == "media") {
                            $media[] = "./" . $k;
                        }
                        if (substru($k, 0, 6) == "system") {
                            $system[] = "./" . $k;
                        }
                    }
                    if (count($content) > 0) {
                        foreach ($content as $c) {
                            if ($c == "./content") {
                                $this->copyProcessor($page, $c, $backupDirectory);
                                break;
                            } else {
                                $this->copyProcessor($page, $c, $backupDirectory);
                                continue;
                            }
                        }
                    }
                    if (count($media) > 0) {
                        foreach ($media as $m) {
                            if ($m == "./media") {
                                $this->copyProcessor($page, $m, $backupDirectory);
                                break;
                            } else {
                                $this->copyProcessor($page, $m, $backupDirectory);
                                continue;
                            }
                        }
                    }
                    if (count($system) > 0) {
                        foreach ($system as $s) {
                            if ($s == "./system") {
                                $this->copyProcessor($page, $s, $backupDirectory);
                                break;
                            } else {
                                $this->copyProcessor($page, $s, $backupDirectory);
                                continue;
                            }
                        }
                    }
                }
            } else {
                $this->yellow->page->error(404);
            }
        }
    }

    public function copyProcessor($page, $path, $backupDirectory)
    {
        $files = $this->yellow->toolbox->getDirectoryEntriesRecursive($path, "/.*/", false, false);
        foreach ($files as $file) {
            if (strpos($file, $backupDirectory) !== true) {
                $this->yellow->toolbox->copyFile($file, $backupDirectory . ltrim($file, './'), true);
            }
        }
    }
}
