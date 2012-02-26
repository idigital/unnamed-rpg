var game_state = {};

$(function () {
	$('#act_attack').click (handleAttackClick);
	$('#act_nothing').click (handleDoNothingClick);
	$('#act_flee').click (handleFleeClick);
	
	loadActions ();
});

function loadActions() {
	$.ajax ({
		cache: false,
		dataType: 'json',
		url: relroot + '/fight_scripts/get_item_actions.php',
		success: function (json) {
			if (json['status'] === 'success') {
				// clear the actions box of whatever it currently has
				$('#act_actions').empty();
			
				for (item_index = 0; item_index < json['items'].length; item_index++) {
					for (action_index = 0; action_index < json['items'][item_index]['actions'].length; action_index++) {
						jq_p = $("<p class=\"link\"></p>");
						
						jq_p.append ('<b>'+json['items'][item_index]['name']+' (x'+json['items'][item_index]['qty']+')</b>: '+json['items'][item_index]['actions'][action_index]['anchor']);
						
						jq_p.data ('item_id', json['items'][item_index]['id']);
						jq_p.data ('action_type', json['items'][item_index]['actions'][action_index]['action']);
						jq_p.data ('params', json['items'][item_index]['actions'][action_index]['params']);
						
						jq_p.click (handleActionClick);
						
						$('#act_actions').append (jq_p);
					}
				}
			} else {
				alert ('Something bad happened with loading actions. The message was:\n'+json['message']);
			}
		},
		error: function () {
			alert ('Something bad happened with loading actions. Try refreshing?');
		}
	});
}

function handleActionClick () {
	action = $(this).data ('action_type');
	item_id = $(this).data ('item_id');
	params = $(this).data ('params');
	
	clearHistory ();

	$.get (
		relroot + '/fight_scripts/use_item.php',
		{
			'item_id': item_id,
			'action': action,
			'params': params
		},
		function (json) {
			if (json['status'] == "success") {
				if (json['use_status'] == "success") {
					if (json['action'] == "heal") {
						changeHP ("char", json['current_health']);
					}

					doMobAction (json);
					
					for (i=0;i<json['message'].length;i++) {
						addHistory (json['message'][i]['msg'], json['message'][i]['type']);
					}
				} else {
					addHistory ("You tried to use an item... but you couldn't. Try refreshing?", "#FF9999");
				}
			} else {
				alert ("Something happened which meant your action failed. The error message was:\n"+jsons['status_message']);
			}

			loadActions();
		},
		"json"
	);
}

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
			$('#actions').html ("<p><a href=\"?complete=true\"><strong>Click here</strong> to see what you found!</a></p>");
		}
		
		loadActions();
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
	
	loadActions();
}

function handleFleeClick () {
	$.post (relroot+'/fight_scripts/flee.php', function (json) {
		clearHistory ();
		
		if (json['fight_stage'] == "player flee success") {
			// they succeeded
			$('#actions').html ("<p><a href=\"?complete=true\"><strong>Click here</strong> to see what happened...</a></p>");
		} else {
			// only bother with the mob action if they failed running away
			doMobAction (json);
			loadActions();
		}
		
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
		$('#actions').html ("<p><a href=\"?complete=true\"><strong>Click here</strong> to see the aftermath...</a></p>");
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
function addHistory (content, colour) {
	$('#round_feedback').show ();

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
	
	$('.'+subject+'_health_bar').animate ({width: percent_health+"px"}).css('overflow', 'visible');
}