$(function () {
	generateMap ();
	bindArrowKeys ();
});

/**
* @deprecated
*/
function loadMap () {
	$('#map_table').load (relroot+'/map.draw.php');
	console.log ("don't use loadMap()");
}

function generateMap (direction) {
	$.ajax ({
		cache: false,
		success: function (returned) {			
			$('#map_table').html ($('root>map_data', returned).text());
			$('#map_nav').html ($('root>navigation_data', returned).text());
		},
		data: { 'move': direction },
		error: function (jqXHR, textStatus, errorthrown) {
			console.log ("unsuccessful ajax call whilst trying to generate map: "+textStatus+" - "+ errorthrown);
		},
		dataType: "xml",
		type: "post",
		url: relroot+'/map.draw.php'
	});
}

function bindArrowKeys () {
	$(window).keyup (function (e) {
		var direction;
		// nab the key code rather than the charCode. We can't access charCode anyway in .keyup, but
		// keyCode - which has a code for each key - is what we can't rather than an ascii value for
		// each key (charCode)
		var code = e.keyCode;

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
		
		if (direction != null) generateMap (direction);
	});
}