<?php
// Backup extension

class YellowBackup
{
    const VERSION = "0.8.21";
    public $yellow;            //access to API

    // Handle initialisation
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("backupDirectory", "backup/");
        $this->yellow->system->setDefault("backupLimit", "5");
    }

    // Handle page meta data
    public function onParseMetaData($page)
    {
        if ($page->get("layout") == "backup") $page->visible = false;
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name)
    {
        $output = null;
        if ($name == "header" && $page->get("layout") == "backup") {
            $output .= "<style>#backupform label {cursor:pointer;}.directory {margin-top:1rem;font-weight:bold;}.subdirectory{display:inline-block;padding-right:1rem;}.submit{margin-top:1rem;}.submit input{margin-right:1rem;}</style>";
        }
        if ($name == "backuplist") {
            $coreSystemDirectory = $this->yellow->system->get("coreSystemDirectory");
            $backupDirectory = "./" . $coreSystemDirectory . $this->yellow->system->get("backupDirectory");
            $files = $this->yellow->toolbox->getDirectoryEntries($backupDirectory, "/.*.zip/", true, false, false);
            if (!is_array_empty($files)) {
                $output .= "<ul>";
                foreach ($files as $file) {
                    $hash = $this->yellow->extension->get("download")->searchHash($backupDirectory . $file);
                    if ($hash) {
                        $url = $page->getLocation(true) . "download" . $this->yellow->toolbox->getLocationArgumentsSeparator() . $hash . "/";
                        $title = basename($file);
                        $output .= "<li>";
                        $output .= '<a href="' . $url . '">' . $title . ' (' . nicesize(filesize($backupDirectory . $file)) . ')</a>';
                        $output .= "</li>";
                    }
                }
                $output .= "</ul>";
            } else {
                $output .= "<p>No Backup</p>";
            }
        }
        return $output;
    }

    public function onParsePageLayout($page, $name)
    {
        $coreSystemDirectory = $this->yellow->system->get("coreSystemDirectory");
        $backupDirectory = "./" . $coreSystemDirectory . $this->yellow->system->get("backupDirectory");
        if ($name == "backup" && $page->getRequest("download")) {
            if ($this->yellow->user->isExisting($this->yellow->user->getUserHtml("email"))) {
                $file_name = $page->getRequest("download") . '.zip';
                $file_path = $backupDirectory . $file_name;
                $this->yellow->extension->get("download")->download($page, 'application/zip');
            } else {
                $this->yellow->page->error(404);
            }
        } elseif ($name == "backup" && !$page->getRequest("download")) {
            if ($this->yellow->user->isExisting($this->yellow->user->getUserHtml("email"))) {
                $request = $this->yellow->toolbox->getLocationArguments();
                if ($request) {
                    $request = explode("/", trim($request, "/"));
                    $content = $media = $system = array();
                    $backupDirectory = $backupDirectory . date("Y-m-d-h-i-s");
                    $backupFile = $backupDirectory . '.zip';
                    $k = $v = null;
                    foreach ($request as $key => $value) {
                        list($k, $v) = explode($this->yellow->toolbox->getLocationArgumentsSeparator(), $value);
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
                    $backupFolders = ['content' => $content, 'media' => $media, 'system' => $system];
                    foreach ($backupFolders as $folder => $array) {
                        if (!is_array_empty($array)) {
                            foreach ($array as $a) {
                                if ($a == "./" . $folder) {
                                    $this->copyProcessor($page, $a, $backupDirectory);
                                    break;
                                } else {
                                    $this->copyProcessor($page, $a, $backupDirectory);
                                    continue;
                                }
                            }
                        }
                    }
                    $this->zip($backupDirectory, $backupFile);
                    $this->yellow->toolbox->deleteDirectory($backupDirectory);
                    $this->yellow->extension->get("download")->addDownloadList($backupFile);
                    header('Location:' . $this->yellow->page->getLocation(true));
                    exit;
                }
                $backupZipDirectory = "." . $this->yellow->system->get("backupDirectory");
                $zips = $this->yellow->toolbox->getDirectoryEntries($backupZipDirectory, "/.*/", true, false, false);
                if ($this->yellow->system->get("backupLimit") != 0 && count($zips) > $this->yellow->system->get("backupLimit")) {
                    $files = $this->yellow->toolbox->getDirectoryEntries($backupZipDirectory, "/.*/", true, false, false);
                    $newer = array_splice($this->yellow->toolbox->getDirectoryEntries($backupZipDirectory, "/.*/", true, false, false), -$this->yellow->system->get("backupLimit"));
                    $delete = array_diff($files, $newer);
                    foreach ($delete as $del) {
                        $this->yellow->toolbox->deleteFile($backupZipDirectory . $del);
                    }
                }
            } else {
                $this->yellow->page->error(404);
            }
        }
    }


    public function copyProcessor($page, $path, $backupDirectory)
    {
        $files = $this->yellow->toolbox->getDirectoryEntriesRecursive("./" . $path, "/.*/", false, false);
        foreach ($files as $file) {
            if (!strpos($file, $this->yellow->system->get("backupDirectory"))) {
                $this->yellow->toolbox->copyFile($file, $backupDirectory  . "/" . ltrim($file, './'), true);
            }
        }
    }

    public function zip($path, $zipfile)
    {
        $za = new ZipArchive();
        $za->open($zipfile, ZIPARCHIVE::CREATE);
        $this->zipSub($za, $path);
        $za->close();
    }

    public function zipSub($za, $path, $parentPath = '')
    {
        $dh = opendir($path);
        while (($entry = readdir($dh)) !== false) {
            if ($entry == '.' || $entry == '..') {
            } else {
                $localPath = $parentPath . $entry;
                $fullpath = $path . '/' . $entry;
                if (is_file($fullpath)) {
                    $za->addFile($fullpath, $localPath);
                } else if (is_dir($fullpath)) {
                    $za->addEmptyDir($localPath);
                    $this->zipSub($za, $fullpath, $localPath . '/');
                }
            }
        }
        closedir($dh);
    }
}
