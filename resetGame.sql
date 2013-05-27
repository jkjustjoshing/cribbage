use jdk3414;
update gamespaces set gamestateID=1;
update gamespaces set cutCard=NULL;
update gamespaces set player1Score=0, player2Score=0, player1backPinPosition=0, player2backPinPosition=0;
update gamespaces set turnID=2;
delete from playerhands;
delete from playedcards;
