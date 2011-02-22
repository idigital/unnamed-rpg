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
	$(window).keyup (function (e) {
		var direction;
		var code = e.keyCode || e.which;

		switch (code) {
			case 72:
				direction = "west";
				break;
			case 74:
				direction = "south";
				break;
			case 75:
				direction = "north";
				break;
			case 76:
				direction = "east";
				break;
			case 89:
				direction = "nw";
				break;
			case 85:
				direction = "ne";
				break;
			case 66:
				direction = "sw";
				break;
			case 78:
				direction = "se";
				break;
		}
		
		if (direction != null) move (direction);
	});
}