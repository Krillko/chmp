/**
 * Created by kristoffer on 2014-04-15.
 */

$(document).ready(function () {
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - expandable buttons
	// TODO: this code sucks, really
	$(document).on('click', '.chmp-do-expand', function () {
		$(this).toggleClass('chmp-input-expanded1').parent().children('.chmp-input-expand').toggleClass('chmp-input-expanded2');

	});

	$(document).on('click', '.chmp-input-cancel, .chmp-input-confirm', function () {
		$(this).parent().parent().toggleClass('chmp-input-expanded2').parent().children('.chmp-do-expand').toggleClass('chmp-input-expanded1');

	});

	$(document).on('click', '#chmp-nav-showhide', function () {
		$(this).toggleClass('chmp-nav-shadow').children('p').toggleClass('chmp-ico-uparrow');
		$("#chmp-nav").toggleClass('chmp-nav-float-hide');

	});

});