<?php 

error_reporting(E_ERROR);
// error_reporting(E_All);

require_once "lib/class.scraperfast.php";

$url = "http://www.yahoo.com/";
$a_tag = "<a href=\"http://www.seo.com\" title=\"Search Engine Optimization SEO &amp; Internet Marketing Company\" id=\"logo\"><img src=\"http://www.seo.com/wp-content/themes/seo-theme/library/images/seo-logo.png\" title=\"Search Engine Optimization SEO &amp; Internet Marketing Company\" id=\"logo-img\"></a>";

$scraperfast = New ScraperFast;

$scraperfast->str_get_html($a_tag);
// $scraperfast->file_get_html($url);

echo "Child Nodes: <pre>";
print_r($scraperfast->getChildNodes());


/*die();

$time_start = microtime(true);

$headings1 = $scraperfast->getHeadingTags();

$current_time = microtime(true);
$time_passed1 = $current_time - $time_start;


// ***************************

$scraperfast->loadDom();
$time_start = microtime(true);

$headings2 = $scraperfast->getHeadingTagsDom();

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