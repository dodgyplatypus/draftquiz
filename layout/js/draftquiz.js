var heroes;
var matchStack = [];

$(document).ready(function () {
	getHeroes();
	getMatches();
});

/**
 * Gets heroes from the API and populates global table heroes with the results
 */
function getHeroes() {
	if (heroes !== undefined) {
		return heroes;
	}
	$.ajax({
		url: '../api/getHeroes.php'
	}).done(function(data) {
		heroes = new Array();
		$.each(data, function(i, e) {
			heroes[e.id] = e;
		});
		debug('Heroes fetched');
	});;
}


/**
 * Gets a next game from a matchStack, populates it if needed
 **/
function nextMatch() {
	if (matchStack.length == 0) {
		debug("Getting matches with sync-ajax...");
		getMatches(10, false);
		debug("Sync ajax done!");
	}
	else if (matchStack.length < 6) {
		getMatches(10);
	}
		
	displayMatch(matchStack.shift());
	
	if (matchStack.length > 0) {
		preloadImages(matchStack[0]);
	}
}

/**
 * Displays a single match in the UI
 * @todo put heroes in correct order
 */
function displayMatch(match) {
	radiantHtml = direHtml = "";
	debug("Showing new game, matches left in cache: " + matchStack.length);
	$.each(match.players, function(i, e) {
		if (this.team == "r") {
			radiantHtml += '<li><img src="' + heroes[this.hero].image + '" alt="' + heroes[this.hero].en_name + '" title="'  + heroes[this.hero].en_name + '"></li>';
		}
		else {
			direHtml += '<li><img src="' + heroes[this.hero].image + '" alt="' + heroes[this.hero].en_name + '" title="'  + heroes[this.hero].en_name + '"></li>';
		}
	});
	
	$('#radiant-heroes').html(radiantHtml);
	$('#dire-heroes').html(direHtml);
}

/**
 * Preloads images from a match
 */
function preloadImages(match) {
	// preload images, but only if heroes are fetched yet
	if (heroes !== undefined) {
		$.each(match.players, function(i, player) {
			(new Image()).src = heroes[player.hero].image;
		});
	}
}

/**
 * Loads matches from the API
 */
function getMatches(matchCount = 10, useAsync = true) {
	$.ajax({
		async: useAsync,
		type: 'GET',
		data: { nocache: (new Date()).getTime() },
		url: '../api/getRandomMatches.php',
		success: appendMatchStack
	});
}

/**
 * Pushes matches to stack
 */
function appendMatchStack(data, status) {
	$.each(data, function(i, match) {
		matchStack.push(match);		
	});
	debug("Appending new matches, now in stack: " + matchStack.length);
}

/**
 * Debug-output is outputted thru this
 */
function debug(text) {
	console.log(text);
}