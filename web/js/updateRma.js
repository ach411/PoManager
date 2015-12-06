function displayNumberItem(number)
{
    $('#number_replacement label').html(number + ' part swap(s) have been made during repair:');
}

$(document).ready(function() {
  // Get the <div> mark which contains the "data-prototype" of item
    var $container = $('div[id$=rmaupdatetype_partReplacements]');
    //var $container = $('div#items_section');
    //var $location = $('div#items_section');

  // create link to add new item
    var $lienAjout = $('<a href="#" id="ajout_categorie" class="btn btn-primary">Add new part swap</a>');
    //$container.append($lienAjout);
    $('div#replacement_section').append($lienAjout);

  // Add new poItem subform when clicking on lienAjout
    $lienAjout.click(function(e) {
	ajouterCategorie($container);
	e.preventDefault(); // prevent # from appearing in URL
	return false;
    });

  // dynamic counter
    //var index = $container.find(':input').length;
    var index = $container.find('[id*="Replacement-item-"]').length;
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
//	    computeTotalAmount();
	    counter--;
	    displayNumberItem(counter);
	    return false;
	});
    }
});
