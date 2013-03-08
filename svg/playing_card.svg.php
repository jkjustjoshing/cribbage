<?php
	
	header("Content-type: image/svg+xml");	
	
	if(!isset($_GET["suit"]) || !isset($_GET["number"])){
		exit(1);
	}
	
	$v_suit = $_GET["suit"];
	$v_number = intval($_GET["number"]);


	if(in_array($v_suit, array("diamond", "heart"))) $v_color = "red";
	else if(in_array($v_suit, array("club", "spade"))) $v_color = "black";
	else exit(1);
	
	if($v_number < 1 || $v_number > 13){
		exit(1);
	}
	
	if($v_number == 1) $v_number = "A";
	if($v_number == 11) $v_number = "J";
        if($v_number == 12) $v_number = "Q";
        if($v_number == 13) $v_number = "K";


	echo '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN"
	      "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">
	      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="140" x="0" y="0">';

	echo '<rect width="100" height="140" 
	            rx="8" ry="8" 
	            stroke="black" stroke-width="1" 
	            fill="none"></rect>';
	
	
	echo '<text x="10" y="25" font-family="Arial" font-size="20" fill="'.$v_color.'">'.$v_number.'</text>';




	echo '</svg>';
	
	
?>
