<?php 

$url = "http://www.seo.com/";

$html = file_get_contents($url);

		preg_match_all(	"#\s(\w*)\s*=\s*(?|\"([^\"]+)\"|'([^']+)'|([^\s><'\"]+))#i", 
						$html, 
						$matches
					  );


print_r($matches);
?>