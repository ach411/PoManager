$(function() {
	
	// enable sorting to the exception of first column
	$("#sortable").tablesorter({ headers: {0: {sorter: false}}});
	
	// handle approval
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
		
		if(confirm("Confirm item on PO # "+$(po).html()+" release # "+$(rel).html()+" line # "+$(line).html()+" for "+$(qty).html()+" unit(s) of "+$(pn).html()+" delivered to the customer on "+$(date).html()) == true)
		{
			$.get("../approve/"+index, function() {
				$(row).remove();
				$("#sortable").trigger("update"); 
			});
		}
	
	});
	
	// handle rejection
	$('[id*="rejection"]').click(function(event) {
		
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
		
		if(reason = prompt("Reject item? on PO # "+$(po).html()+" release # "+$(rel).html()+" line # "+$(line).html()+" for "+$(qty).html()+" unit(s) of "+$(pn).html()+" delivered to the customer on ",'please enter comment'))
		{
			//alert(s);
			$.get("../reject/"+index+"?info="+reason, function() {
			//$.post("../reject/"+index, reason, function() {
				alert(reason);
				$(row).remove();
				$("#sortable").trigger("update"); 
				});
		}
	});
	
	//update comment
	$('[id*="update-comment-id"]').click(function(event) {
		// get item number where event comes from
		var re = /\d+/ig;
		var result = re.exec(event.target.id);
		var index = result[0];
		//var text = $('[id*="comment-text-id"]').val();
		var text = $('#comment-text-id-'+index).val();
		$.post("../../update/poitem/"+index, { comment: text});
		alert("Comment updated: "+text);
		
	});
	
});