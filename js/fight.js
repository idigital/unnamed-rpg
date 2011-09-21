var game_state = {};

$(function () {
	$('#act_attack').click (handleAttackClick);
	$('#act_nothing').click (handleDoNothingClick);
});

function handleAttackClick () {
	$.post (relroot+'/fight_scripts/attack.php', function (json) {
		// clear the action history to make space for this new one
		clearHistory ();
		
		if (json['char']['attack']['hit'] == true) {
			// so what are we changing the health to?
			new_health = game_state['mob']['hp'] - json['char']['attack']['hit_amount'];
			changeHP ("mob", new_health);
		}
		
		doMobAction (json);
		
		for (i=0;i<json['message'].length;i++) {
			addHistory (json['message'][i]['msg'], json['message'][i]['type']);
		}
		
		// check the game state. other UI changes might be needed.
		if (json['fight_stage'] == "player win") {
			$('#actions').html ("<p><a href=\""+relroot+"/fight_scripts/aftermath.php\"><strong>Click here</strong> to see what you found!</a></p>");
		}
	}, "json");
}

function handleDoNothingClick () {
	$.post (relroot+'/fight_scripts/nothing.php', function (json) {
		clearHistory ();

		doMobAction (json);
		
		for (i=0;i<json['message'].length;i++) {
			addHistory (json['message'][i]['msg'], json['message'][i]['type']);
		}		
	}, "json");
}

function doMobAction (json) {
	if (json['mob'] != undefined && json['mob']['attack']['hit'] == true) {
		new_health = game_state['char']['hp'] - json['mob']['attack']['hit_amount'];
		changeHP ("char", new_health);
	}
	
	if (json['fight_stage'] == "mob win") {
		// remove all the actions, and change it with a link back to the map
		$('#actions').html ("<p><a href=\""+relroot+"/fight_scripts/aftermath.php\"><strong>Click here</strong> to see the aftermath...</a></p>");
	}
}

function clearHistory () {
	$('#round_feedback').hide ();
	$('#round_feedback>ul').html ('');
}

/**
* Adds a line to the round history
*
* @param string String to use as the history
* @param string 'good','bad','note'
*/
function addHistory (content, flag) {
	$('#round_feedback').show ();

	switch (flag) {
		case 'good': colour = "rgb(0, 255, 0)"; break;
		case 'bad': colour = "rgb(255, 153, 153)"; break;
		case 'note': colour = "rgb(226, 224, 224)"; break;
		default: colour = "rgb(226, 224, 224)"; break;
	}

	$('#round_feedback>ul').append ('<li style="background-color: '+colour+'">'+content+'</li>');
}

/**
* Changes the display of health to an amount.
*
* Note: This is "change to" not "change by".
*
* @param string 'char','mob'
* @param number Amount to be changed to
*/
function changeHP (who, amount) {
	// validate 'who'. must be either 'char', or 'mob'. default to character
	subject = (who == "mob") ? "mob" : "char";
	// amount needs to be a number
	amount = (isNaN (parseInt (amount, 10))) ? 0 : parseInt (amount, 10);
	
	// don't let the health drop below zero
	if (amount < 0) amount = 0;
	
	game_state[subject]['hp'] = amount;
	$('.'+subject+'_health').html (amount);
	
	// what percent is amount of the full health?
	percent_health = (game_state[subject]['hp']/game_state[subject]['max_hp']) * 100;
	
	$('.'+subject+'_health_bar').animate ({width: percent_health+"px"});
}