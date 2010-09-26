;(function($){
/**
 * jqGrid English Translation
 * Tony Tomov tony@trirand.com
 * http://trirand.com/blog/ 
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
**/
$.jgrid = {};

$.jgrid.defaults = {
	recordtext: "Ligne(s)",
	loadtext: "Chargement...",
	pgtext : "/"
};
$.jgrid.search = {
    caption: "Recherche...",
    Find: "Chercher",
    Reset: "Annuler",
    odata : ['égal', 'différent', 'inférieur', 'inférieur ou égal','supérieur','supérieur ou égal', 'débute par','termine par','contient']
};
$.jgrid.edit = {
    addCaption: "Ajouter",
    editCaption: "Editer",
    bSubmit: "Valider",
    bCancel: "Annuler",
	bClose: "Fermer",
    processData: "Traitement...",
    msg: {
        required:"Champ obligatoire",
        number:"Saisissez un nombre valide",
        minValue:"La valeur doit être supérieure ou égal à 0 ",
        maxValue:"La valeur doit être inférieure ou égal à 0",
        email: "n'est pas un email valide",
        integer: "Saisissez un entier valide",
		date: "Saisissez une date valide"
    }
};
$.jgrid.del = {
    caption: "Supprimer",
    msg: "Supprimer les enregistrements sélectionnés ?",
    bSubmit: "Supprimer",
    bCancel: "Annuler",
    processData: "Traitement..."
};
$.jgrid.nav = {
	edittext: " ",
    edittitle: "Editer la ligne sélectionnée",
	addtext:" ",
    addtitle: "Ajouter une ligne",
    deltext: " ",
    deltitle: "Supprimer la ligne sélectionnée",
    searchtext: " ",
    searchtitle: "Chercher un enregistrement",
    refreshtext: "",
    refreshtitle: "Recharger le tableau",
    alertcap: "Avertissement",
    alerttext: "Veuillez sélectionner une ligne"
};
// setcolumns module
$.jgrid.col ={
    caption: "Afficher/Masquer les colonnes",
    bSubmit: "Valider",
    bCancel: "Annuler"	
};
$.jgrid.errors = {
	errcap : "Erreur",
	nourl : "Aucune url paramétrée",
	norecords: "Aucun enregistrement à traiter",
    model : "Length of colNames <> colModel!"
};
$.jgrid.formatter = {
	integer : {thousandsSeparator: " ", defaulValue: 0},
	number : {decimalSeparator:".", thousandsSeparator: " ", decimalPlaces: 2, defaulValue: 0},
	currency : {decimalSeparator:".", thousandsSeparator: " ", decimalPlaces: 2, prefix: "", suffix:"", defaulValue: 0},
	date : {
		dayNames:   [
			"Sun", "Mon", "Tue", "Wed", "Thr", "Fri", "Sat",
			"Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
		],
		monthNames: [
			"Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
			"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
		],
		AmPm : ["am","pm","AM","PM"],
		S: function (j) {return j < 11 || j > 13 ? ['st', 'nd', 'rd', 'th'][Math.min((j - 1) % 10, 3)] : 'th'},
		srcformat: 'Y-m-d',
		newformat: 'd/m/Y',
		masks : {
            ISO8601Long:"Y-m-d H:i:s",
            ISO8601Short:"Y-m-d",
            ShortDate: "n/j/Y",
            LongDate: "l, F d, Y",
            FullDateTime: "l, F d, Y g:i:s A",
            MonthDay: "F d",
            ShortTime: "g:i A",
            LongTime: "g:i:s A",
            SortableDateTime: "Y-m-d\\TH:i:s",
            UniversalSortableDateTime: "Y-m-d H:i:sO",
            YearMonth: "F, Y"
        },
        reformatAfterEdit : false
	},
	baseLinkUrl: '',
	showAction: 'show'
};
})(jQuery);
