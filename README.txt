README
Cribbage Game
Final project, 546
Josh Kramer
Live version - http://nova.it.rit.edu/~jdk3414/546/cribbage


BASIC INSTRUCTIONS
	Create account and/or login with new account.
	Once logged in current players show up on the left, group chat on the right.
	Click on an online player's name to challenge them (opens a 1 on 1 chat window as well)
	

PROVIDED ACCOUNTS
	username: josh       password: joshjosh
	username: dan        password: dandandan
	username: cribbage   password: cribbage


NOTABLE FEATURES
	-> The chat is built modularly, so many can be open on the page at the 
	   same time. When a player is challenged it opens up a chat with that 
	   player. This chat is the same one that is displayed while in a game 
	   with that user.
	-> The gameboard (so far) has been built in a way so that a user can 
	   hypothetically have multiple games at a time, each one with a 
	   different opponent.
	-> The gameboard (so far) has been built in a stable way, so that a 
	   refresh of the page does not mess the game up. Of course, this is 
	   currently limited to the situations in which the game actually 
	   functions, namely dealing. Also applies to the challenge system.
	-> Frontend and backend files are tied together by a single "config.php" 
	   file in the frontend directory. If the two are moving relative to each
	   other (like the backend directory moving to a non-web accessable 
	   directory) it's straightforward to point the frontend to the backend 
	   files.


AS-OF-YET MISSING FEATURES/BUGS
	-> Besides the login screen, styling is not done. The bootstrap look will 
	   be brought to the lobby as well.
	-> Clean up when messages are/are not displayed and hidden regarding the 
	   challenge system, and alerting the user if there is a challenge while 
	   they are on a different tab.
	-> In Safari (Firefox untested at the moment) the scoreboard does not 
	   display properly.
	-> When dragging cards to the crib box the data structures are 
	   sporadically/randomly getting corrupted
	-> The game is not implemented past dealing and dragging to 
	   the crib (most of the functionality has been built into the backend
	   already, it just hasn't been imelpmented in the frontend).
	   