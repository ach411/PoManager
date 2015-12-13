$(function() {
    $('form').submit(function (e) {

	e.preventDefault();
	e.returnValue = false;
	
	// Get the serial number value and trim it
	var sn = $('#ach_pomanagerbundle_rmatype_serialNumF').val();
	
	// Check if polite or not
	if (sn  === 'merde') {
            alert('Pas de gros mots!');
            return false;
	}
	else
	{
//	    alert(sn);

	    var $form = $(this);

            // this is the important part. you want to submit
            // the form but only after the ajax call is completed
            $.ajax({ 
		type: 'get',
		url: '../search/sn/number/'+sn+'?return=json&match=exact', 
		context: $form, // context will be "this" in your handlers
		success: function(result) { // your success handler
		    //alert('json get request successfull: ');
		    if(jQuery.isEmptyObject(result))
		    {
			alert('S/N entry does not match any recorded units. Make sure you enter complete serial number such as \'IP1-SYS D1515050\'');
		    }
		    else
		    {
			// alert('il y a quelque chose');
			// make sure that you are no longer handling the submit event; clear handler
			this.off('submit');
			// actually submit the form
			this.submit();
		    }
		},
		error: function() { // your error handler
		    alert('Error searching database for S/N');
		},
		complete: function() {
		}
            });
	    // 	/*$.each( data, function( key, val ) {
	    // 	    text += key + ' - ' + val ;
	    // 	    });*/
	}
    });
});
