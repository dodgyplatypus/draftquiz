<?php

if ($htmlSource = file_get_contents("http://www.dota2.com/heroes/")) {
	preg_match_all("/http:\/\/cdn\.dota2\.com\/apps\/dota2\/images\/heroes\/([a-zA-Z_]*)_hphover.png/", $htmlSource, $matches);
	foreach ($matches[0] AS $key => $url) {
		$ch = curl_init($url);
		$fileOut = 'images/heroportraits/' . $matches[1][$key] . ".png";
		if ($fp = fopen($fileOut, 'wb')) {
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
			echo "<img src='$fileOut' />";
		}
		else {
			echo "Opening file $fileOut failed.";
		}
	}
}