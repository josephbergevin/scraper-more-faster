<?php 

error_reporting(E_All);

require_once "lib/ScraperMoreFaster.php";

$url = "http://www.yahoo.com/";


$smf = new ScraperMoreFaster;
	

$smf->setCurrentDirectory($url);
$smf->file_get_html($url);

$plain_text = $smf->getPlainText();

echo "<p>Plain Text of this Page: </p>";
echo "<p>$plain_text</p>";



$smf->loadDom();
$webPageInfo = array();
$webPageInfo['titleTag'] 		= $smf->getTitleTag();
$webPageInfo['aTags'] 			= $smf->getATags();
$webPageInfo['linkTags'] 		= $smf->getLinkTags();
$webPageInfo['imgTags'] 		= $smf->getImgTags();
$webPageInfo['headingTags'] 	= $smf->getHeadingTags();
$webPageInfo['scriptTags'] 		= $smf->getScriptTags();
$webPageInfo['styleTags'] 		= $smf->getStyleTags();
$webPageInfo['pageMetaTags']	= $smf->getMetaTags();

// these lists are built as the above methods are run.
$webPageInfo['tagsList'] 		= $smf->tagsList;
$webPageInfo['urlsList'] 		= $smf->urlsList;

echo "<pre>";

foreach ( $webPageInfo as $list_name => $arrayList ) {
	echo "<p>$list_name: -----------------------------------------------</p>";
	print_r($arrayList);
}

echo "</pre>";

// playground.dev/ScraperMoreFaster/smf_tester.php

?>