use jdk3414;
update gamespaces set gamestateID=(SELECT id from gamestates where value='PEGGING');
update gamespaces set player1Score=0, player2Score=0, player1backPinPosition=0, player2backPinPosition=0;
update gamespaces set turnID=2;
delete from playerhands;
delete from playedcards;
insert into playerhands (gameID, playerID, playingcardID, inHand)
	VALUES
	(1, 1, 1, 1),
	(1, 1, 21, 1),
	(1, 1, 22, 1),
	(1, 1, 23, 1),
	(1, 2, 24, 1),
	(1, 2, 25, 1),
	(1, 2, 33, 1),
	(1, 2, 34, 1),
	(1, NULL, 29, NULL),
	(1, NULL, 30, NULL),
	(1, NULL, 31, NULL),
	(1, NULL, 32, NULL);
	    
					    
