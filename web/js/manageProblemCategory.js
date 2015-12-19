var non_prod_mode = false;
var currency = "NONE";

function updateCurrency(index, currency_item)
{
    if(currency == "NONE")
    {
	currency = currency_item;
	$("label[for$='potype_totalAmount']").html("Total amount in "+currency);
	//alert("update currency");
    }
    else
    {
	if(currency != currency_item)
	{
	    alert("New item currency is not consistent with previous item\nPlease add item sold in "+currency);
	    $('#Po-item-'+index).attr("class", "form-group has-error");
	}
    }
}

function displayNumberItem(number)
{
    $('#number_item label').html('PO includes ' + number + ' following item(s):');
}

function computeTotalAmount()
{
    var sum = 0;
    $('[id*="totalPriceF"]').each(function(index, value){
	sum += Number($( this ).val());
    });
    sum = Math.round(sum * 100) / 100;
    $('[id$=potype_totalAmount]').val(sum);
}

function computeItemTotal(index)
{
    // get IDs of field to be populated
    var unitPrice = '[id$=potype_poItems_' + index + '_priceF]';
    var qty = '[id$=potype_poItems_' + index + '_qty]';
    var total = '[id$=potype_poItems_' + index + '_totalPriceF]';

    // calculate sum
    var sum = 0;
    sum = $(unitPrice).val() * $(qty).val();
    sum = Math.round(sum * 100) / 100;
    
    $(total).val(sum);
}

function populateByPn(index)
{
    // get IDs of field to be populated
    var unitPrice = '[id$=potype_poItems_' + index + '_priceF]';
    var qty = '[id$=potype_poItems_' + index + '_qty]';
    var total = '[id$=potype_poItems_' + index + '_totalPriceF]';
    var pn = '[id$=potype_poItems_' + index + '_pnF]';
    var custPn = '[id$=potype_poItems_' + index + '_custPnF]';
    var desc = '[id$=potype_poItems_' + index + '_description]';
    var rev = '[id$=potype_poItems_' + index + '_revisionF]';
    //var comment = '[id$=potype_poItems_' + index + '_comment]';
		var history = '[id$=potype_poItems_' + index + '_historyF]';
    
    // get info pertaining to P/N entry
    $.getJSON('../search/product/pn/'+$(pn).val()+'?return=json&active=true', function(data) {
	
	// populate information in fields
	$(pn).val(data.PN);
	$(custPn).val(data.SKPN);
	$(unitPrice).val(data.PRICE);
	$(desc).val(data.DESC);
	$(rev).val(data.REV);
	$(history).val(data.HISTORY);
	
	// check and update currency label
	updateCurrency(index, data.CURRENCY);
	
	// calculate total item
	$(total).val( $(unitPrice).val() * $(qty).val() );

	// auto populate latest rev
	// $.getJSON('../json/latestactiverev/'+$(pn).val(), function(data) {
	//     $(rev).val(data.REV);
	// });

	// manage special mode when Item is non-prod and does not match product in database
	// if ($(pn).val() == 'NoneUSD')
	var re = /^None[A-Z]{3}/;
	if(re.test($(pn).val()))
	{
	    //alert("Non Prod PO mode: Define a price");
	    $(unitPrice).removeAttr("readonly");
	    $(custPn).attr("readonly", "readonly");
	    $(rev).attr("readonly", "readonly");
	    $(ach_pomanagerbundle_potype_comment).val("THIS IS A NON PRODUCTION PO");
	    non_prod_mode = true;
	}
	else 
	{
	    if (non_prod_mode)
	    {
		$(unitPrice).attr("readonly", "readonly");
		$(custPn).removeAttr("readonly");
		$(rev).removeAttr("readonly");
		$(ach_pomanagerbundle_potype_comment).val("");
		non_prod_mode = false;
	    }
	}

	computeItemTotal(index);
	computeTotalAmount();
	
    });
}

function populateByCustPn(index)
{
    // get IDs of field to be populated
    var unitPrice = '[id$=potype_poItems_' + index + '_priceF]';
    var qty = '[id$=potype_poItems_' + index + '_qty';
    var total = '[id$=potype_poItems_' + index + '_totalPriceF]';
    var pn = '[id$=potype_poItems_' + index + '_pnF]';
    var custPn = '[id$=potype_poItems_' + index + '_custPnF]';
    var desc = '[id$=potype_poItems_' + index + '_description]';
    var rev = '[id$=potype_poItems_' + index + '_revisionF]';
		var history = '[id$=potype_poItems_' + index + '_historyF]';
    
    $.getJSON('../search/product/custpn/'+$(custPn).val()+'?return=json&active=true', function(data) {

	// populate information in fields
	$(pn).val(data.PN);
	$(custPn).val(data.SKPN);
	$(unitPrice).val(data.PRICE);
	$(desc).val(data.DESC);
	$(rev).val(data.REV);
	$(history).val(data.HISTORY);

	// check and update currency label
	updateCurrency(index, data.CURRENCY);
	
	// calculate total item
	$(total).val( $(unitPrice).val() * $(qty).val() );

	// auto populate latest rev
	// $.getJSON('../json/latestactiverev/'+$(pn).val(), function(data) {
	//     $(rev).val(data.REV);
	// });

	computeItemTotal(index);
	computeTotalAmount();

    });
}

function disableReleaseField()
{
	$('[id$=potype_relNum]').attr("readonly", "readonly");
	$('[id$=potype_relNum]').val("N/A");
}

function enableReleaseField()
{
	$('[id$=potype_relNum]').removeAttr("readonly");
	$('[id$=potype_relNum]').val("");
}


$(function() {


    $('div[id$=potype_poItems]').on('blur', '[id*="_pnF"]', function(event) {
	
	// get item number where event comes from
	var re = /\d+/ig;
	var result = re.exec(event.target.id);
	var index = result[0];
	populateByPn(index);
	
    });

    $('div[id$=potype_poItems]').on('blur', '[id*="_custPnF"]', function(event) {
	
	// get item number where event comes from
	var re = /\d+/ig;
	var result = re.exec(event.target.id);
	var index = result[0];
	populateByCustPn(index);
	
    });

    $('div[id$=potype_poItems]').on('click blur', '[id*="_qty"],[id*="_priceF"]', function(event) {
	
	// get item number where event comes from
	var re = /\d+/ig;
	var result = re.exec(event.target.id);
	var index = result[0];

	computeItemTotal(index);
	computeTotalAmount();

	
    });


    // when webpage launches, for each PO item
    $('[id*="Po-item"]').each(function(index){

	// id
	var unitPrice = '[id$=potype_poItems_' + index + '_priceF]';
	var qty = '[id$=potype_poItems_' + index + '_qty]';
	var total = '[id$=potype_poItems_' + index + '_totalPriceF]';
	var pn = '[id$=potype_poItems_' + index + '_pnF]';
	var custPn = '[id$=potype_poItems_' + index + '_custPnF]';
	var desc = '[id$=potype_poItems_' + index + '_description]';
	var rev = '[id$=potype_poItems_' + index + '_revisionF]';

	var currency_item;

	// calculate the total value for each item
	computeItemTotal(index);
	//$(total).val($(unitPrice).val() * $(qty).val());

	// check and update Currency
	$.getJSON('../search/product/custpn/'+$(custPn).val()+'?return=json&active=true', function(data) {
	    updateCurrency(index, data.CURRENCY);
	});



	// // get the proper item description from database
	// $.getJSON('../search/product/pn/'+$(pn).val()+'?return=json&active=true', function(data) {
	//     //$(custPn).val(data.SKPN);
	//     //$(unitPrice).val(data.PRICE);
	//     $(desc).val(data.DESC);
	//     //$(total).val( $(unitPrice).val() * $(qty).val() );
	//     //computeTotalAmount();
	// });

	// // when focusing off of qty field
	// $(qty).blur(function() {
	//     $(total).val( $(unitPrice).val() * $(qty).val() );
	//     computeTotalAmount();
	// });

	// // when focusing off of pn field
	// $(pn).blur(function(){
	//     $.getJSON('../search/product/pn/'+$(this).val()+'?return=json&active=true', function(data) {
	// 	$(custPn).val(data.SKPN);
	// 	$(unitPrice).val(data.PRICE);
	// 	$(desc).val(data.DESC);
	// 	$(total).val( $(unitPrice).val() * $(qty).val() );
	// 	computeTotalAmount();
	// 	$.getJSON('../json/latestactiverev/'+$(pn).val(), function(data) {
	// 	    $(rev).val(data.REV);
	// 	});
	//     });
	// });

	// // when focusing off of custPn
	// $(custPn).blur(function(){
	//     $.getJSON('../search/product/custpn/'+$(this).val()+'?return=json&active=true', function(data) {
	// 	$(pn).val(data.PN);
	// 	$(unitPrice).val(data.PRICE);
	// 	$(desc).val(data.DESC);
	// 	$(total).val( $(unitPrice).val() * $(qty).val() );
	// 	computeTotalAmount();
	// 	$.getJSON('../json/latestactiverev/'+$(pn).val(), function(data) {
	// 	    $(rev).val(data.REV);
	// 	});
	//     });
	// });
    });   
    computeTotalAmount();
	
	if($('[id$=potype_isBpo]').is(':checked') == false)
	{
		disableReleaseField();
	}
	
	$('[id$=potype_isBpo]').change(function() {
		if($('[id$=potype_isBpo]').is(':checked'))
		{
			enableReleaseField();
		}
		else
		{
			disableReleaseField();
		}
	});
	
});


$(document).ready(function() {
  // Get the <div> mark which contains the "data-prototype" of item
    var $container = $('div[id$=potype_poItems]');
    //var $container = $('div#items_section');
    //var $location = $('div#items_section');

  // create link to add new item
    var $lienAjout = $('<a href="#" id="ajout_categorie" class="btn btn-primary">Add new item</a>');
    //$container.append($lienAjout);
    $('div#items_section').append($lienAjout);

  // Add new poItem subform when clicking on lienAjout
    $lienAjout.click(function(e) {
	ajouterCategorie($container);
	e.preventDefault(); // prevent # from appearing in URL
	return false;
    });

  // dynamic counter
    //var index = $container.find(':input').length;
    var index = $container.find('[id*="Po-item-"]').length;
    var counter = index;

  // automatically add one item
    if (index == 0) {
	ajouterCategorie($container);
    } else {
    // for every item already there, adding a delete link
	$container.children('div').each(function() {
	    ajouterLienSuppression($(this));
	});
    }

  // La fonction qui ajoute un formulaire Categorie
    function ajouterCategorie($container) {
    // Dans le contenu de l'attribut data-prototype, on remplace :
    // - le texte "__name__label__" qu'il contient par le label du champ
    // - le texte "__name__" qu'il contient par le numero du champ
	var $prototype = $($container.attr('data-prototype').replace(/__name__label__/g, 'Item #' + (index+1))
                           .replace(/__name__/g, index));

    // On ajoute au prototype un lien pour pouvoir supprimer la categorie
	ajouterLienSuppression($prototype);

    // On ajoute le prototype modifie a la fin de la balise <div>
	$container.append($prototype);

    // increment counter
	index++;
	counter++;
	displayNumberItem(counter);
    }

  // La fonction qui ajoute un lien de suppression d'une categorie
    function ajouterLienSuppression($prototype) {
    // Creation du lien
	$lienSuppression = $('<a href="#" class="btn btn-danger">Delete</a>');

    // Ajout du lien
	$prototype.append($lienSuppression);

    // Ajout du listener sur le clic du lien
	$lienSuppression.click(function(e) {
	    $prototype.remove();
	    e.preventDefault(); // evite qu'un # apparaisse dans l'URL
	    computeTotalAmount();
	    counter--;
	    displayNumberItem(counter);
	    return false;
	});
    }
});