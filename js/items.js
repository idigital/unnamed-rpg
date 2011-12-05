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
	action = $(this).html();
	item_li = '#itemid'+$('#item-details').data ('item_id');

	$.get ('items/use-item.php', { 'item_id': $('#item-details').data ('item_id'), 'action': action }, function (data) {
		if (data == 'true') {
			if (action == "Destroy") {
				qty = parseInt ($(item_li).children ('.qty').html(), 10);
				
				if (qty == 1) {
					$(item_li).remove();
				} else {
					--qty;
					
					$(item_li).children ('.qty').html ((qty));
				}
			}
		} else {
			alert ("Something happened which meant your action failed.");
		}
	});
}