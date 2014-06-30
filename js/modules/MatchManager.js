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
 *   nextMatch()
 *   resetScore()
 * 
 * Private methods:
 *   getHeroes(callback)
 *   getMatches(callback)
 *   preloadImages()
 *   showMatch()
 *   updateConfig(settings)
 *   addScore(correct)
 *   displayScore()
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
	var modes = [
		'None', 
		'All Pick',
		'Captain\'s Mode',
		'Random Draft',
		'Single Draft',
		'All Random',
		'Intro',
		'Diretide',
		'Reverse Captain\'s Mode',
		'The Greeviling',
		'Tutorial',
		'Mid Only',
		'Least Played',
		'New Player Pool',
		'Compendium Matchmaking'
	];
	
	/**
	 * Initialize module
	 * @param {object} settings
	 */
	var init = function(settings) {
		updateConfig(settings);
		displayScore();
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
			nextMatch();
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
				if (data[0].winner === guess.toString()) {
					$('#result').html("Correct!");
					addScore(1);
				}
				else {
					$('#result').html("Wrong...");
					addScore(0);
				}
				$('#result-tables').html(parseResult(data));
				$('#button-external-link').html('<a href="http://www.dotabuff.com/matches/' + data[0].match_id + '" target="_blank">View match on Dotabuff</a>');
			},
			error: function() {
				alert("API IS KAPUT! :O");
			},
			complete: function() {
				$('#guess-view').hide();
				$('#result-view').show();
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
			canGuess = true;
			showMatch(currentMatch);
		}
		else {
			setTimeout(function() {
				nextMatch();
			}, config.timeout);
		}
	};
	
	/**
	 * Parse match results into html
	 * @param {json} data
	 * @returns {string}
	 */
	var parseResult = function(data) {
		console.log(data);
		var html = '';
		var i;
		
		// title
		if(data[0].winner === '0') {
			html += '<h2>Dire victory</h2>';
		}
		else {
			html += '<h2>Radiant victory</h2>';
		}
		
		// start results
		html += '<div class="row"><div class="small-6 columns">';
		
		// radiant table
		html += '<table id="radiant-results">';
		html += '<tr><th>&nbsp;</th><th class="hero">Hero</th><th>Level</th><th>K</th><th>D</th><th>A</th></tr>';
		for(i = 0; i < 5; i++) {
			html += '<tr>\n\
						<td class="portrait"><img src="images/heroportraits/' + data[i].name + '.png" /></td>\n\
						<td>' + data[i].en_name + '</td>\n\
						<td>' + data[i].level + '</td>\n\
						<td>' + data[i].kills + '</td>\n\
						<td>' + data[i].deaths + '</td>\n\
						<td>' + data[i].assists + '</td>\n\
					</tr>';
		}
		html += '</table>';
		
		// switch column
		html += '</div><div class="small-6 columns">';
		
		// dire table
		html += '<table id="dire-results">';
		html += '<tr><th>&nbsp;</th><th class="hero">Hero</th><th>Level</th><th>K</th><th>D</th><th>A</th></tr>';
		for(i = 5; i < 10; i++) {
			html += '<tr>\n\
						<td class="portrait"><img src="images/heroportraits/' + data[i].name + '.png" /></td>\n\
						<td>' + data[i].en_name + '</td>\n\
						<td>' + data[i].level + '</td>\n\
						<td>' + data[i].kills + '</td>\n\
						<td>' + data[i].deaths + '</td>\n\
						<td>' + data[i].assists + '</td>\n\
					</tr>';
		}
		html += '</table>';
		
		// end column
		html += '</div></div>';
		
		return html;
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
		var radiantHtml = "";
		var direHtml = "";
		var heroHtml = "";
		
		$.each(match.players, function(i, player) {
			heroHtml = '<li>\n\
							<div class="hero outer">\n\
								<div class="inner">\n\
									<img class="portrait" src="' + heroes[player.hero].image + '" alt="' + heroes[player.hero].en_name + '" title="' + heroes[player.hero].en_name + '">\n\
									<div class="attribute"><img src="images/layout/icon_' + heroes[player.hero].attr + '.png" /></div>\n\
								</div>\n\
							</div>\n\
						</li>';
			if (player.team === "r") {
				radiantHtml += heroHtml;
			}
			else {
				direHtml += heroHtml;
			}
		});
		
		direHtml += '<li><button class="button round right">Guess<br/>Dire</button></li>';
		radiantHtml += '<li><button class="button round right">Guess<br/>Radiant</button></li>';
		
		// converts 3099 to 3000 - 3500, since we don't know mmr too accurately
		var mmrRange = (match.mmr - match.mmr % 500).toString() + ' - ' + (match.mmr - match.mmr % 500 + 500).toString();
		$('#match-info-details #match-mode').html(modes[match.mode]);
		$('#match-info-details #match-mmr').html(', MMR ' + mmrRange);

		$('#radiant-heroes').html(radiantHtml);
		$('#dire-heroes').html(direHtml);
		
		$('#guess-view').show();
		$('#result-view').hide();
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
	 * Keeps track of score, correct guesses and total
	 * Keeps UI updated as well
	 * @param correct integer
	 */
	var addScore = function(correct) {
		if (localStorage.getItem('scoreTotal') === null) {
			localStorage.setItem('scoreCorrect', '0')
			localStorage.setItem('scoreTotal', '0')
		}
		localStorage['scoreCorrect'] = parseInt(localStorage['scoreCorrect']) + correct;
		localStorage['scoreTotal'] = parseInt(localStorage['scoreTotal']) + 1;
		
		displayScore();
	}
	
	/**
	 * Updates the score status to the UI
	 **/
	var displayScore = function() {
		if (localStorage.getItem('scoreTotal') === null) {
			localStorage.setItem('scoreCorrect', '0')
			localStorage.setItem('scoreTotal', '0')
		}
		var scoreCorrect = parseInt(localStorage['scoreCorrect']);
		var scoreTotal = parseInt(localStorage['scoreTotal']);
		if (scoreTotal > 0) {
			var scoreRatio = (Math.round(scoreCorrect / scoreTotal * 100 * 10) / 10).toString() + '%';
		}
		else {
			var scoreRatio = '-';
		}	
		$('#score-correct').html(scoreCorrect);
		$('#score-total').html(scoreTotal);
		$('#score-ratio').html(scoreRatio);
	}
	
	/**
	 * Resets the score, asks confirmation first
	 **/
	var resetScore = function() {
		if (confirm("Are you sure want to reset the score?") == true) {
			localStorage.setItem('scoreCorrect', '0')
			localStorage.setItem('scoreTotal', '0');
			displayScore();
		}
		else {
			return false;
		}
	}
	
	/**
	 * Return interface
	 */
	return {
		init: init,
		guessWinner: guessWinner,
		nextMatch: nextMatch,
		resetScore: resetScore
	};
}(jQuery));