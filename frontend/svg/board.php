<?php
	$color1 = "#ff3636";
	$color2 = "#3636ff";
?>

<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="100" height="522" x="0" y="0" id="scoreboard">
	
	<!-- Border Rectangle -->	
	<rect width="100" height="522" stroke="black" fill="#aaa" />

	<!-- One instance of the three long straight columns -->
	<g id="70_dots" transform="translate(10,70)">

		<!-- One grouping of 10 dots (5 blue, 5 red) -->
		<g id="10_dots">

			<!-- A red and a blue dot next to each other -->
			<g id="2_dots">
				
				<!-- A red and a blue background, without the dots -->
				<g id="1_background">
					<rect id="red_box" width="10" height="10" x="-5" y="-5" fill="<?php echo $color1; ?>" />
					<rect id="blue_box" width="10" height="10" x="5" y="-5" fill="<?php echo $color2; ?>" />
				</g>

				<!-- 2 opposing dots, without the background color (for the turns) -->
				<g id="2_dots_no_background">
					<circle r="3" cx="0" cy="0" fill="#dd5555" id="dot" />
					<circle r="3" cx="10" cy="0" fill="#5555dd" id="dot" />
				</g>
			</g>

			<!-- Repeat the set of 2 dots 5 times for a group of 10 -->
			<use xlink:href="#2_dots" transform="translate(0,10)" />
			<use xlink:href="#2_dots" transform="translate(0,20)" />
			<use xlink:href="#2_dots" transform="translate(0,30)" />
			<use xlink:href="#2_dots" transform="translate(0,40)" />
			<!-- Throw some background at the end to separate the groups of 10 -->	
			<use xlink:href="#red_box" transform="translate(0,50)" />
			<use xlink:href="#blue_box" transform="translate(0,50)" />
		</g>
		
		<!-- Duplicate the groups of 10 to make a long line of them (the straight-aways) -->
		<use xlink:href="#10_dots" transform="translate(0,60)" />
		<use xlink:href="#10_dots" transform="translate(0,120)" />
		<use xlink:href="#10_dots" transform="translate(0,180)" />	
		<use xlink:href="#10_dots" transform="translate(0,240)" />
		<use xlink:href="#10_dots" transform="translate(0,300)" />
		<use xlink:href="#10_dots" transform="translate(0,360)" />
	</g>
	
	<!-- Duplicate the straight-aways twice to have 3 of them -->
	<use xlink:href="#70_dots" transform="translate(35,0)" />
	<use xlink:href="#70_dots" transform="rotate(180,50,270)" />

	<g transform="translate(50,50)"><!-- big turn -->

		<!-- Filler background at the start of the curve -->
		<use xlink:href="#1_background" transform="translate(-40,7) scale(1,1.6)" />
		<use xlink:href="#1_background" transform="translate(40,7) rotate(180,0,0) scale(1,1.6)" />

		<!-- Smooth background to the turn -->
		<path d="M -45,0 a 45,45 0 1,1 90,0" fill="<?php echo $color1; ?>"/>
		<path d="M -35,0 a 35,35 0 1,1 70,0" fill="<?php echo $color2; ?>"/>
		<path d="M -25,0 a 25,25 0 1,1 50,0" fill="#aaa"/>

		<!-- Position a pair of background-less dots to prepare for repeated rotation -->
		<use xlink:href="#2_dots_no_background" transform="translate(-40,0)" id="rotating" />
		
		
		<!-- Copy and rotate the dots for the big turn -->
		<use xlink:href="#rotating" transform="rotate(18,0,0)" />
		<use xlink:href="#rotating" transform="rotate(36,0,0)" />
		<use xlink:href="#rotating" transform="rotate(54,0,0)" />
		<use xlink:href="#rotating" transform="rotate(72,0,0)" />

		<use xlink:href="#rotating" transform="rotate(108,0,0)" />
		<use xlink:href="#rotating" transform="rotate(126,0,0)" />
		<use xlink:href="#rotating" transform="rotate(144,0,0)" />
		<use xlink:href="#rotating" transform="rotate(162,0,0)" />
		<use xlink:href="#rotating" transform="rotate(180,0,0)" />
	</g>

	<g transform="translate(67.5,490)"><!-- small turn -->

		<!-- Smooth background to the turn -->
		<path d="M -27.5,0 a 27.5,27.5 0 1,0 55,0" fill="<?php echo $color1; ?>"/>
        <path d="M -17.5,0 a 17.5,17.5 0 1,0 35,0" fill="<?php echo $color2; ?>"/>
        <path d="m -7.5,0 a 7.5,7.5 0 1,0 15,0" fill="#aaa"/>

		<!-- Filler background at the start of the curve -->
		<use xlink:href="#1_background" transform="translate(22.5,-7) rotate(180,0,0) scale(1,1.6)" />
		<use xlink:href="#1_background" transform="translate(-22.5,-7) scale(1,1.6)" />
			
		<!-- Position a pair of background-less dots to prepare for repeated rotation -->
		<use xlink:href="#2_dots_no_background" transform="rotate(180,0,0) translate(-22.5,0)" id="small_rotating" />

		<!-- Copy and rotate the dots for the big turn -->
		<use xlink:href="#small_rotating" transform="rotate(45,0,0)" />
		<use xlink:href="#small_rotating" transform="rotate(90,0,0)" />
		<use xlink:href="#small_rotating" transform="rotate(135,0,0)" />
		<use xlink:href="#small_rotating" transform="rotate(180,0,0)" />
		
	</g>

</svg>
