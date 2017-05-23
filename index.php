<?php

include_once("includes/connect.php"); 
ini_set('max_execution_time', 600); 
require('simple_html_dom.php'); 

$myUrl = "http://www.toonget.net/alpha-cartoon/f";
$html = file_get_html($myUrl);

//scrape each season
foreach($html->find('div.right_col h3 a') as $element) { 
	
	if(strpos($element, 'Family Guy') !== false){

		$href = $element->href;
		$season = $element->plaintext;
		$all_seasons[$season] = $href;
	}

}
krsort($all_seasons, SORT_NATURAL);

foreach($all_seasons as $season => $link) {
	
	//check if season is in db, set done to true 
	$ar = explode(" ", $season);
	$num = array_values(array_filter($ar, 'is_numeric'));

	$sql1 = "SELECT * FROM `episodes` where season='$num[0]'";
	$query1 = mysqli_query($db_conx, $sql1);

	$html1 = file_get_html($link);
	if(mysqli_num_rows($query1) == count($html1->find('div#videos ul li a'))){echo "done at " . $season; break;}

	//scrape each episode
	foreach($html1->find('div#videos ul li a') as $episode) { 
		$res = explode(" ", $episode->plaintext);
		$nums = array_values(array_filter($res, 'is_numeric'));

		//echo "Season: " . $nums[0] . " Episode: ". $nums[1] . '<br><br>';

		//get iframe src for each episode
		$html2 = file_get_html($episode->href);

		foreach($html2->find('div.vmargin iframe') as $vid_url) { 
		//echo $vid_url->src . '<br>';

		$sql = "INSERT INTO `episodes` (`id`, `season`, `episode`, `url`) VALUES (NULL, '$nums[0]', '$nums[1]', '$vid_url->src');";
		$query = mysqli_query($db_conx, $sql);

		
		//echo '<br>';
		break;

			}


	}

}

mysqli_close($db_conx);




?>