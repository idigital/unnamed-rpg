$(function () {
	$('#blocks>li>img').click (selectBlock);
});

function selectBlock () {
	$('#blocks>li>img').removeClass ('selected');
	$(this).addClass ('selected');
}

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
			} else if (returned['status'] == "missing para") {
				alert ("Some required data was missing.");
			} else if (returned['status'] == "no auth") {
				alert ("Need to be an admin to use this script.");
			}
		},
		dataType: "json",
		type: "post",
		url: relroot+'/admin/map.create.php'
	});
}