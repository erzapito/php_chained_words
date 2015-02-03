$(document).ready(function(){
	checkCurrentGame();
	getBestGames();
	
	$('#new_game').click(newGame);
	$('#send_next_word').unbind('click').click(sendNextWord);
});

function getBestGames(){
	var baseurl = window.URLS.baseurl;
	
	$.ajax({
		'method': 'GET',
		'url': baseurl + '/games/best',
		'dataType':'json',
		'success' : function(data) {
			showBestGames(data);
		}
	})
}

function showBestGames(data) {
	var bP = $('#best_players tbody');
	bP.html('');
	for (var i = 0; i < data.length; i++) {
		var game = data[i];
		var row = $('<tr/>');
		row.append( $('<td/>').append(game.user) );
		row.append( $('<td/>').append(game.total_words) );
		row.append( $('<td/>').append(game.last_word) );
		bP.append(row);
	}
}

function checkCurrentGame(){
	var baseurl = window.URLS.baseurl;
	
	$.ajax({
		'method': 'GET',
		'url': baseurl + '/games/current',
		'dataType':'json',
		'success' : function(gameData) {
			loadGameData(gameData);
		}
	})
}

function addNewWords(w1,w2) {
	console.log(w1);
	console.log(w2);
	
	var i1 = $('<li/>').append(w1).hide();
	i1.prependTo('#last_words').fadeIn('slow', function(){
		var i2 = $('<li/>').append(w2).hide();	
		i2.prependTo('#last_words').fadeIn('slow');
	});
}

function sendNextWord() {
	var baseurl = window.URLS.baseurl;
	
	var word = $('#next_word').val();
	
	var loading = $('#loading');
	loading.show();
	
	$.ajax({
		'method': 'POST',
		'url': baseurl + '/games/play?word='+word,
		'dataType':'json',
		'success' : function(playData) {
			if (playData.winned) {
				alert('you winned');
				resetGame();
			} else {
				addNewWords(word,playData.last_word);
			}
		},
		'error' : function(info, errorStatus, errorThrown) {
			alert('You lost');
			resetGame();
		},
		'complete': function() {
			loading.hide();
		}
	})
}

function resetGame() {
	$('#total_words').text('');
	$('#current_game').hide();
}

function loadGameData(gameData) {
	$('#total_words').text(gameData.total_words);
	
	var lastWordsList = $('#last_words');
	lastWordsList.html('');
	
	
	for (var i = 0; i < gameData.last_words.length; i++) {
		var element = gameData.last_words[i];
		var e = $('<li/>').append(element);
		lastWordsList.append(e);
	};
	
	$('#current_game').show();
};

function newGame() {
	var baseurl = window.URLS.baseurl;
	
	$.ajax({
		'method':'POST',
		'url': baseurl + '/games/create',
		'dataType' : 'json',
		'success' : function(gameData) {
			loadGameData(gameData);
		},
	});
}