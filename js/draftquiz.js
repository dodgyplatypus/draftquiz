var config;

$(document).ready(function() {
	config = MatchManager.init();
	if (config['matchType'] === 'c') {
		$('#select-match-type').html('Competitive');
	}
	else if (config['matchType'] === 'p') {
		$('#select-match-type').html('Public');
	}
	else if (config['matchType'] === 'b') {
		$('#select-match-type').html('Both');
	}
});

$(document).on('click', '.vote-dire', function() {
	MatchManager.guessWinner(0);
	return false;
});

$(document).on('click', '.vote-radiant', function() {
	MatchManager.guessWinner(1);
	return false;
});

$(document).on('click', '#button-next-match', function() {
	MatchManager.nextMatch();
	return false;
});

$(document).on('click', '#select-match-type', function() {
	var b = $('#select-match-type');
	if (b.html() == 'Competitive') {
		b.html('TI4 Main');
		MatchManager.updateConfig({'matchType': 'ti4_main'});
	}
	else if (b.html() == 'TI4 Main') {
		b.html('Public');
		MatchManager.updateConfig({'matchType': 'p'});
	}
	else if (b.html() == 'Public') {
		b.html('All');
		MatchManager.updateConfig({'matchType': 'b'});
	}
	else if (b.html() == 'All') {
		b.html('Competitive');
		MatchManager.updateConfig({'matchType': 'c'});
	}
	return false;
});

$(document).on('click', '#button-settings', function() {
	$('#guess-view').hide(500);
	$('#result-view').hide(500);
	$('#settings-view').show(500);
	return false;
});

$(document).on('click', '#button-save-settings', function() {
	MatchManager.clearMatchCache();
	MatchManager.nextMatch();
	$('#settings-view').hide(500);
	$('#guess-view').show(500);
	return false;
});

$(document).on('click', '#score-reset', function() {
	MatchManager.resetScore();
	return false;
});