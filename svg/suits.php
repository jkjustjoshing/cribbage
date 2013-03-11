<?php
	
	define("SUIT_DEFAULT_SCALE", "0.25");

	define("CLUB_FIRST", '<path id="club" d="
	                    M 20,70 
	                    Q 28,60 27,50 
	                    C -8,70 -8,17 20,27
	
	                    C -8,-8 68,-8  40,27 

	                    C 68,17 68,70 33,50
	                    Q 32,60 40,70" transform="');
						
	define("CLUB_LAST", ' scale('.SUIT_DEFAULT_SCALE.')" style="fill:black;" />');

	define("SPADE_FIRST", '<path id="spade" d="
	                         M 20,70 
							 Q 28,60 27,50 
							 
							 C -13,70 -7,23 30,0
							 
							 C 67,23 73,70 33,50
							 Q 32,60 40,70" transform="');
							 
	define("SPADE_LAST", ' scale('.SUIT_DEFAULT_SCALE.')" style="fill:black;" />');	

	define("HEART_FIRST", '<path id="heart" d="M 33,70 
	                     L 3,25 
						 C -10,0 28,-10 33,13 
						 C 38,-10 76,0 63,25 
						 L 33,70" transform="');

	define("HEART_LAST", ' scale('.SUIT_DEFAULT_SCALE.')" style="fill:red;" />');

	define("DIAMOND_FIRST", '<path id="diamond" d="
	                       M 30,0 
						   Q 16.95,18.25 0,35 
						   Q 16.95,51.75 30,70 
						   Q 43.05,51.75 60,35 
						   Q 43.05,18.25 30,0"
						   transform="');
	define("DIAMOND_LAST", ' scale('.SUIT_DEFAULT_SCALE.')" style="fill:red;" />');
		

?>
