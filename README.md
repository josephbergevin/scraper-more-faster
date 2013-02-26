ScraperMoreFaster
=============

ScraperMoreFaster is a PHP class built to scrape the content of a webpage faster than SimpleHTMLDOM (by SourceForge). It came about when I needed a faster scraper solution for a web crawler. SimpleHTMLDOM is a wonderful parser, and very robust in it's feature set. But unfortunately too slow for crawler purposes, where every millisecond counts.

Setup
-----

The ScraperMoreFaster.php file found in the lib folder is the only file needed to use this class.

Usage
-----

### Defining the HTML to be Parsed

	$scraper_more_faster = new ScraperMoreFaster;

**To define the HTML file *from a URL*:**
	
	$scraper_more_faster->file_get_html($url);

This will define the HTML by using the file_get_contents php command to pull in the HTML from the given URL.

**To define the HTML file *from a string*:**
	
	$scraper_more_faster->str_get_html($html_str);

This will define the HTML simply from the string passed in the $html_str var.



### Scrape PlainText from page

My biggest purpose for creating this class was for the PlainText functionality. In speed tests, I found the plaintext functionality to be dozens of times faster than SimpleHTMLDOM's plaintext functionality. And in all comparison tests, the plaintext from each tool was 99% - 100% similar.

To run this command (after defining the HTML as desribed above):

	$scraper_more_faster->plaintext();


Examples
--------

See ScraperMoreFasterTester.php for example usage.