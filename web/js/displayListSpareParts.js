$(function() {
	
	//update comment
	$('[id*="update-comment-id"]').click(function(event) {
		// get item number where event comes from
		var re = /\d+/ig;
		var result = re.exec(event.target.id);
		var index = result[0];
		//var text = $('[id*="comment-text-id"]').val();
		var text = $('#comment-text-id-'+index).val();
		$.post("../../update/product/"+index, { comment: text});
		alert("Comment updated: "+text);
		
	});
	
});
