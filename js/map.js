$(function () {
	loadMap();
});

function loadMap () {
	$('#map_table').load (relroot+'/map.draw.php');
}