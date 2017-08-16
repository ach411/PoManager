function updateCount()
{
    //var unitsPerLot = parseInt($('#unitsPerLot').html());
    var total = 0;
    var available = 0;
    $('[id*="check-removal-"]').each(function(){
	var re = /\d+/ig;
	var result = re.exec($(this).attr('id'));
	var index = result[0];
	//available += unitsPerLot;
	available += parseInt($('#snCount-'+index).html());
	if($(this).hasClass("glyphicon-ok"))
	{
	    //total += unitsPerLot;
	    total += parseInt($('#snCount-'+index).html());
	    $('#recap').append($('#lot-num-'+index).html());
	    $('#recap').append("\n");
	}
    });
    $('#totalUnitsSelected').html(total);
    $('#totalUnitsAvailable').html(available);
}


$(function() {

    updateCount();
    
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
		//total += unitsPerLot;
		//$('#totalUnitsSelected').html(total);
		$('#recap').html('');
		updateCount();
	    });
	}

	if($('#check-removal-'+index).hasClass("glyphicon-ok"))
	{
	    $.get("../unselect/shipmentbatch/id/"+index, function() {
		//$("#sortable").trigger("update");
		//alert("Selected: "+index);
		$('#check-removal-'+index).toggleClass("glyphicon-remove alert-danger");
		$('#check-removal-'+index).toggleClass("glyphicon-ok alert-success");
		//total -= unitsPerLot;
		//$('#totalUnitsSelected').html(total);
		$('#recap').html('');
		updateCount();
	    });
	}
	
	
    });
    
});
