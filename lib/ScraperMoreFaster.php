<?php

/*
Class: ScraperMoreFaster
Author: Joe Bergevin (joe@joescode.com)
Purpose: 
	
Call Examples: 	
	
*/



class ScraperMoreFaster {

	public $html = false;
	protected $domDoc = false;
	protected $responseHeaders = false;
	
	
	/*
	Function: __construct
	Purpose: Pre-set additional properties for the api call
	*/
	/*public function __construct() {
		
	}*/

	/*
	Function: file_get_html
	Expects: $url to get the HTML contents.
	Purpose: Retrieve the HTML contents from the given.
	*/
	public function file_get_html($url)
	{
		$this->html = file_get_contents($url);
		$this->responseHeaders = $http_response_header;
		return;
	} // end of file_get_html function

	/*
	Function: str_get_html
	Expects: String containing HTML code.
	Purpose: Retrieve the HTML contents from the given 
	*/
	public function str_get_html($html_str)
	{
		$this->html = $html_str;
		return;
	} // end of str_get_html function

	/*
	Function: plaintext
	Expects: $html var must be filled first.
	Purpose: Parse the plain text (visible text) from an HTML document.
	Returns: Plain Text of an HTML document.
	*/
	public function plaintext( $html_str = null )
	{
		// remove comments and any content found in the the comment area (strip_tags only removes the actual tags).
		if ( !$html_str ) {
			$plaintext = preg_replace('#<!--.*?-->#s', '', $this->html);
		} else {
			$plaintext = preg_replace('#<!--.*?-->#s', '', $html_str); // for use of this function within this class
		}

		// put a space between list items (strip_tags just removes the tags).
		$plaintext = preg_replace('#</li>#', ' </li>', $plaintext);
		
		// remove all script and style tags
		$plaintext = preg_replace('#<(script|style)\b[^>]*>(.*?)</(script|style)>#is', "", $plaintext);
		
		// remove br tags (missed by strip_tags)
		$plaintext = preg_replace("#<br[^>]*?>#", " ", $plaintext);
		
		// remove all remaining html
		$plaintext = strip_tags($plaintext);
		
		return $plaintext;
	} // end of plaintext function

	/*
	Function: getLinks
	Expects: $html var must be filled first.
	Purpose: Retrieve the HTML contents from the given 
	Returns: Plain Text of an HTML document.
	*/
	public function getLinks()
	{
		$this->loadDom();
		$xpath = new DOMXPath($dom);
		
		$xpath_query = '//a';
		$link_obj_group = $xpath->query( $xpath_query );
		
		$links_list = array();
		foreach( $link_obj_group as $link ) { 
			$links_list[] = array( 	'href' => $link->attributes->getNamedItem("href")->nodeValue,
									'anchor' => $link->nodeValue ); 
		}
		return $links_list;
	} // end of getLinks function

	/*
	Function: 	getATags
	Purpose: 	Gets all the a tags from the given html source.
	Returns: 	Array conatining all found a tags.
	*/
	public function getATags( $html_str )
	{                
  		preg_match_all(	"#<a[^>]*?>(.*?)<[^>]*?/a>#i", 
						$this->html, 
						$matches
					  );
		return $matches;
	}

	/*
	Function: getImages
	Expects: $html var must be filled first.
	Purpose: Retrieve the HTML contents from the given 
	Returns: Plain Text of an HTML document.
	*/
	public function getImages()
	{
		$this->loadDom();
		return $this->domDoc->getImages();
	} // end of getImages function

	/*
	Function: getElementById
	Expects: $html var must be filled first.
	Purpose: Retrieve the HTML contents from the given 
	Returns: Plain Text of an HTML document.
	*/
	public function getElementById( $elementId )
	{
		$this->loadDom();
		return $this->domDoc->getElementById($elementId);
	} // end of getElementById function

	/*
	Function: getElementByName
	Expects: $html var must be filled first.
	Purpose: Retrieve the HTML contents from the given 
	Returns: Plain Text of an HTML document.
	*/
	public function getElementByName( $elementName )
	{
		$this->loadDom();
		return $this->domDoc->getElementByName($elementName);
	} // end of getElementByName function

	/*
	Function: loadDom
	Purpose: Load the HTML document into the DomDocument class 
	*/
	public function loadDom()
	{
		if ( !$this->domDoc ) {
			$this->domDoc = new DomDocument;
			if ( isset($this->html) ) {
				$this->domDoc->loadHTML($this->html);
			}
		}
	} // end of loadDom function

	/*
	Function: 	getTitle
	Purpose: 	Gets the title from the source stored in the $html variable.
	Returns: 	Title as string.
	*/
	public function getTitle()
	{
		preg_match_all(	"#<title>(.+)<\/title>#i", 
						$this->html, 
						$title
					  );
		return $title[1][0];
	}

	/*
	Function: 	getHeadingTags
	Purpose: 	Gets all heading tags from the source stored in the $html variable.
	Returns: 	Associative array conatining all found heading tags.
	Note: 		getHeadingTagsDom is slightly faster, but it will not return tags within the heading tags.
	*/
	public function getHeadingTags( $include_a_tags = false )
	{
		preg_match_all(	"#<h(\d)[^>]*?>(.*?)<[^>]*?/h\d>#i", 
						$this->html, 
						$matches
					  );
		$headings = array();
		$h_instance = array();
		for ($i = 1; $i <= 6; $i++) { 
			$h_instance[$i] = 1;
		}

		foreach ($matches[1] as $key => $heading_key) {
			$headings[] = array(	'level' 		=> $heading_key, 
									'instance' 		=> $h_instance[$heading_key]++,
									'header_text' 	=> $this->plaintext($matches[0][$key]), 
									'raw_header' 	=> $matches[0][$key],
									'a_tag_raw' 	=> $this->getATags($matches[0][$key]) 
								);
		}
		return $headings;
	}

  	/*
	Function: 	getHeadingTagsDom
	Purpose: 	Gets all heading tags from the source stored in the $html variable.
	Returns: 	Associative array conatining all found heading tags.
	*/
	public function getHeadingTagsDom()
	{                
  		$this->loadDom();      

		$headings = array();
		for ($type = 1; $type < 6; $type++) {
			$matches = $this->domDoc->getElementsByTagName("h$type");
			foreach ($matches as $h) {
				$headings["h$type"][] = $h->textContent;
			}
		}
		return $headings;
	}

	/*
	Function: 	getMetaData
	Purpose: 	Gets all the meta data from the given html source.
	Returns: 	Associative array conatining all found meta attributes.
	*/
	public function getMetaData( $html_str = null )
	{                
  		if ( !$html_str ) {
			
			preg_match_all(	"#\s(\w*)\s*=\s*(?|\"([^\"]+)\"|'([^']+)'|([^\s><'\"]+))#i", 
							$this->html, 
							$matches
						  );
		} else {
			preg_match_all(	"#\s(\w*)\s*=\s*(?|\"([^\"]+)\"|'([^']+)'|([^\s><'\"]+))#i", 
							$html_str, 
							$matches
						  );
		}
		$meta_data = array_combine($matches[1], $matches[2]);
		return $meta_data;
	}

	/*
	Function: 	getChildren
	Purpose: 	Gets all the meta data from the given html source.
	Returns: 	Associative array conatining all found heading tags.
	*/
	public function getChildNodes()
	{                
  		$this->loadDom();
  		
  		// $this->html = $this->domDoc->saveHTML();


  		echo "length: " .$this->domDoc->childNodes->length;
  		foreach ($this->domDoc->childNodes as $child_node) {
  			// foreach ($child_node->childNodes as $child_node2) {
  				echo "<p>Node: $child_node->saveHTML()</p>";
  			// }
  		}

  		return array();
	}

	/*
	Function: 	getMetaTagAttributes
	Purpose: 	Gets all meta-tag attributes from the source stored in the $html variable.
	Returns: 	Associative array conatining all found meta-attributes.
				The keys are the meta-names, the values the content of the attributes.
				(like $tags["robots"] = "nofollow")
	Note: 		Uses the same regex statement as used in the PHPCrawl class, written by Uwe Hunfeld
	*/
	public function getMetaTagAttributes()
	{
		preg_match_all(	"#<\s*meta\s+".
						"name\s*=\s*(?|\"([^\"]+)\"|'([^']+)'|([^\s><'\"]+))\s+".
						"content\s*=\s*(?|\"([^\"]+)\"|'([^']+)'|([^\s><'\"]+))".
						".*># Uis", $this->html, $matches
					  );

		$tags = array();
		for ( $x = 0; $x < count($matches[0]); $x++ ) {
			$meta_name  = strtolower(trim($matches[1][$x]));
			$meta_value = strtolower(trim($matches[2][$x]));
			$tags[$meta_name] = $meta_value;
		}
		return $tags;
  	}

	
} // end of ScraperMoreFaster class

?>