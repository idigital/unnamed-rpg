$(function () {
	$('#items-list>li').click (handleItemOnClick);
	
	$('#equipment .lefthand').click ({"where": "lefthand"}, handleEquipmentOnClick);
	$('#equipment .righthand').click ({"where": "righthand"}, handleEquipmentOnClick);
	$('#equipment .head').click ({"where": "head"}, handleEquipmentOnClick);
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

function handleEquipmentOnClick (event) {
	$.get (relroot+'/items/equipped_details.php', { 'position': event.data.where }, function (data) {
		if (data['item_found']) {
			$('#item-details').data ('item_id', data['item']['id']).data ('position', data['position']).data ('name', data['item']['name']);
			
			$('#item-details>.item-name').html ('Equiped: '+data['item']['name']);
			$('#item-details>.item-description').html (data['item']['description']);
			
			$('#item-details .item-actions').empty ();
			
			jq_unequip = $('<p class="link" onclick="handleUnequipOnClick(\''+event.data.where+'\')">Unequip</p>');
			
			$('#item-details .item-actions').append (jq_unequip);
		} else {
			$('#item-details>.item-name').html ('Equiped: Nothing.');
			$('#item-details>.item-description').html ("You have nothing equipped to here.");
			
			$('#item-details .item-actions').empty ();
		}
	}, 'json');
}

function handleUnequipOnClick () {
	$.ajax ({
		'url': relroot + '/items/unequip.php',
		'data': {
			'position': $('#item-details').data ('position')
		},
		'success': function (json) {
			if (json['status'] == "success") {
				// now we need to increase the number of this item in the items list.
				// is there already one of these in the item list?
				if ($('#itemid'+$('#item-details').data ('item_id')).length > 0) {
					// yes, so we just increase the count by one
					$('#itemid'+$('#item-details').data ('item_id')).children('.qty').html (parseInt ($('#itemid'+$('#item-details').data ('item_id')).children('.qty'), 10) + 1);
				} else {
					// nope. so we have to make it.
					jq_li = $('<li id="itemid'+$('#item-details').data ('item_id')+'" class="item"><img width="50px" height="50px" alt="'+$('#item-details').data ('name')+'" src="asdfd" /><span class="qty">1</span>');
					
					jq_li.click (handleItemOnClick);
					
					$('#items-list').append (jq_li);
				}
				
				// now we remove the item data from the equipped UI
				jq_p = $('<p>no item</p>')
				$('#equipment .'+$('#item-details').data ('position')).unbind("click").click ({"where": $('#item-details').data ('position')}, handleEquipmentOnClick).empty().append (jq_p);
				
				// and now clear the item details.
				$('#item-details>.item-name').html ('Inventory');
				$('#item-details>.item-description').html ('Click on an item to see more data about it.');
				$('#item-details>.item-actions').empty();
			} else {
				alert ("An issue occured whilst trying to unequip:\n"+json['status_message']);
			}
		},
		'dataType': 'json',
		'type': 'GET'
	});
}