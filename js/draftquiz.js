var heroes;
var matchStack = [];
var currentMatch;

$(document).ready(function () {
	getHeroes(false);
	// we go for non async ajax, since we want to preload images from first game
	getMatches(10, false);
	if (matchStack.length > 0) {
		preloadImages(matchStack[0]);
	}
	nextMatch();
});

/**
 * Gets heroes from the API and populates global table heroes with the results
 */
function getHeroes(useAsync) {
	useAsync = typeof useAsync !== 'undefined' ? useAsync : true;
	
	if (heroes !== undefined) {
		return heroes;
	}
	$.ajax({
		async: useAsync,
		url: '../api/getHeroes.php'
	}).done(function(data) {
		heroes = new Array();
		$.each(data, function(i, e) {
			heroes[e.id] = e;
		});
		debug('Heroes fetched');
	});;
}

function guessWinner(aGuess) {
	$.ajax({
		async: false,
		type: 'GET',
		data: { publicId: currentMatch.publicId, guess: aGuess, nocache: (new Date()).getTime() },
		url: '../api/getResult.php'
	}).done(function (data) {
		if (data.winner == undefined) {
			alert("API EI TOIMI!");
		}
		else if (data.winner == aGuess) {
			alert("Correct!");
		}
		else {
			alert("Wrong guess");
		}
		$(".dotabuff a").attr("href", "http://www.dotabuff.com/matches/" + data.match_id);
		$("#guessButtons").hide();
		$("#nextMatch").show();
	});
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
	currentMatch = matchStack.shift();
	displayMatch(currentMatch);
	
	$("#guessButtons").show();
	$("#nextMatch").hide();
	
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
		heroHtml = '\
		<li> \
			<div class="heroContainer">\
				<img src="images/layout/icon_' + heroes[this.hero].attr + '.png" alt="Attribute" class="heroAttr">\
				<div class="heroBorder">\
				<div class="heroLabel">' + heroes[this.hero].en_name + '</div>\
				<img src="' + heroes[this.hero].image + '" alt="' + heroes[this.hero].en_name + '" title="'  + heroes[this.hero].en_name + '">\
				</div>\
			</div>\
		</li>';
		if (this.team == "r") {
			radiantHtml += heroHtml
		}
		else {
			direHtml += heroHtml
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
function getMatches(matchCount, useAsync) {
	matchCount = typeof matchCount !== 'undefined' ? matchCount : 10;
	useAsync = typeof useAsync !== 'undefined' ? useAsync : true;
	
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