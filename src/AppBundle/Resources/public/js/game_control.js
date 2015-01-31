$(document).ready(function(){
	checkCurrentGame();
	
	$('#new_game').click(newGame);
});

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

function loadGameData(gameData) {
	$('#total_words').text(gameData.total_words);
	
	var lastWordsList = $('#last_words');
	lastWordsList.html('');
	
	
	for (var i = 0; i < gameData.last_words.length; i++) {
		var elements = gameData.last_words[i];
		var e = $('<li/>').append(element);
		lostWordsList.append(e);
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