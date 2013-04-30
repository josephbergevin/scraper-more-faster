<?php

/*
Class: ScraperMoreFaster
Author: Joe Bergevin (joe@joescode.com)	
*/

class ScraperMoreFaster {

	public $html = false;
	public $current_directory;
	public $current_url;
	protected $domDoc = false;
	public $response_header = false;
	public $urlsList = array();
	public $tagsList = array();

	/*
	Function: file_get_html
	Expects: $url to get the HTML contents.
	Purpose: Retrieve the HTML contents from the given.
	*/
	public function file_get_html($url) {
		$this->current_url = $url;
		$this->html = file_get_contents($url);
		$this->response_header = $http_response_header;
		return;
	} // end of file_get_html function


	/*
	Function: loadDom
	Purpose: Load the HTML document into the DomDocument class 
	*/
	public function loadDom() {
		libxml_use_internal_errors(true);
		$this->domDoc = new DomDocument;
		$this->domDoc->loadHTML($this->html);
	} // end of loadDom function

	/*
	Function: str_get_html
	Expects: String containing HTML code.
	Purpose: Retrieve the HTML contents from the given 
	*/
	public function str_get_html($html_str) {
		$this->html = $html_str;
		return;
	} // end of str_get_html function

	/*
	Function: 	setCurrentDirectory
	Purpose: 	Sets the current directory. This is necessary for the 
				rebuildUrl method to work. 
	*/
	public function setCurrentDirectory( $current_directory ) {
		if ( substr($current_directory, -1) == "/" ) {
			$this->current_directory = $current_directory;
		} else {
			$this->current_directory = $current_directory ."/";
		}
		return;
	}

	/***********
	Method: 	getRedirectPath
	Expects: 	$
	Returns:	$
	Purpose: 	
	*/
	public function getRedirectPath() {
		//get the header in key/value format
		$this->response_header = get_headers($this->current_url);
		$responseHeader = $this->parseResponseHeader();
		
		if ( $responseHeader['status'][0] < 300 ) {
			return false;
		}

		$status_count = 0;
		$redirectPath = array();
		$redirectPath[$this->current_url] = 
			$responseHeader['status'][$status_count++];
		
		foreach ( $responseHeader['location'] as $location ) {
			$redirectPath[$location] = 
				$responseHeader['status'][$status_count++];
		}

		return $redirectPath;
	} // end of getRedirectPath method

	/*
	Function: 	parseResponseHeader
	Expects: 	$html var must be filled first.
	Purpose: 	Parse the response header from an HTML document.
	Returns: 	Plain Text of an HTML document.
	*/
	public function parseResponseHeader() {
		$responseHeaderArray = array();
		foreach ( $this->response_header as $header_item ) {
			$space_pos = strpos($header_item, " ");
			
			if ( $space_pos == 0 ) {
				$space_pos = strlen($header_item);
			}
			
			$header_key = strtolower(substr($header_item, 0, $space_pos));
			
			// check for a status header...
			if ( substr($header_key, 0, 5) == "http/" ) {
				$header_key = "status:";
			}

			$header_key 	= substr($header_key, 0, strlen($header_key) - 1);
			$length 		= strlen($header_item) - $space_pos;
			$header_value 	= substr($header_item, $space_pos + 1, $length);
			
			switch ($header_key) {
				case 'status':
					$responseHeaderArray[$header_key][] = 
					substr($header_value, 0, strpos($header_value, " "));
					break;
				case 'set-cookie':
					$responseHeaderArray[$header_key][] = $header_value;
					break;
				case 'location':
					$responseHeaderArray[$header_key][] = $header_value;
					break;
				case 'content-type':
					$responseHeaderArray[$header_key][] = $header_value;
					break;
				case 'server':
					$responseHeaderArray[$header_key] = $header_value;
					break;
				/*default:
					# code...
					break;*/
			}

			// $responseHeaderArray[$header_key] = $header_value;
			
		}
		return $responseHeaderArray;
	} 

	/*
	Function: getPlainText
	Expects: $html var must be filled first.
	Purpose: Parse the plain text (visible text) from an HTML document.
	Returns: Plain Text of an HTML document.
	*/
	public function getPlainText( $html_str = null ) {
		// remove comments and any content found in the the comment area 
		// (strip_tags only removes the actual tags).
		if ( !$html_str ) {
			$plaintext = preg_replace('#<!--.*?-->#s', '', $this->html);
		} else {
			$plaintext = preg_replace('#<!--.*?-->#s', '', $html_str); // for use of this function within this class
		}

		$plaintext = html_entity_decode($plaintext);

		// put a space between list items (strip_tags just removes the tags).
		$plaintext = preg_replace('#</li>#', ' </li>', $plaintext);
		
		// remove all script and style tags
		$plaintext = preg_replace('#<(script|style)\b[^>]*>(.*?)</(script|style)>#is', "", $plaintext);
		
		// remove br tags (missed by strip_tags)
		$plaintext = preg_replace("#<br[^>]*?>#", " ", $plaintext);
		
		// remove all remaining html
		$plaintext = strip_tags($plaintext);
		$plaintext = preg_replace('#\s+#', ' ', $plaintext);
		$plaintext = htmlspecialchars_decode($plaintext, ENT_QUOTES);
		$plaintext = preg_replace('/&(#\d+|\w+);/', ' ', $plaintext);
		$plaintext = $this->normalize_str($plaintext);
		
		return $plaintext;
	} // end of getPlainText function

	//replace smart quotes
	private function convert_smart_quotes( $string ) {
		$search = array(
			chr(145), chr(146), chr(147), chr(148),
			chr(151), chr(150), chr(133) );
		$replace = array(
			"'", "'", '"', '"', '--', '-', '...' );
		return str_replace($search, $replace, $string);
	}

	private function normalize_str($str) {
		$invalid = array('Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z',
		'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A',
		'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E',
		'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
		'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y',
		'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a',
		'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e',  'ë'=>'e', 'ì'=>'i', 'í'=>'i',
		'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
		'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y',  'ý'=>'y', 'þ'=>'b',
		'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', "`" => "'", "´" => "'", "„" => ",", "`" => "'",
		"´" => "'", "“" => "\"", "”" => "\"", "´" => "'", "&acirc;€™" => "'", "{" => "",
		"~" => "", "–" => "--", "’" => "'", "—" => "--" );
 
		$str = str_replace(array_keys($invalid), array_values($invalid), $str);
 
		return $str;
	}

	/*
	Function: 	getTitleTag
	Purpose: 	Gets the title from the source stored in the $html variable.
	Returns: 	Title as string.
	*/
	public function getTitleTag() {
		$title_tag_array = false;
		
		$found = $this->domDoc->getElementsByTagName("title");
		if ( $found->length > 0 ) {
			$title_text = $found->item(0)->nodeValue;
			$tag_code = $this->domDoc->saveHTML($found->item(0));
			// $tag_code = $this->innerHTML($found->item(0));
			
			$this->tagsList[$tag_code] = null;

			$title_tag_array[] = array(
				'tag_code' 				=> $tag_code,
				'title_tag_text'		=> trim($title_text),
				'title_tag_text_code' 	=> $this->innerHTML($found->item(0)) );
		}
		
		return $title_tag_array;
	} // end of getTitleTag function

	
	/*
	Function: 	getHeadingTags
	Purpose: 	Gets all heading tags from the source stored in the $html variable.
	Returns: 	Associative array conatining all found heading tags.
	*/
	public function getHeadingTags() {                
		
		$headings = array();
		for ($type = 1; $type < 6; $type++) {
			$matches = $this->domDoc->getElementsByTagName("h$type");
			$h_instance = 1;
			foreach ($matches as $heading) {
				$heading_tag = $this->domDoc->saveHTML($heading);
				// $a_tag = $this->getATagFromNode($heading_tag);
				$a_tag = $this->getATagFromNode($heading);
				$meta_attributes = $this->getElementAttributes($heading);

				if ( $a_tag !== false ) {
					$this->tagsList[$heading_tag] = 
						$this->tagsList[$a_tag['tag_code']];
				} else {
					$this->tagsList[$heading_tag] = null;
				}

				$headings[] = array(
					'level' 				=> $type, 
					'instance' 				=> $h_instance++,
					'heading_tag_text' 		=> trim($heading->nodeValue),
					'heading_tag_text_code' => $this->innerHTML($heading),
					'tag_code'	 			=> $heading_tag,
					'a_tag' 				=> $a_tag['tag_code'],
					'href'					=> $a_tag['href_rebuilt'],
					'meta_attributes'		=> $meta_attributes );
			}
		}

		return $headings;
	}

	/*
	Function: 	getATags
	Purpose: 	Gets all a tags from the source stored in the $html variable.
	Returns: 	Associative array conatining all found a tags.
	*/
	public function getATags() {
		// $this->loadDom();
		$tags_array = array();
		
		$matches = $this->domDoc->getElementsByTagName("a");
		foreach ($matches as $tag) {
			$tag_code = $this->domDoc->saveHTML($tag);
			// $img_tag = $this->getImgTagFromNode($tag_code);
			$img_tag = $this->getImgTagFromNode($tag);
			$href = $tag->getAttribute('href');
			if ( $href == "" || substr($href, 0, 4) == "http" ) {
				$href_rebuilt = $href;
			} else {
				$href_rebuilt = $this->rebuildUrl($href);
			}
			
			if ( $href_rebuilt !== "" ) {
				$this->urlsList[] = $href_rebuilt;
			}
			$this->tagsList[$tag_code] = $href_rebuilt;

			// $href_rebuilt = $this->rebuildUrl($tag)
			$meta_attributes = $this->getElementAttributes($tag);
			// $meta_attribs = $this->getMetaTagAttributes("a", $tag, false);

			$tags_array[] = array(
				'tag_code' 			=> $tag_code,
				'href'				=> $href,
				'href_rebuilt'		=> $href_rebuilt,
				'a_tag_text'		=> trim($tag->nodeValue),
				'a_tag_text_code' 	=> $this->innerHTML($tag),
				'img_tag'			=> $img_tag,
				'meta_attributes'	=> $meta_attributes );
		}

		return $tags_array;
	}

	/*
	Function: 	getImgTags
	Purpose: 	Gets all img tags from the source stored in the $html variable.
	Returns: 	Associative array conatining all found img tags.
	*/
	public function getImgTags() {
		$tags_array = array();
		
		$matches = $this->domDoc->getElementsByTagName("img");
		foreach ($matches as $tag) {
			$tag_code = $this->domDoc->saveHTML($tag);
			$src = $tag->getAttribute('src');
			if ( $src == "" || substr($src, 0, 4) == "http" ) {
				$src_rebuilt = $src;
			} else {
				$src_rebuilt = $this->rebuildUrl($src);
			}
			
			if ( $src_rebuilt !== "" ) {
				$this->urlsList[] = $src_rebuilt;
			}
			$this->tagsList[$tag_code] = $src_rebuilt;

			$meta_attributes = $this->getElementAttributes($tag);
			$tags_array[] = array(
				'tag_code' 			=> $tag_code,
				'src'				=> $src,
				'src_rebuilt'		=> $src_rebuilt,
				'meta_attributes'	=> $meta_attributes );
		
			}

		return $tags_array;
	}

	/*
	Function: 	getLinkTags
	Purpose: 	Gets all link tags from the source stored in the $html variable.
	Returns: 	Associative array conatining all found link tags.
	*/
	public function getLinkTags() {
		$tags_array = array();
		
		$matches = $this->domDoc->getElementsByTagName("link");
		foreach ($matches as $tag) {
			$tag_code = $this->domDoc->saveHTML($tag);
			$href = $tag->getAttribute('href');
			if ( $href == "" || substr($href, 0, 4) == "http" ) {
				$href_rebuilt = $href;
			} else {
				$href_rebuilt = $this->rebuildUrl($href);
			}
			
			if ( $href_rebuilt !== "" ) {
				$this->urlsList[] = $href_rebuilt;
			}
			$this->tagsList[$tag_code] = $href_rebuilt;

			$meta_attributes = $this->getElementAttributes($tag);
			$tags_array[] = array(
				'tag_code' 			=> $tag_code,
				'href'				=> $href,
				'href_rebuilt'		=> $href_rebuilt,
				'meta_attributes'	=> $meta_attributes );
		
			}

		return $tags_array;
	}

	/*
	Function: 	getMetaTags
	Purpose: 	Gets all meta tags from the source stored in the $html variable.
	Returns: 	Associative array conatining all found meta tags.
	*/
	public function getMetaTags() {
		$tags_array = array();
		
		$matches = $this->domDoc->getElementsByTagName("meta");
		foreach ($matches as $tag) {
			$tag_code = $this->domDoc->saveHTML($tag);
			/*$href = $tag->getAttribute('href');
			if ( $href == "" || substr($href, 0, 4) == "http" ) {
				$href_rebuilt = $href;
			} else {
				$href_rebuilt = $this->rebuildUrl($href);
			}*/
			
			$this->tagsList[$tag_code] = null;

			$meta_attributes = $this->getElementAttributes($tag);
			$tags_array[] = array(
				'tag_code' 			=> $tag_code,
				'meta_attributes'	=> $meta_attributes );
			}

		return $tags_array;
	}

	/*
	Function: 	getScriptTags
	Purpose: 	Gets all script tags from the source stored in the $html variable.
	Returns: 	Associative array conatining all found script tags.
	*/
	public function getScriptTags() {
		$tags_array = array();
		
		$matches = $this->domDoc->getElementsByTagName("script");
		foreach ($matches as $tag) {
			$tag_code = $this->domDoc->saveHTML($tag);
			$src = $tag->getAttribute('src');
			if ( $src == "" || substr($src, 0, 4) == "http" ) {
				$src_rebuilt = $src;
			} else {
				$src_rebuilt = $this->rebuildUrl($src);
			}
			
			if ( $src_rebuilt !== "" ) {
				$this->urlsList[] = $src_rebuilt;
			}
			$this->tagsList[$tag_code] = $src_rebuilt;

			$meta_attributes = $this->getElementAttributes($tag);
			$tags_array[] = array(
				'tag_code' 			=> $tag_code,
				'src'				=> $src,
				'src_rebuilt'		=> $src_rebuilt,
				'meta_attributes'	=> $meta_attributes );
			}

		return $tags_array;
	}

	/*
	Function: 	getStyleTags
	Purpose: 	Gets all style tags from the source stored in the $html variable.
	Returns: 	Associative array conatining all found style tags.
	*/
	public function getStyleTags() {
		$tags_array = array();
		
		$matches = $this->domDoc->getElementsByTagName("style");
		foreach ($matches as $tag) {
			$tag_code = $this->domDoc->saveHTML($tag);
			/*$href = $tag->getAttribute('href');
			if ( $href == "" || substr($href, 0, 4) == "http" ) {
				$href_rebuilt = $href;
			} else {
				$href_rebuilt = $this->rebuildUrl($href);
			}*/
			
			$this->tagsList[$tag_code] = null;

			$meta_attributes = $this->getElementAttributes($tag);
			$tags_array[] = array(
				'tag_code' 			=> $tag_code,
				'meta_attributes'	=> $meta_attributes );
			}

		return $tags_array;
	}

	/*
	Function: 	getImgTagFromNode
	Purpose: 	Use this when you want the entire nodeValue with HTML code
	Returns: 	the nodeValue with including HTML (if it has any).
	*/
	public function getImgTagFromNode( $node )  { 
		$matches = $node->getElementsByTagName("img");
		if ( $matches->length > 0 ) {
			$tag_code = $this->domDoc->saveHTML($matches->item(0));
			return $tag_code;
		} else {
			return false;
		}

	}

	/*
	Function: 	getATagFromNode
	Purpose: 	Use this when you want the entire nodeValue with HTML code
	Returns: 	the nodeValue with including HTML (if it has any).
	*/
	public function getATagFromNode( $node )  { 
		$matches = $node->getElementsByTagName("a");
		if ( $matches->length > 0 ) {
			$anchor_text 	= $matches->item(0)->nodeValue;
			// $tag_code 		= $this->innerHTML($matches->item(0));
			$tag_code 		= $this->domDoc->saveHTML($matches->item(0));
			$href 			= $matches->item(0)->getAttribute('href');

			if ( $href == "" || substr($href, 0, 4) == "http" ) {
				$href_rebuilt = $href;
			} else {
				$href_rebuilt = $this->rebuildUrl($href);
			}

			$a_tag = array(
				'tag_code' 		=> $tag_code,
				'a_tag_text'	=> trim($anchor_text),
				'href_rebuilt' 	=> $href_rebuilt );

			return $a_tag;
		} else {
			return false;
		}

	}

	/*
	Function: 	getATagFromString
	Purpose: 	Use this when you want the entire nodeValue with HTML code
	Returns: 	the nodeValue with including HTML (if it has any).
	*/
	public function getATagFromString( $node = null )  { 
		$begin = strpos($node, "<a ");
		
		if ( !$begin ) {
			return false;
		} else {
			$end 	= strpos($node, "/a>", $begin) + 2;
			$length = $end - $begin + 1;
			$a_tag 	= substr($node, $begin, $length);
			$href	= ""; // $this->getHrefFromATag($a_tag);
			return $a_tag;
		}
	}

	/*
	Function: 	getImgTagFromString
	Purpose: 	Use this when you want the entire nodeValue with HTML code
	Returns: 	the nodeValue with including HTML (if it has any).
	*/
	public function getImgTagFromString( $node = null )  { 
		$begin = strpos($node, "<img ");
		
		if ( !$begin ) {
			return false;
		} else {
			$end 		= strpos($node, ">", $begin);
			$length 	= $end - $begin + 1;
			$img_tag 	= substr($node, $begin, $length);
			return $img_tag;
		}
	}

	/*
	Function: 	getElementAttributes
	Purpose: 	Get all attributes (keys and values) from a given tag.
	Returns: 	Array of attributes.
	*/
	public function getElementAttributes( $element ) {
		$attributes = array();

		foreach($element->attributes as $attribute_name => $attribute_node) {
			$attributes[$attribute_name] = $attribute_node->nodeValue;
		}
		return $attributes;
	}

	/*
	Function: getElementById
	Expects: $html var must be filled first.
	Purpose: Retrieve the HTML contents from the given 
	Returns: Plain Text of an HTML document.
	*/
	public function getElementById( $elementId ) {
		// $this->loadDom();
		return $this->domDoc->getElementById($elementId);
	} // end of getElementById function

	/*
	Function: getElementByName
	Expects: $html var must be filled first.
	Purpose: Retrieve the HTML contents from the given 
	Returns: Plain Text of an HTML document.
	*/
	public function getElementByName( $elementName ) {
		return $this->domDoc->getElementByName($elementName);
	} // end of getElementByName function

	/*
	Function: 	innerHTMLDom
	Purpose: 	Use this when you want the entire nodeValue with HTML code
	Returns: 	the nodeValue with including HTML (if it has any).
	*/
	public function innerHTMLDom()  {  
		return $this->domDoc->saveXML();
	}

	/*
	Function: 	innerHTML
	Purpose: 	Use this when you want the entire nodeValue with HTML code
	Returns: 	the nodeValue with including HTML (if it has any).
	*/
	public function innerHTML( $node )  { 
		$doc = $node->ownerDocument;
		$frag = $doc->createDocumentFragment();
		
		foreach ($node->childNodes as $child) { 
			$frag->appendChild($child->cloneNode(TRUE)); 
		} 

		$inner_html = $doc->saveHTML($frag);
		$inner_html = substr($inner_html, 2, strlen($inner_html) - 5);
		return $inner_html;
	}

	/*
	Function: 	getMetaData
	Purpose: 	Gets all the meta data from the given html source.
	Returns: 	Associative array conatining all found meta attributes.
	*/
	public function getMetaData( $html_str = null ) {                
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
	Function: 	getMetaRegEx
	Purpose: 	Gets all meta-tag attributes from the source stored in the 
				$html variable.
	Returns: 	Associative array conatining all found meta-attributes.
				The keys are the meta-names, the values the content of the attributes.
				(like $tags["robots"] = "nofollow")
	Note: 		Uses the same regex statement as used in the PHPCrawl class, 
				written by Uwe Hunfeld
	*/
	public function getMetaRegEx() {
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

	
	/*
	Function: 	findATagNode
	Purpose: 	Gets all the meta data from the given html source.
	Returns: 	Associative array conatining all found meta attributes.
	*/
	public function findATagNode( $array = null ) {	
		foreach ($array as $value) {
			if ( substr($value, 0, 3) == "<a " ) {
				return $value;
			}
		}
		return null;
	}

	/*
	Function: 	getChildNodesArray
	Purpose: 	To get the child nodes of a node
	Returns: 	An array containing the child nodes of the given node
	*/
	public function getChildNodesArray( $node )  { 
		$doc = $node->ownerDocument;
		$child_node_array = array();
		
		foreach ($node->childNodes as $child) { 
			$child_node_array[] = $doc->saveXML($child);
		}
		return $child_node_array;
	}


	/*
	Function: 	rebuildUrl
	Purpose: 	To rebuid a relative link based on the page it is found in.
	Returns: 	The rebuilt URL.
	*/
	public function rebuildUrl( $relative_url = null ) {
		// If relative URL has a scheme, clean path and return.
		$r = $this->split_url( $relative_url );
		if ( $r === FALSE )
			return FALSE;
		if ( !empty( $r['scheme'] ) )
		{
			if ( !empty( $r['path'] ) && $r['path'][0] == '/' )
				$r['path'] = $this->url_remove_dot_segments( $r['path'] );
			return $this->join_url( $r );
		}
	 
		// Make sure the base URL is absolute.
		$b = $this->split_url( $this->current_directory );
		if ( $b === FALSE || empty( $b['scheme'] ) || empty( $b['host'] ) )
			return FALSE;
		$r['scheme'] = $b['scheme'];
	 
		// If relative URL has an authority, clean path and return.
		if ( isset( $r['host'] ) )
		{
			if ( !empty( $r['path'] ) )
				$r['path'] = $this->url_remove_dot_segments( $r['path'] );
			return $this->join_url( $r );
		}
		unset( $r['port'] );
		unset( $r['user'] );
		unset( $r['pass'] );
	 
		// Copy base authority.
		$r['host'] = $b['host'];
		if ( isset( $b['port'] ) ) $r['port'] = $b['port'];
		if ( isset( $b['user'] ) ) $r['user'] = $b['user'];
		if ( isset( $b['pass'] ) ) $r['pass'] = $b['pass'];
	 
		// If relative URL has no path, use base path
		if ( empty( $r['path'] ) )
		{
			if ( !empty( $b['path'] ) )
				$r['path'] = $b['path'];
			if ( !isset( $r['query'] ) && isset( $b['query'] ) )
				$r['query'] = $b['query'];
			return $this->join_url( $r );
		}
	 
		// If relative URL path doesn't start with /, merge with base path
		if ( $r['path'][0] != '/' )
		{
			$base = mb_strrchr( $b['path'], '/', TRUE, 'UTF-8' );
			if ( $base === FALSE ) $base = '';
			$r['path'] = $base . '/' . $r['path'];
		}
		$r['path'] = $this->url_remove_dot_segments( $r['path'] );
		return $this->join_url( $r );
	}

	public function url_to_absolute( $relative_url ) {
		// If relative URL has a scheme, clean path and return.
		$r = $this->split_url( $relative_url );
		if ( $r === FALSE )
			return FALSE;
		if ( !empty( $r['scheme'] ) )
		{
			if ( !empty( $r['path'] ) && $r['path'][0] == '/' )
				$r['path'] = $this->url_remove_dot_segments( $r['path'] );
			return $this->join_url( $r );
		}
	 
		// Make sure the base URL is absolute.
		$b = $this->split_url( $this->current_directory );
		if ( $b === FALSE || empty( $b['scheme'] ) || empty( $b['host'] ) )
			return FALSE;
		$r['scheme'] = $b['scheme'];
	 
		// If relative URL has an authority, clean path and return.
		if ( isset( $r['host'] ) )
		{
			if ( !empty( $r['path'] ) )
				$r['path'] = $this->url_remove_dot_segments( $r['path'] );
			return $this->join_url( $r );
		}
		unset( $r['port'] );
		unset( $r['user'] );
		unset( $r['pass'] );
	 
		// Copy base authority.
		$r['host'] = $b['host'];
		if ( isset( $b['port'] ) ) $r['port'] = $b['port'];
		if ( isset( $b['user'] ) ) $r['user'] = $b['user'];
		if ( isset( $b['pass'] ) ) $r['pass'] = $b['pass'];
	 
		// If relative URL has no path, use base path
		if ( empty( $r['path'] ) )
		{
			if ( !empty( $b['path'] ) )
				$r['path'] = $b['path'];
			if ( !isset( $r['query'] ) && isset( $b['query'] ) )
				$r['query'] = $b['query'];
			return $this->join_url( $r );
		}
	 
		// If relative URL path doesn't start with /, merge with base path
		if ( $r['path'][0] != '/' )
		{
			$base = mb_strrchr( $b['path'], '/', TRUE, 'UTF-8' );
			if ( $base === FALSE ) $base = '';
			$r['path'] = $base . '/' . $r['path'];
		}
		$r['path'] = $this->url_remove_dot_segments( $r['path'] );
		return $this->join_url( $r );
	}

	public function url_remove_dot_segments( $path ) {
		// multi-byte character explode
		$inSegs  = preg_split( '!/!u', $path );
		$outSegs = array( );
		foreach ( $inSegs as $seg )
		{
			if ( $seg == '' || $seg == '.')
				continue;
			if ( $seg == '..' )
				array_pop( $outSegs );
			else
				array_push( $outSegs, $seg );
		}
		$outPath = implode( '/', $outSegs );
		if ( $path[0] == '/' )
			$outPath = '/' . $outPath;
		// compare last multi-byte character against '/'
		if ( $outPath != '/' &&
			(mb_strlen($path)-1) == mb_strrpos( $path, '/', 'UTF-8' ) )
			$outPath .= '/';
		return $outPath;
	}

	public function join_url( $parts, $encode=TRUE )
	{
		if ( $encode )
		{
			if ( isset($parts['user']) )
				$parts['user']     = rawurlencode( $parts['user'] );
			if ( isset($parts['pass']) )
				$parts['pass']     = rawurlencode( $parts['pass'] );
			if ( isset($parts['host'])  &&
				!preg_match( '!^(\[[\da-f.:]+\]])|([\da-f.:]+)$!ui', $parts['host'] ) )
				$parts['host']     = rawurlencode( $parts['host'] );
			if ( !empty( $parts['path'] ) )
				$parts['path']     = preg_replace( '!%2F!ui', '/',
					rawurlencode( $parts['path'] ) );
			if ( isset($parts['query']) )
				$parts['query']    = rawurlencode( $parts['query'] );
			if ( isset($parts['fragment']) )
				$parts['fragment'] = rawurlencode( $parts['fragment'] );
		}
	 
		$url = '';
		if ( !empty( $parts['scheme'] ) )
			$url .= $parts['scheme'] . ':';
		if ( isset($parts['host']) )
		{
			$url .= '//';
			if ( isset($parts['user']) )
			{
				$url .= $parts['user'];
				if ( isset($parts['pass']) )
					$url .= ':' . $parts['pass'];
				$url .= '@';
			}
			if ( preg_match( '!^[\da-f]*:[\da-f.:]+$!ui', $parts['host'] ) )
				$url .= '[' . $parts['host'] . ']'; // IPv6
			else
				$url .= $parts['host'];             // IPv4 or name
			if ( isset($parts['port']) )
				$url .= ':' . $parts['port'];
			if ( !empty( $parts['path'] ) && $parts['path'][0] != '/' )
				$url .= '/';
		}
		if ( !empty( $parts['path'] ) )
			$url .= $parts['path'];
		if ( isset($parts['query']) )
			$url .= '?' . $parts['query'];
		if ( isset($parts['fragment']) )
			$url .= '#' . $parts['fragment'];
		return $url;
	}

	public function split_url( $url, $decode=TRUE )
	{
		$xunressub     = 'a-zA-Z\d\-._~\!$&\'()*+,;=';
		$xpchar        = $xunressub . ':@%';

		$xscheme       = '([a-zA-Z][a-zA-Z\d+-.]*)';

		$xuserinfo     = '((['  . $xunressub . '%]*)' .
						 '(:([' . $xunressub . ':%]*))?)';

		$xipv4         = '(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})';

		$xipv6         = '(\[([a-fA-F\d.:]+)\])';

		$xhost_name    = '([a-zA-Z\d-.%]+)';

		$xhost         = '(' . $xhost_name . '|' . $xipv4 . '|' . $xipv6 . ')';
		$xport         = '(\d*)';
		$xauthority    = '((' . $xuserinfo . '@)?' . $xhost .
						 '?(:' . $xport . ')?)';

		$xslash_seg    = '(/[' . $xpchar . ']*)';
		$xpath_authabs = '((//' . $xauthority . ')((/[' . $xpchar . ']*)*))';
		$xpath_rel     = '([' . $xpchar . ']+' . $xslash_seg . '*)';
		$xpath_abs     = '(/(' . $xpath_rel . ')?)';
		$xapath        = '(' . $xpath_authabs . '|' . $xpath_abs .
						 '|' . $xpath_rel . ')';

		$xqueryfrag    = '([' . $xpchar . '/?' . ']*)';

		$xurl          = '^(' . $xscheme . ':)?' .  $xapath . '?' .
						 '(\?' . $xqueryfrag . ')?(#' . $xqueryfrag . ')?$';
	 
	 
		// Split the URL into components.
		if ( !preg_match( '!' . $xurl . '!', $url, $m ) )
			return FALSE;
	 
		if ( !empty($m[2]) )        $parts['scheme']  = strtolower($m[2]);
	 
		if ( !empty($m[7]) ) {
			if ( isset( $m[9] ) )   $parts['user']    = $m[9];
			else            $parts['user']    = '';
		}
		if ( !empty($m[10]) )       $parts['pass']    = $m[11];
	 
		if ( !empty($m[13]) )       $h=$parts['host'] = $m[13];
		else if ( !empty($m[14]) )  $parts['host']    = $m[14];
		else if ( !empty($m[16]) )  $parts['host']    = $m[16];
		else if ( !empty( $m[5] ) ) $parts['host']    = '';
		if ( !empty($m[17]) )       $parts['port']    = $m[18];
	 
		if ( !empty($m[19]) )       $parts['path']    = $m[19];
		else if ( !empty($m[21]) )  $parts['path']    = $m[21];
		else if ( !empty($m[25]) )  $parts['path']    = $m[25];
	 
		if ( !empty($m[27]) )       $parts['query']   = $m[28];
		if ( !empty($m[29]) )       $parts['fragment']= $m[30];
	 
		if ( !$decode )
			return $parts;
		if ( !empty($parts['user']) )
			$parts['user']     = rawurldecode( $parts['user'] );
		if ( !empty($parts['pass']) )
			$parts['pass']     = rawurldecode( $parts['pass'] );
		if ( !empty($parts['path']) )
			$parts['path']     = rawurldecode( $parts['path'] );
		if ( isset($h) )
			$parts['host']     = rawurldecode( $parts['host'] );
		if ( !empty($parts['query']) )
			$parts['query']    = rawurldecode( $parts['query'] );
		if ( !empty($parts['fragment']) )
			$parts['fragment'] = rawurldecode( $parts['fragment'] );
		return $parts;
	}

	
	
} // end of ScraperMoreFaster class

