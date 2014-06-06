/**
 * MATCHMANAGER
 * Maintains a list of matches and processes user guesses.
 * 
 * @param {function} $ Import jQuery as a global
 * 
 * Private variables:
 *   config
 *   currentMatch
 *   canGetMatches
 *   canGuess
 *   heroes
 *   matches
 * 
 * Public methods:
 *   init(settings)
 *   guessWinner(guess)
 * 
 * Private methods:
 *   getHeroes(callback)
 *   getMatches(callback)
 *   nextMatch()
 *   preloadImages()
 *   showMatch()
 *   updateConfig(settings)
 */

var MatchManager = (function($) {
	var config = {
		numberOfMatchesToGet: 10,
		timeout: 500
	};

	var currentMatch;
	var canGetMatches = true;
	var canGuess = true;
	var heroes = [];
	var matches = [];

	/**
	 * Initialize module
	 * @param {object} settings
	 */
	var init = function(settings) {
		updateConfig(settings);
		getHeroes(function() {
			getMatches(function() {
				nextMatch();
			});
		});
	};

	/**
	 * Process user guess
	 * @param {integer} guess 0 for Dire and 1 for Radiant
	 * @returns {Boolean} Always false
	 */
	var guessWinner = function(guess) {
		if (canGuess === false) {
			return false;
		}

		canGuess = false;

		$.ajax({
			type: 'GET',
			url: 'api/getResult.php',
			data: {
				publicId: currentMatch.publicId,
				guess: guess,
				nocache: (new Date()).getTime()
			},
			success: function(data) {
				if (data.winner === guess.toString()) {
					alert("Correct :D");
				}
				else {
					alert("Wrong :(");
				}
				nextMatch();
			},
			error: function() {
				alert("API IS KAPUT! :O");
			},
			complete: function() {
				canGuess = true;
				return false;
			}
		});
	};

	/**
	 * Get heroes
	 * @param {function} [callback]
	 */
	var getHeroes = function(callback) {
		if (heroes.length > 0) {
			return heroes;
		}

		$.ajax({
			type: 'GET',
			url: 'api/getHeroes.php',
			success: function(data) {
				$.each(data, function(i, hero) {
					heroes[hero.id] = hero;
				});
				
				if(typeof callback !== 'undefined') {
					callback();
				}
			}
		});
	};

	/**
	 * Get more matches
	 * @param {function} [callback]
	 */
	var getMatches = function(callback) {
		if (canGetMatches === false || matches.length > 10) {
			return;
		}

		canGetMatches = false;
		var oldCount = matches.length;

		$.ajax({
			type: 'GET',
			data: {
				count: config.numberOfMatchesToGet,
				nocache: (new Date()).getTime()
			},
			url: 'api/getRandomMatches.php',
			success: function(data) {
				$.each(data, function(i, match) {
					matches.push(match);
				});
			},
			complete: function() {
				canGetMatches = true;
				if (oldCount === 0) {
					preloadImages();
				}
				if (typeof callback !== 'undefined') {
					callback();
				}
			}
		});
	};

	/**
	 * Shift next match from list and show it
	 */
	var nextMatch = function() {
		currentMatch = matches.shift();
		getMatches();
		preloadImages();

		if (typeof currentMatch !== 'undefined') {
			showMatch(currentMatch);
		}
		else {
			setTimeout(function() {
				nextMatch();
			}, config.timeout);
		}
	};

	/**
	 * Preload images for next match
	 * @returns {undefined}
	 */
	var preloadImages = function() {
		if (matches.length > 0 && typeof heroes !== 'undefined') {
			$.each(matches[0].players, function(i, player) {
				if(typeof heroes[player.hero] !== 'undefined') {
					(new Image()).src = heroes[player.hero].image;
				}
			});
		}
	};

	/**
	 * Update DOM with match details
	 * @param {object} match
	 */
	var showMatch = function(match) {
		radiantHtml = "";
		direHtml = "";

		$.each(match.players, function(i, player) {
			heroHtml = '<li><div class="hero"><img src="' + heroes[player.hero].image + '" alt="' + heroes[player.hero].en_name + '" title="' + heroes[player.hero].en_name + '"></div></li>';
			if (player.team === "r") {
				radiantHtml += heroHtml;
			}
			else {
				direHtml += heroHtml;
			}
		});

		$('#radiant-heroes').html(radiantHtml);
		$('#dire-heroes').html(direHtml);
	};

	/**
	 * Iterate through settings and update them to config
	 * @param {object} settings
	 */
	var updateConfig = function(settings) {
		for (var key in settings) {
			if (settings.hasOwnProperty(key) && config.hasOwnProperty(key)) {
				config[key] = settings[key];
			}
		}
	};

	/**
	 * Return interface
	 */
	return {
		init: init,
		guessWinner: guessWinner
	};
}(jQuery));