$(function () {
	$('#items-list>li').click (handleItemOnClick);
});

function handleItemOnClick () {
	item_id = $(this).attr ('id').slice (6);
	
	$.get (relroot+'/items/load-details.php', { 'item_id': item_id }, function (data) {
		$('#item-details').data ('item_id', data['item_id']);
		
		$('#item-details>.item-name').html (data['name']);
		$('#item-details>.item-description').html (data['description']);
		
		if (data['actions'].length) {
			actions_data = "<ul>";
			
			for (i = 0; i < data['actions'].length; i++) {
				actions_data = actions_data + "<li class=\"link\">"+data['actions'][i]['action_type']+"</li>";
			}
			
			actions_data = actions_data + "</ul>";
			$('#item-details>.item-actions').html (actions_data);
			
			$('#item-details>.item-actions li').click (handleActionClick);
		} else {
			$('#item-details>.item-actions').html ('');
		}
	}, 'json');
}

function handleActionClick () {
	$.get ('items/use-item.php', { 'item_id': $('#item-details').data ('item_id'), 'action': $(this).html() }, function () {
		console.log ("action triggered and done.");
	});
}