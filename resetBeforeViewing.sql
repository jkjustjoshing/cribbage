use jdk3414;
update gamespaces set gamestateID=(SELECT id from gamestates where value='PEGGING') WHERE id=1;
update gamespaces set turnID=2 where id=1;
delete from playerhands;
delete from playedcards;
insert into playerhands (gameID, playerID, playingcardID, inHand)
	VALUES
	(1, 1, 1, 0),
	(1, 1, 21, 0),
	(1, 1, 22, 0),
	(1, 1, 23, 0),
	(1, 2, 24, 0),
	(1, 2, 25, 0),
	(1, 2, 33, 0),
	(1, 2, 34, 1),
	(1, NULL, 29, NULL),
	(1, NULL, 30, NULL),
	(1, NULL, 31, NULL),
	(1, NULL, 32, NULL);
	    
					    
