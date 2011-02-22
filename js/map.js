$(function () {
	loadMap ();
	bindArrowKeys ();
});

function loadMap () {
	$('#map_table').load (relroot+'/map.draw.php');
}

/**
* Triggered by an onclick, and also by the directional keys
*/
function move (direction) {
	// reload the map...
	$('#map_table').load (relroot+'/map.draw.php', { move: direction });
}

function bindArrowKeys () {
	$(document).keypress (function (e) {
		var direction;
		var code = e.keyCode || e.which;
		
		switch (code) {
			case 104:
				direction = "west";
				break;
			case 106:
				direction = "south";
				break;
			case 107:
				direction = "north";
				break;
			case 108:
				direction = "east";
				break;
			case 121:
				direction = "nw";
				break;
			case 117:
				direction = "ne";
				break;
			case 98:
				direction = "sw";
				break;
			case 110:
				direction = "se";
				break;
		}
		
		if (direction != null) move (direction);
	});
}