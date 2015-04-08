$(function() {

    $('[id*="approval"]').click(function(event) {

	// get item number where event comes from
	var re = /\d+/ig;
	var result = re.exec(event.target.id);
	var index = result[0];
	var po = '#po-id-' + index;
	var rel = '#rel-id-' + index;
	var line = '#line-id-' + index;
	var pn =  '#pn-id-' + index;
	var qty = '#qty-id-' + index;
	var row = '#row-id-' + index;
	var date = '#date-id-' + index;
	
	if(confirm("You're about to confirm PO # "+$(po).html()+" release # "+$(rel).html()+" on line # "+$(line).html()+" for "+$(qty).html()+" unit(s) of "+$(pn).html()+" delivered to the customer on "+$(date).html()) == true)
	{
	    $.get("../../../approve/poItem/"+index, function() {
		$(row).remove();
	    });
	}

    });
});