$(function () {
	$('#act_attack').click (handleAttackClick);
});

function handleAttackClick () {
	$.post (relroot+'/fight_scripts/attack.php', function (json) {
		console.log (json);
	}, "json");
}