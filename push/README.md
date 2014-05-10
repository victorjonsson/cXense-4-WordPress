
## cXense Push Utilities (wip)

This directory contains utility scripts that can be used to register large quantities of URL:s
at cXense (https://wiki.cxense.com/pages/viewpage.action?pageId=18843212).

**export.php** — Used to export URL:s from WordPress

**push.js** — Nodejs script used to register a bunch of URL:s at cXense


## Usage

### Extract URL:s from WordPress

Navigate to this directory in your console and run any of the commands below. The extracted URL:s will be
written to a file named *urls.txt* in the same directory. Each URL will be separated with a new line.

This script will extract 500 posts per second (to reduce allocated resources) until all URL:s is extracted. Running
this script several times will not create duplications of the extracted URL:s.

`$ php export.php` Extracts all URL:s to *urls.txt* separated with a new line

`$ php export.php livsstil` Extracts all URL:s of posts related to the category "livsstil"


### Ping cXense crawler (push.js)

This node-script will parse a file named "urls.txt" that should be located in the same directory and contain URL:s separated with a new line.
You can either create this file manually yourself or generate it with the extract script described above.

**urls.txt**

```
http://mywebsite.com/23932/an-url-of-some-sort
http://mywebsite.com/1990/another-url-of-some-sort
...
```

**Running the script**

```
$ node push.js api.user@website.com api-key

- Parsing urls.txt
- 13291 URL:s loaded into memory
* Pushed http://mywebsite.com/23932/an-url-of-some-sort successfully
* Pushed http://mywebsite.com/1990/another-url-of-some-sort successfully
...
```
