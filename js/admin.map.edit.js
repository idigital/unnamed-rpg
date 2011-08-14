$(function () {
	$('#blocks>li>img').click (selectBlock);
});

/**
* Changes the block type that the user will 'paint' with.
*
* This is done simply by setting the .selected class. You can get the selected block by checking
* which image has that class - there will only ever be one.
*/
function selectBlock () {
	$('#blocks>li>img').removeClass ('selected');
	$(this).addClass ('selected');
}

/**
* Using the details in the 'Create Map' form, creates a map by sending the data to a script.
*/
function createMap () {
	// check that the form validates first. we need a name, and the height and width.
	if ($('#create_name').val () == "" || parseInt ($('#create_width').val(), 10) <= 0 || parseInt ($('#create_height').val(), 10) <= 0) {
		alert ("You must supply an area name, and a numeric height and width.");
	}
	
	// send this data to the script with makes maps!
	$.ajax ({
		cache: false,
		data: {
			'name': $('#create_name').val (),
			'height': parseInt ($('#create_height').val(), 10),
			'width': parseInt ($('#create_width').val(), 10),
			'image': $('#create_default').val ()
		},
		error: function (jqXHR, textStatus, errorthrown) {
			console.log ("unsuccessful ajax call whilst trying to create map: "+textStatus+" - "+ errorthrown);
		},
		success: function (returned) {
			if (returned['status'] == "complete") {
				alert ("Map created!");
				loadMap (returned['map_id']);
			} else if (returned['status'] == "missing para") {
				alert ("Some required data was missing.");
			} else if (returned['status'] == "no auth") {
				alert ("Need to be an admin to use this script.");
			} else {
				alert ("Unknown response from creation script.");
			}
		},
		dataType: "json",
		type: "post",
		url: relroot+'/admin/map.create.php'
	});
}

/**
* Sets up and populates the UI for the map editor.
*/
function loadEditor (map_id) {
	$('#dashboard').hide ();
	$('#map_editor').show ();
	
	// get some data about the map, including it's name, and coord information
	$.ajax ({
		cache: false,
		data: {
			'map_id': map_id,
			'refine_x_from': ($('#refine_x_from').val()) ? $('#refine_x_from').val() : -99999,
			'refine_x_num': ($('#refine_x_num').val()) ? $('#refine_x_num').val() : -99999,
			'refine_y_from': ($('#refine_y_from').val()) ? $('#refine_y_from').val() : -99999,
			'refine_y_num': ($('#refine_y_num').val()) ? $('#refine_y_num').val() : -99999,
		},
		error: function (jqXHR, textStatus, errorthrown) {
			console.log ("unsuccessful ajax call whilst trying to create map: "+textStatus+" - "+ errorthrown);
		},
		success: function (returned) {
			if (returned['status'] == 200) {
				$('#map_editor>h1').html ('Editing '+returned['map_data']['map_name']);
				
				$('#refine_x_from').val (returned['refine']['x']['from']);
				$('#refine_y_from').val (returned['refine']['y']['from']);
				$('#refine_x_num').val (returned['refine']['x']['num']);
				$('#refine_y_num').val (returned['refine']['y']['num']);
				
				var canvasText = "";
				
				// now we need to output all the coords to the #canvas
				for (x=returned['refine']['x']['from'];x<(returned['refine']['x']['num']+returned['refine']['x']['from']);x++) {
					// check if we've actually been given this coord (we may have requested ['num'], but there's only ['num']-1 to display)
					if (undefined == returned['coords'][x]) continue;
					
					canvasText += "<div>";
					for (y=returned['refine']['y']['from'];y<(returned['refine']['y']['num']+returned['refine']['y']['from']);y++) {
						if (undefined == returned['coords'][x][y]) continue;
						
						canvasText += "<span id=\"block"+x+":"+y+"\"><img src=\""+relroot+"/images/map_images/"+returned['coords'][x][y]['image']+"\" title=\""+x+":"+y+"\" /></span>";
					}
					canvasText += "</div>";
				}
				
				// now append it to the canvas area
				$('#canvas').html (canvasText);
				
				$('#canvas>div').css ('min-width', returned['map_data']['num_columns']*40);
				$('#canvas>div>span').click (handleClickCanvasBlock);
			} else {
				console.log ("errored, with "+returned['status']);
			}
		},
		dataType: 'json',
		type: 'post',
		url: relroot+'/admin/map.load.php'
	});
}

function handleClickCanvasBlock () {
	coords = $(this).attr ('id').substr (5).split (':');
	x = coords[0];
	y = coords[1];
	
	// send the ajax to update this coord!
	$.ajax ({
		data: {
			'x': x,
			'y': y,
			'image': $('#blocks .selected').attr ('alt'),
			'type': $('#block_type').val (),
			'locality': $('#block_locality').val (),
			'map_id': $('#map_id').val ()
		},
		error: function (jqXHR, textStatus, errorthrown) {
			console.log ("unsuccessful ajax call whilst trying to create map: "+textStatus+" - "+ errorthrown);
		},
		success: function (returned) {
			
		},
		dataType: 'json',
		type: 'post',
		url: relroot+'/admin/map.save.php'
	});
}