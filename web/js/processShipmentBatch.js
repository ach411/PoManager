$(function() {
    var unitsPerLot = parseInt($('#unitsPerLot').html());
    var total = 0;
    var available = 0;
    $('[id*="check-removal-"]').each(function(){
	available += unitsPerLot;
	if($(this).hasClass("glyphicon-ok"))
	    total += unitsPerLot;
    });
    $('#totalUnitsSelected').html(total);
    $('#totalUnitsAvailable').html(available);
    
    $('[id*="cell-removal-"]').click(function(event) {

	var re = /\d+/ig;
	var result = re.exec(event.target.id);
	var index = result[0];

	if($('#check-removal-'+index).hasClass("glyphicon-remove"))
	{
	    $.get("../select/shipmentbatch/id/"+index, function() {
		//$("#sortable").trigger("update");
		//alert("Selected: "+index);
		$('#check-removal-'+index).toggleClass("glyphicon-remove alert-danger");
		$('#check-removal-'+index).toggleClass("glyphicon-ok alert-success");
		total += unitsPerLot;
		$('#totalUnitsSelected').html(total);
	    });
	}

	if($('#check-removal-'+index).hasClass("glyphicon-ok"))
	{
	    $.get("../unselect/shipmentbatch/id/"+index, function() {
		//$("#sortable").trigger("update");
		//alert("Selected: "+index);
		$('#check-removal-'+index).toggleClass("glyphicon-remove alert-danger");
		$('#check-removal-'+index).toggleClass("glyphicon-ok alert-success");
		total -= unitsPerLot;
		$('#totalUnitsSelected').html(total);
	    });
	}
	
	
    });
    
});
