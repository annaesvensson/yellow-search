<?php
// Search extension, https://github.com/datenstrom/yellow-extensions/tree/master/source/search

class YellowSearch {
    const VERSION = "0.8.23";
    public $yellow;         // access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("searchLocation", "/search/");
        $this->yellow->system->setDefault("searchPaginationLimit", "5");
        $this->yellow->system->setDefault("searchPageLength", "360");
    }
    
    // Handle page content of shortcut
    public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if ($name=="search" && ($type=="block" || $type=="inline")) {
            list($location) = $this->yellow->toolbox->getTextArguments($text);
            if (empty($location)) $location = $this->yellow->system->get("searchLocation");
            $output = "<div class=\"".htmlspecialchars($name)."\" role=\"search\">\n";
            $output .= "<form class=\"search-form\" action=\"".$this->yellow->page->base.$location."\" method=\"post\">\n";
            $output .= "<input class=\"form-control\" type=\"text\" name=\"query\" placeholder=\"".$this->yellow->language->getTextHtml("searchButton")."\" />\n";
            $output .= "<input type=\"hidden\" name=\"clean-url\" />\n";
            $output .= "</form>\n";
            $output .= "</div>\n";
        }
        return $output;
    }
    
    // Handle page layout
    public function onParsePageLayout($page, $name) {
        if ($name=="search") {
            $query = trim($page->getRequest("query"));
            list($tokens, $filters) = $this->getSearchInformation($query, 10);
            if (!empty($tokens) || !empty($filters)) {
                $pages = $this->yellow->content->clean();
                $showInvisible = $this->yellow->getRequestHandler()=="edit" && isset($filters["status"]);
                $pagesContent = $this->yellow->content->index($showInvisible, false);
                if (!empty($filters)) {
                    if (isset($filters["tag"])) $pagesContent->filter("tag", $filters["tag"]);
                    if (isset($filters["author"])) $pagesContent->filter("author", $filters["author"]);
                    if (isset($filters["language"])) $pagesContent->filter("language", $filters["language"]);
                    if (isset($filters["folder"])) $pagesContent->match("#$filters[folder]#i", false);
                    if (isset($filters["status"])) $pagesContent->filter("status", $filters["status"]);
                }
                if (isset($filters["status"]) && $filters["status"]=="shared" && $showInvisible) {
                    $pagesContent->merge($this->yellow->content->getShared($page->location));
                }
                foreach ($pagesContent as $pageContent) {
                    $searchScore = 0;
                    $searchTokens = array();
                    foreach ($tokens as $token) {
                        $score = substr_count(strtoloweru($pageContent->getContent(true)), strtoloweru($token));
                        if ($score) {
                            $searchScore += $score;
                            $searchTokens[$token] = true;
                        }
                        if (stristr($pageContent->getLocation(), $token)) {
                            $searchScore += $this->yellow->lookup->isFileLocation($pageContent->location) ? 10 : 100;
                            $searchTokens[$token] = true;
                        }
                        if (stristr($pageContent->get("title"), $token)) {
                            $searchScore += 50;
                            $searchTokens[$token] = true;
                        }
                        if (stristr($pageContent->get("tag"), $token)) {
                            $searchScore += 5;
                            $searchTokens[$token] = true;
                        }
                        if (stristr($pageContent->get("author"), $token)) {
                            $searchScore += 2;
                            $searchTokens[$token] = true;
                        }
                    }
                    if (count($tokens)==count($searchTokens)) {
                        $pageContent->rawData = $this->getRawDataSearch($pageContent, $tokens);
                        $pageContent->set("searchScore", $searchScore);
                        $pages->append($pageContent);
                    }
                }
                $pages->sort("modified", false)->sort("searchScore", false);
                $text = empty($query) ? $this->yellow->language->getText("searchSpecialChanges") : $query;
                $this->yellow->page->set("titleHeader", $text." - ".$this->yellow->page->get("sitename"));
                $this->yellow->page->set("titleContent", $this->yellow->page->get("title").": ".$text);
                $this->yellow->page->set("title", $this->yellow->page->get("title").": ".$text);
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
    
    // Return search information
    public function getSearchInformation($query, $tokensMax) {
        $tokens = array_unique(array_filter($this->yellow->toolbox->getTextArguments($query), "strlen"));
        $filters = array();
        $filtersSupported = array("tag", "author", "language", "folder", "status", "special");
        foreach ($_REQUEST as $key=>$value) {
            if (in_array($key, $filtersSupported)) $filters[$key] = $value;
        }
        foreach ($tokens as $key=>$value) {
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
    
    // Return raw data for search results, extract the relevant page content
    public function getRawDataSearch($page, $tokens) {
        $output = $outputStart = "";
        if (!empty($tokens)) {
            $foundStart = $insideCode = false;
            foreach ($tokens as $token) {
                if (stristr($page->get("title"), $token)) {
                    $foundStart = true;
                    break;
                }
            }
            foreach ($this->yellow->toolbox->getTextLines($page->getContent(true)) as $line) {
                if (!$foundStart) {
                    if (preg_match("/^`{3,}/", $line)) $insideCode ^= true;
                    if (!$insideCode && ($line=="\n" || preg_match("/^(\!)?\s*(\d*\.|\*|\-|\|)\s+/", $line))) {
                        $outputStart = $line;
                    } else {
                        $outputStart .= $line;
                    }
                    foreach ($tokens as $token) {
                        if (stristr($line, $token)) {
                            $output = $outputStart;
                            $foundStart = true;
                            break;
                        }
                    }
                } else {
                    $output .= $line;
                }
            }
        }
        if (empty($output)) $output = $page->getContent(true);
        return substrb($page->rawData, 0, $page->metaDataOffsetBytes).substrb($output, 0, 4096);
    }
}
