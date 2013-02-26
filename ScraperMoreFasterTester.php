<?php 

error_reporting(E_ERROR);
// error_reporting(E_All);

require_once "lib/ScraperMoreFaster.php";

$url = "http://www.yahoo.com/";
$a_tag = "<html><body><a href=\"http://www.seo.com\" title=\"Search Engine Optimization SEO &amp; Internet Marketing Company\" id=\"logo\"><img src=\"http://www.seo.com/wp-content/themes/seo-theme/library/images/seo-logo.png\" title=\"Search Engine Optimization SEO &amp; Internet Marketing Company\" id=\"logo-img\"></a></body><html>";

$scrapermorefaster = New ScraperMoreFaster;

$scrapermorefaster->str_get_html($a_tag);
// $scrapermorefaster->file_get_html($url);

echo "Child Nodes: <pre>";
print_r($scrapermorefaster->getChildNodes());


/*die();

$time_start = microtime(true);

$headings1 = $scrapermorefaster->getHeadingTags();

$current_time = microtime(true);
$time_passed1 = $current_time - $time_start;


// ***************************

$scrapermorefaster->loadDom();
$time_start = microtime(true);

$headings2 = $scrapermorefaster->getHeadingTagsDom();

$current_time = microtime(true);
$time_passed2 = $current_time - $time_start;

echo "<p>PhpDom: $time_passed1</p>";
echo "<p>RegEx: $time_passed2</p>";

echo "<pre>";
print_r($headings1);
echo "<p>--------------------------------------------------------</p>";
print_r($headings2);
*/

?>