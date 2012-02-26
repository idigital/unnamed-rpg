$(function () {
	$('#items-list>li').click (handleItemOnClick);
});

/**
* Once an item in the #items-list gets click, this gets triggered. Loads the item details.
*
* @return void
*/
function handleItemOnClick () {
	item_id = $(this).attr ('id').slice (6);
	item_clicked = this;
	
	$.get (relroot+'/items/load_details.php', { 'item_id': item_id }, function (data) {
		$('#item-details').data ('item_id', data['item']['item_id']);
		
		$('#item-details>.item-name').html (data['item']['name']);
		$('#item-details>.item-description').html (data['item']['description']);
		
		if (data['item']['actions'] !== undefined) {
			// there are actions that they can do for this item, so create the actions list UL
			jq_ul = $('<ul></ul>');
			
			for (i = 0; i < data['item']['actions'].length; i++) {
				// make the LI for the action...
				jq_li = $("<li class=\"link\">"+data['item']['actions'][i]['anchor']+"</li>");
				
				// we need to add the action type and the params it requires too...
				jq_li.data ('action', data['item']['actions'][i]['action']);
				jq_li.data ('params', data['item']['actions'][i]['params']);
				
				// also store the item LI (DOM) from the item list that was just clicked
				jq_li.data ('item_li', item_clicked);
				
				// now make it clickable.
				jq_li.click (handleActionClick);
				
				// And finally add it to the UL
				jq_ul.append (jq_li);
			}
			
			// add the UL to the item_actions div
			$('#item-details>.item-actions').empty().append (jq_ul);
		} else {
			$('#item-details>.item-actions').empty();
		}
	}, 'json');
}

/**
* Triggered onclick of an action LI.
*
* Sends out an ajax request to use the item, and displays the feedback.
*
* @return void
*/
function handleActionClick () {
	action = $(this).data ('action');
	params = $(this).data ('params');
	item_li = $(this).data ('item_li');
	
	$.get (
		'items/use_item.php',
		{
			'item_id': $('#item-details').data ('item_id'),
			'action': action,
			'params': params
		},
		function (data) {
			qty = parseInt ($(item_li).children ('.qty').html(), 10);

			if (data['status'] == "success") {
				if (data['action'] == "destroy") {
					// remove an item from the UI
					if (qty == 1) {
						$(item_li).remove();
						// remove the data about this item
						$('#item-details>.item-name').html ('Inventory');
						$('#item-details>.item-description').html ('Click on an item to see more data about it.');
						$('#item-details>.item-actions').empty();
					} else {
						$(item_li).children ('.qty').html (--qty);
					}
				} else if (data['action'] == "heal") {
					// animate the HP updating
					$('.char_health').html (data['current_health']);
					$('.char_health_bar').animate ({'width': data['current_health_percent']+'px'});
					
					// remove an item from the UI
					if (qty == 1) {
						$(item_li).remove();
						// remove the data about this item
						$('#item-details>.item-name').html ('Inventory');
						$('#item-details>.item-description').html ('Click on an item to see more data about it.');
						$('#item-details>.item-actions').empty();
					} else {
						$(item_li).children ('.qty').html (--qty);
					}
				} else if (data['action'] == "equip") {
					// remove an item from the UI
					if (qty == 1) {
						$(item_li).remove();
					} else {
						$(item_li).children ('.qty').html (--qty);
						
						// remove the data about this item
						$('#item-details>.item-name').html ('Inventory');
						$('#item-details>.item-description').html ('Click on an item to see more data about it.');
						$('#item-details>.item-actions').empty();
					}
				}
			} else {
				alert ("Something happened which meant your action failed. The error is:\n"+data['message']);
			}
		},
		"json"
	);
}