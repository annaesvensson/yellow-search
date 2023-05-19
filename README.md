<p align="right"><a href="README-de.md">Deutsch</a> &nbsp; <a href="README.md">English</a> &nbsp; <a href="README-sv.md">Svenska</a></p>

# Search 0.8.26

Full-text search.

<p align="center"><img src="search-screenshot.png?raw=true" alt="Screenshot"></p>

## How to install an extension

[Download ZIP file](https://github.com/annaesvensson/yellow-search/archive/main.zip) and copy it into your `system/extensions` folder. [Learn more about extensions](https://github.com/annaesvensson/yellow-update).

## How to use a search

The search is available on your website as `http://website/search/`. It searches trough content of the entire website, only visible pages are included. To show a search field on your website use a `[search]` shortcut.

## How to customise a search

If you don't want to search trough the entire website, you can use different filters to customise search results. The `author:` filter finds pages by a specific author. The `language:` filter finds pages in a specific language. The `tag:` filter finds pages with a specific tag. The `folder:` filter finds pages in a specific folder. Once you're logged in with your user account, you can search with the `status:` filter for [hidden pages](https://github.com/annaesvensson/yellow-core) and [draft pages](https://github.com/annaesvensson/yellow-draft).

## Examples

Searching a website:

    coffee
    milk sugar
    "milk and sugar"

Searching a website, different filters:

    coffee author:datenstrom
    coffee language:en
    coffee tag:example
    coffee folder:help

Searching a website, additional filters for logged in users:

    status:private
    status:draft
    status:unlisted
    status:shared

Showing a search field:

    [search]
    [search /search/]
    [search /en/search/]

Content file with search field:

    ---
    Title: Example page
    ---
    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut 
    labore et dolore magna pizza. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris 
    nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit 
    esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt 
    in culpa qui officia deserunt mollit anim id est laborum.

    [search]

Content file with link to search:

    ---
    Title: Example page
    ---
    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut 
    labore et dolore magna pizza. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris 
    nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit 
    esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt 
    in culpa qui officia deserunt mollit anim id est laborum.

    [Search all pages](/search/).

## Settings

The following settings can be configured in file `system/extensions/yellow-system.ini`:

`SearchLocation` = search location  
`SearchPaginationLimit` = number of entries to show per page, 0 for unlimited  
`SearchPageLength` = maximum page length to show  

The following files can be customised:

`system/layouts/search.html` = layout file for search  

## Developer

Anna Svensson. [Get help](https://datenstrom.se/yellow/help/).
