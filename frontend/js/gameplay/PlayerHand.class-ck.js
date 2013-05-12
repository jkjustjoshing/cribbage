/**
 * PlayerHand object
 * Represents a Player's hand, both in memory and on the screen in the DOM
 * 
 * @param {Boolean} isCrib
 * @param {Array}  cardArray
 */function PlayerHand(e,t){this.isCrib=e;this.cards=[];for(var n=0;n<t.length;++n)t[n]instanceof PlayerCard?this.cards[this.cards.length]=t[n]:this.cards[this.cards.length]=new PlayerCard(0,"")}PlayerHand.prototype.remove=function(e){for(var t=0;t<this.cards.length;++t)if(this.cards[t].equals(e)){this.cards.splice(t);return!0}return!1};