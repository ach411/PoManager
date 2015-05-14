$(function() {

	// enable sorting to the exception of first column
	$("#sortable").tablesorter({ headers: {0: {sorter: false}}});
	
	$('#shippingDate-id-global').datepicker({
		format: "yyyy-mm-dd",
		weekStart: 0,
		todayBtn: "linked",
		autoclose: true,
		todayHighlight: true
	});

	$("#ship-id-global").click(function(event) {
		//alert("click");
		var tracking = '#tracking-id-global';
		var carrier = '#carrier-id-global option:selected';
		var shippingDate = '#shippingDate-id-global';
		var first = true;
		var parameter_string = "?";
		
		$('input[type=checkbox]').each(function () {
			if(this.checked)
			{
				var index = $(this).val();
				
				if(first)
				{
					first = false;
				}
				else
				{
					parameter_string += "&";
				}
				
				parameter_string = parameter_string + "poItem_" + index + "=" + index;
				
				var select_val = $("select[name='qty_id_"+ index +"'] option:selected").text();
				var original_val = $("#origin-qty-id-"+index).val();
				
				parameter_string = parameter_string + "&qty_id_" + index + "=" + select_val;
				
				//var rest=$("#origin-qty-id-"+index).val() - $("select[name='qty_id_"+ index +"'] option:selected").text();
				var rest= original_val - select_val;
				
				if(rest == 0)
				{
					var row = '#row-id-' + index;
					$(row).remove();
				}
				else
				{
					for(i = rest+1; i <= original_val; i++)
					{
						$("select[name='qty_id_"+ index +"'] option[value='" + i + "']").remove();
					}
					$("#origin-qty-id-"+index).val(rest);
					$("select[name='qty_id_"+ index +"']").val(rest);
				}
			}
		});
		
		//if at least one box was checked, send the ajax request
		if(first == false)
		{
			//alert("../tracking/"+$(tracking).val()+parameter_string);
			//prompt("blbla","/tracking/"+$(tracking).val()+"/"+$(carrier).val()+parameter_string);
			var tracking_num = $(tracking).val();
			if(tracking_num == null || tracking_num == "")
			{
				tracking_num = "none";
			}
			
			$.get("../tracking/"+$(shippingDate).val()+"/"+$(carrier).val()+"/"+tracking_num+parameter_string, function(data) {
				$("#sortable").trigger("update"); 
				alert(data);
			});
		}
		// if no box was checked, send alert
		else
		{
			alert("No item selected!");
		}
		
	});
	
	//date picker on the date input
	$('[id*="date-id-"]').click(function(event) {
		//alert("click the date!");
		// get item number where event comes from
		var re = /\d+/ig;
		var result = re.exec(event.target.id);
		var index = result[0];
		// check box selector
		var selectBox = 'excel_recap_' + index;
		//we get the class attributes of the date item. All the checkbox for the same date have the same class attribute...
		var classDate = this.getAttribute("class");
		//alert(classDate);
		// $("input."+classDate+":checkbox").attr('checked', true);
		// $("input[name="+selectBox+"]").attr('checked', true);
		$("input."+classDate+":checkbox").prop('checked', !($("input[name="+selectBox+"]").prop("checked")));
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