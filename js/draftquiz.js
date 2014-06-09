$(document).ready(function() {
	MatchManager.init();
});

$(document).on('click', '.vote-dire', function() {
	MatchManager.guessWinner(0);
	return false;
});

$(document).on('click', '.vote-radiant', function() {
	MatchManager.guessWinner(1);
	return false;
});

$(document).on('click', '#button-nextmatch', function() {
	MatchManager.nextMatch();
	return false;
});