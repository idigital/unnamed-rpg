$(function () {
	$('#blocks>li>img').click (selectBlock);
});

function selectBlock () {
	$('#blocks>li>img').removeClass ('selected');
	$(this).addClass ('selected');
}