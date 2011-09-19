$(function () {
	$('#act_attack').click (handleAttackClick);
});

function handleAttackClick () {
	$.post (relroot+'/fight_scripts/attack.php', function (json) {
		// clear the action history to make space for this new one
		clearHistory ();
		
		if (json['attack']['hit'] == true) {
			addHistory ("You blast the mob for <strong>"+json['attack']['hit_amount']+"</strong> damage!", "good");
		} else {
			addHistory ("You missed the mob.", "bad");
		}
	}, "json");
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