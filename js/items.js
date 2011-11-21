$(function () {
	$('#items-list>li').click (handleItemOnClick);
});

function handleItemOnClick () {
	item_id = $(this).attr ('id').slice (6);
	
	$.get (relroot+'/items/load-details.php', { 'item_id': item_id }, function (data) {
		$('#item-details>.item-name').html (data['name']);
		$('#item-details>.item-description').html (data['description']);
	}, 'json');
}