<?php
	
	require_once("club.php");
	require_once("spade.php");
	require_once("diamond.php");
	require_once("heart.php");
		
	function shape($v_shape, $v_transform){
		static $v_printed = false;
		
		if(!$v_printed){
			
			$v_printed = true;
			switch($v_shape){
				case("heart"):
					return HEART_FIRST . $v_transform . HEART_LAST;			
					break;
				case("diamond"):
					return DIAMOND_FIRST . $v_transform . DIAMOND_LAST;
					break;
				case("club"):
					return CLUB_FIRST . $v_transform . CLUB_LAST;
					break;
				case("spade"):
					return SPADE_FIRST . $v_transform . SPADE_LAST;
					break;
				default:
					return "";	
			}
		}
	}

	
	function svgcard($v_suit, $v_number){
	
		$v_string_acc = "";
		$v_number = intval($v_number);


		if(in_array($v_suit, array("diamond", "heart"))) $v_color = "red";
		else if(in_array($v_suit, array("club", "spade"))) $v_color = "black";
		else return "";
	
		if($v_number < 1 || $v_number > 13){
			return "";
		}
	
		if($v_number == 1) $v_number = "A";
		else if($v_number == 11) $v_number = "J";
        else if($v_number == 12) $v_number = "Q";
        else if($v_number == 13) $v_number = "K";

		// Border rectangle
		$v_string_acc .= '<rect width="100" height="140" 
		            rx="8" ry="8" 
		            stroke="black" stroke-width="1" 
		            fill="none" />';
	
	
		// Rightside-up text
		$v_string_acc .= '<text id="text" x="5" y="20" font-family="Arial" font-size="20" fill="'.$v_color.'">'.$v_number.'</text>';

		// Upside-down text
		$v_string_acc .= '<use xlink:href="#text" transform="rotate(180,50,70)"/>';


		$v_string_acc .= shape($v_suit,"translate(42.5,61.25)");

		return $v_string_acc;

	}



	if(isset($_GET["suit"]) && isset($_GET["number"])){
    

    	// Header for SVG
    	header("Content-type: image/svg+xml");
	
		// Doctype and header for document
    	echo '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN"
        	  "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">
        	  <svg xmlns="http://www.w3.org/2000/svg"
        	  xmlns:xlink="http://www.w3.org/1999/xlink" width="100" height="140" x="0" y="0">';	

		echo svgcard($_GET["suit"], $_GET["number"]);
	
		echo '</svg>';
	}
?>
