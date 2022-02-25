<?php
// Search extension

class YellowSiteSearch
{
    const VERSION = "0.8.19";
    public $yellow;         // access to API

    // Handle initialization
    public function onLoad($yellow)
    {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("searchFindLocation", "");
        $this->yellow->system->setDefault("searchLayoutFilter", "");
        $this->yellow->system->setDefault("searchFields", "title+50,description+50,tag+5,author+2");
        $this->yellow->system->setDefault("searchAddFields", "");
        $this->yellow->system->setDefault("searchFilterSupported", "tag,author,language,status,special");
        $this->yellow->system->setDefault("searchLocation", "/search/");
        $this->yellow->system->setDefault("searchPaginationLimit", "10");
    }

    // Handle page layout
    public function onParsePageLayout($page, $name)
    {
        if (strpos($name, "search") !== false) {
            $searchLocation = ($page->get("searchLocation")) ? $page->get("searchLocation") : $this->yellow->system->get("searchLocation");
            $page->set("searchLocation", $searchLocation);
            $searchPaginationLimit = ($page->get("searchPaginationLimit")) ? $page->get("searchPaginationLimit") : $this->yellow->system->get("searchPaginationLimit");
            $page->set("searchPaginationLimit", $searchPaginationLimit);
            $query = trim($page->getRequest("query"));
            $filtersSupported = ($page->get("searchFilterSupported")) ? $page->get("searchFilterSupported") : $this->yellow->system->get("searchFilterSupported");
            $filtersSupported = explode(',', $filtersSupported);

            list($tokens, $filters) = $this->getSearchInformation(mb_convert_kana($query, "s"), 10, $filtersSupported);
            if (!empty($tokens) || !empty($filters)) {
                $pages = $this->yellow->content->clean();
                $showInvisible = $this->yellow->getRequestHandler() == "edit" && isset($filters["status"]) && ($filters["status"] == "private" || $filters["status"] == "draft" || $filters["status"] == "unlisted");
                $searchFindLocation = ($page->get("searchFindLocation")) ? $page->get("searchFindLocation") : $this->yellow->system->get("searchFindLocation");
                if ($searchFindLocation) {
                    $spages = $this->yellow->content->find($searchFindLocation)->getChildren($showInvisible);
                } else {
                    $spages = $this->yellow->content->index($showInvisible, false);
                }
                $searchLayoutFilter = ($page->get("searchLayoutFilter")) ? $page->get("searchLayoutFilter") : $this->yellow->system->get("searchLayoutFilter");
                if ($searchLayoutFilter) {
                    $spages = $spages->filter("layout", $searchLayoutFilter);
                }
                $searchAddFields = ($page->get("searchAddFields")) ? $page->get("searchAddFields") : $this->yellow->system->get("searchAddFields");
                $fields = $this->yellow->system->get("searchFields") . ',' . $searchAddFields;
                $searchFields = array();
                $searchFields = explode(',',  $fields);
                foreach ($spages as $pageSearch) {
                    $searchScore = 0;
                    $searchTokens = array();
                    foreach ($tokens as $token) {
                        $token = $this->searchStr($token);
                        $score = substr_count($this->searchStr($pageSearch->getContent(true)), $token);
                        if ($score) {
                            $searchScore += $score;
                            $searchTokens[$token] = true;
                        }
                        if (is_array($searchFields)) {
                            foreach ($searchFields as $sf) {
                                $scoreHeader = explode('+', $sf);
                                if (stristr($this->searchStr($pageSearch->get($scoreHeader[0])), $token)) {
                                    $searchScore += $scoreHeader[1];
                                    $searchTokens[$token] = true;
                                }
                            }
                        }
                    }
                    if (count($tokens) == count($searchTokens)) {
                        $pageSearch->set("searchscore", $searchScore);
                        $pages->append($pageSearch);
                    }
                }
                if (!empty($filters)) {
                    foreach ($filtersSupported as $key => $val) {
                        if (isset($filters[$val])) $pages->filter($val, $filters[$val]);
                    }
                }
                $pages->sort("modified")->sort("searchscore");
                $this->yellow->page->set("titleHeader", $query . " - " . $this->yellow->page->get("sitename"));
                $this->yellow->page->set("titleContent", $this->yellow->page->get("title") . ": " . $query);
                $this->yellow->page->set("title", $this->yellow->page->get("title") . ": " . $query);
                $this->yellow->page->setPages("search", $pages);
                $this->yellow->page->setLastModified($pages->getModified());
                $this->yellow->page->setHeader("Cache-Control", "max-age=60");
                $this->yellow->page->set("status", count($pages) ? "done" : "empty");
            } else {
                if ($this->yellow->isCommandLine()) $this->yellow->page->error(500, "Static website not supported!");
                $this->yellow->page->set("status", "none");
            }
        }
    }

    public function getSearchInformation($query, $tokensMax, $filtersSupported)
    {
        $tokens = array_unique(array_filter($this->yellow->toolbox->getTextArguments($query), "strlen"));
        $filters = array();
        foreach ($_REQUEST as $key => $value) {
            if (in_array($key, $filtersSupported)) $filters[$key] = $value;
        }
        foreach ($tokens as $key => $value) {
            if (preg_match("/^(.*?):(.*)$/", $value, $matches)) {
                if (!empty($matches[1]) && !strempty($matches[2]) && in_array($matches[1], $filtersSupported)) {
                    $filters[$matches[1]] = $matches[2];
                    unset($tokens[$key]);
                }
            }
        }
        if ($tokensMax) $tokens = array_slice($tokens, 0, $tokensMax);
        return array($tokens, $filters);
    }

    public function searchStr($str)
    {
        return mb_convert_kana($str, 'KAsC');
    }
}
