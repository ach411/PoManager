$(function() {

	// enable sorting to the exception of first column
	$("#sortable").tablesorter({ headers: {0: {sorter: false}}});
	
	//date picker on the date input
	$('#invoiceDate-id-global').datepicker({
		format: "yyyy-mm-dd",
		weekStart: 0,
		todayBtn: "linked",
		autoclose: true,
		todayHighlight: true
	});
	
	$('#form').submit(function() {
		var currency = "";
		var noBoxChecked = true;
		$('input[type=checkbox]').each( function () {
			if(this.checked)
			{
				var index = $(this).val();
				
				if(noBoxChecked)
				{
					currency = $('#currency-id-'+index).html();
					noBoxChecked = false;
				}
				else
				{
					if(currency != $('#currency-id-'+index).html())
					{
						currency = "MIX";
					}
				}
				
			}
		});
		if(noBoxChecked)
		{
			alert("Please select at least one item");
			return false;
		}
		
		if(currency == "MIX")
		{
			alert('At least 2 items have different currency: impossible to invoice');
			return false;
		}
		
		var fileName = $("#invoiceFile").val();
		if(fileName.lastIndexOf("pdf")!==fileName.length-3)
		{
			alert("Please upload PDF file as invoice");
			return false;
		}
		
	});
	
	$('[id*="tracking-id-"]').click(function(event) {
		//alert("click the date!");
		// get item number where event comes from
		var re = /\d+/ig;
		var result = re.exec(event.target.id);
		var index = result[0];
		// check box selector
		var selectBox = 'shipmentItem_' + index;
		//we get the class attributes of the date item. All the checkbox for the same date have the same class attribute...
		var classDate = this.getAttribute("class");
		//alert(classDate);
		// $("input."+classDate+":checkbox").attr('checked', true);
		// $("input[name="+selectBox+"]").attr('checked', true);
		$("input."+classDate+":checkbox").prop('checked', !($("input[name="+selectBox+"]").prop("checked")));
	});
	
	$("#bill-id-global").click(function(event) {
		//alert("click");
		var invoice = '#invoice-id-global';
		var first = true;
		var parameter_string = "?";
		
		if($('#invoice-id-global').val() == "")
		{
			alert('Invoice number missing');
		}
		else
		{
			if($('#invoiceDate-id-global').val() == "")
			{
				alert('Invoice date missing');
			}
			else
			{
				parameter_string = "?date=" + $('#invoiceDate-id-global').val();
				$('input[type=checkbox]').each(function () {
					if(this.checked)
					{
						var index = $(this).val();
						
						if(first)
						{
							first = false;
						}
						
						parameter_string = parameter_string + "&poItem_" + index + "=" + index;
						
						var row = '#row-id-' + index;
						$(row).remove();
					}
				});
				
				//if at least one box was checked, send the ajax request
				if(first == false)
				{
					$.get("../../../create/invoice/"+$(invoice).val()+parameter_string, function(data) {
						$("#sortable").trigger("update"); 
						alert(data);
					});
				}
				// if no box was checked, send alert
				else
				{
					alert("No item selected!");
				}
			}
		}
	});
});