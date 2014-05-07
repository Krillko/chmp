/**
 * This is the scripts thats control the edit mode
 * It should only be load if logged in
 * TODO: Separate ever further, with one script for logged in, and another for EDIT = ON
 */


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - setting some values
chmp.is_saving = false; // if we are currently trying to send data from page
chmp.is_on_timer = false; // if we are waiting to send

/*
 * This is zenpen editor from zenpen.io
 * by Tim Holman (@twholman)
 *
 * It has been edited in several ways to fit chmp.
 * */
var chmp_zen_editor = (function () {

	// Editor elements
	var headerField, contentField, cleanSlate, lastType, currentNodeList, savedSelection;

	// Editor Bubble elements
	var textOptions, optionsBox, boldButton, italicButton, quoteButton, urlButton, urlInput;

	// added
	var is_editable;

	var composing;

	function init() {

		composing = false;
		bindElements();

		// Set cursor position
		var range = document.createRange();
		var selection = window.getSelection();
		//range.setStart(headerField, 1);
		selection.removeAllRanges();
		selection.addRange(range);

		createEventBindings();

		// Load state if storage is supported
		//if ( supportsHtmlStorage() ) {
		//	loadState();
		//}
	}

	function createEventBindings() {

		// local storage connection removed
		/* Key up bindings
		 if ( supportsHtmlStorage() ) {

		 document.onkeyup = function( event ) {
		 checkTextHighlighting( event );
		 saveState();
		 }

		 } else {
		 document.onkeyup = checkTextHighlighting;
		 }
		 */

		document.onkeyup = checkTextHighlighting;

		// Mouse bindings
		document.onmousedown = checkTextHighlighting;
		document.onmouseup = function (event) {

			setTimeout(function () {
				checkTextHighlighting(event);
			}, 1);
		};

		// Window bindings
		window.addEventListener('resize', function (event) {
			updateBubblePosition();
		});

		// Scroll bindings. We limit the events, to free the ui
		// thread and prevent stuttering. See:
		// http://ejohn.org/blog/learning-from-twitter
		var scrollEnabled = true;
		document.body.addEventListener('scroll', function () {

			if ( !scrollEnabled ) {
				return;
			}

			scrollEnabled = true;

			updateBubblePosition();

			return setTimeout((function () {
				scrollEnabled = true;
			}), 250);
		});

		// Composition bindings. We need them to distinguish
		// IME composition from text selection
		document.addEventListener('compositionstart', onCompositionStart);
		document.addEventListener('compositionend', onCompositionEnd);
	}

	function bindElements() {

		//headerField = document.querySelector( '.header' );
		contentField = document.querySelector('.content');

		textOptions = document.querySelector('#chmp_text_options');

		optionsBox = textOptions.querySelector('.chmp_zen_options');

		boldButton = textOptions.querySelector('.chmp_zen_bold');
		boldButton.onclick = onBoldClick;

		italicButton = textOptions.querySelector('.chmp_zen_italic');
		italicButton.onclick = onItalicClick;

		quoteButton = textOptions.querySelector('.chmp_zen_quote');
		quoteButton.onclick = onQuoteClick;

		urlButton = textOptions.querySelector('.chmp_zen_url');
		urlButton.onmousedown = onUrlClick;

		urlInput = textOptions.querySelector('.chmp_zen_url-input');
		urlInput.onblur = onUrlInputBlur;
		urlInput.onkeydown = onUrlInputKeyDown;
	}

	function checkTextHighlighting(event) {

		var selection = window.getSelection();

		if (
			event.target.className === "chmp_zen_url-input" ||
				event.target.classList.contains("chmp_zen_url") ||

				( typeof event.target.parentNode.classList !== 'undefined' && event.target.parentNode.classList.contains("chmp_zen_ui-inputs"))


			) {

			currentNodeList = findNodes(selection.focusNode);
			updateBubbleStates();
			return;
		}

		// Check selections exist
		if ( selection.isCollapsed === true && lastType === false ) {

			onSelectorBlur();
		}

		// Text is selected
		if ( selection.isCollapsed === false && composing === false ) {

			currentNodeList = findNodes(selection.focusNode);


			// finds out if any parent is contenteditable, since it's inheirted
			is_editable = false;

			$(selection.focusNode).parents().map(function () {
				if ( $(this).is("[contenteditable='true']") ) {
					is_editable = true;

				}
			}).get();


			// Find if highlighting is in the editable area
			if ( is_editable ) {
				updateBubbleStates();
				updateBubblePosition();

				// Show the ui bubble
				textOptions.className = "chmp_zen_text-options active";

			}


		}

		lastType = selection.isCollapsed;
	}

	function updateBubblePosition() {
		var selection = window.getSelection();
		var range = selection.getRangeAt(0);
		var boundary = range.getBoundingClientRect();

		textOptions.style.top = boundary.top - 5 + window.pageYOffset + "px";
		textOptions.style.left = (boundary.left + boundary.right) / 2 + "px";
	}

	function updateBubbleStates() {

		// It would be possible to use classList here, but I feel that the
		// browser support isn't quite there, and this functionality doesn't
		// warrent a shim.

		if ( hasNode(currentNodeList, 'B') ) {
			boldButton.className = "chmp_zen_bold active"
		} else {
			boldButton.className = "chmp_zen_bold"
		}

		if ( hasNode(currentNodeList, 'I') ) {
			italicButton.className = "chmp_zen_italic active"
		} else {
			italicButton.className = "chmp_zen_italic"
		}

		if ( hasNode(currentNodeList, 'BLOCKQUOTE') ) {
			quoteButton.className = "chmp_zen_quote active"
		} else {
			quoteButton.className = "chmp_zen_quote"
		}

		if ( hasNode(currentNodeList, 'A') ) {
			urlButton.className = "chmp_zen_url chmp_zen_useicons active"
		} else {
			urlButton.className = "chmp_zen_url chmp_zen_useicons"
		}
	}

	function onSelectorBlur() {

		textOptions.className = "chmp_zen_text-options fade";
		setTimeout(function () {

			if ( textOptions.className == "chmp_zen_text-options fade" ) {

				textOptions.className = "chmp_zen_text-options";
				textOptions.style.top = '-999px';
				textOptions.style.left = '-999px';
			}
		}, 260)
	}

	function findNodes(element) {

		var nodeNames = {};

		while (element.parentNode) {

			nodeNames[element.nodeName] = true;
			element = element.parentNode;

			if ( element.nodeName === 'A' ) {
				nodeNames.url = element.href;
			}
		}

		return nodeNames;
	}

	function hasNode(nodeList, name) {

		return !!nodeList[ name ];
	}

	function saveState(event) {

		localStorage[ 'header' ] = headerField.innerHTML;
		localStorage[ 'content' ] = contentField.innerHTML;
	}

	/*
	 function loadState() {

	 if ( localStorage[ 'header' ] ) {
	 headerField.innerHTML = localStorage[ 'header' ];
	 }

	 if ( localStorage[ 'content' ] ) {
	 contentField.innerHTML = localStorage[ 'content' ];
	 }
	 }
	 */

	function onBoldClick() {
		document.execCommand('bold', false);
		chmp.autosave_start(false);
	}

	function onItalicClick() {
		document.execCommand('italic', false);
		chmp.autosave_start(false);
	}

	function onQuoteClick() {

		var nodeNames = findNodes(window.getSelection().focusNode);

		if ( hasNode(nodeNames, 'BLOCKQUOTE') ) {
			document.execCommand('formatBlock', false, 'p');
			document.execCommand('outdent');
		} else {
			document.execCommand('formatBlock', false, 'blockquote');
		}

		chmp.autosave_start(false);

	}

	function onUrlClick() {

		if ( optionsBox.className == 'chmp_zen_options' ) {

			optionsBox.className = 'chmp_zen_options chmp_zen_url-mode';

			// Set timeout here to debounce the focus action
			setTimeout(function () {

				var nodeNames = findNodes(window.getSelection().focusNode);

				if ( hasNode(nodeNames, "A") ) {
					urlInput.value = nodeNames.url;

					//console.log(urlInput.value);

				} else {
					// Symbolize text turning into a link, which is temporary, and will never be seen.
					document.execCommand('createLink', false, '/');

					//console.log("test");

				}

				// Since typing in the input box kills the highlighted text we need
				// to save this selection, to add the url link if it is provided.
				lastSelection = window.getSelection().getRangeAt(0);
				lastType = false;

				urlInput.focus();

			}, 100);

		} else {

			optionsBox.className = 'chmp_zen_options';
		}
	}

	function onUrlInputKeyDown(event) {

		if ( event.keyCode === 13 ) {
			event.preventDefault();
			applyURL(urlInput.value);
			urlInput.blur();
		}
	}

	function onUrlInputBlur(event) {

		optionsBox.className = 'chmp_zen_options';
		applyURL(urlInput.value);
		urlInput.value = '';

		currentNodeList = findNodes(window.getSelection().focusNode);
		updateBubbleStates();

		chmp.autosave_start(false);

	}

	function applyURL(url) {

		rehighlightLastSelection();

		// Unlink any current links
		document.execCommand('unlink', false);

		if ( url !== "" ) {

			// Insert HTTP if it doesn't exist.
			if ( !url.match("^(http|https)://") ) {

				url = "http://" + url;
			}

			document.execCommand('createLink', false, url);
		}
	}

	function rehighlightLastSelection() {

		window.getSelection().addRange(lastSelection);
	}

	function getWordCount() {
		console.log("wordcount");
		var text = get_text(contentField);

		if ( text === "" ) {
			return 0
		} else {
			return text.split(/\s+/).length;
		}
	}

	function onCompositionStart(event) {
		composing = true;
	}

	function onCompositionEnd(event) {
		composing = false;
	}

	return {
		init:         init,
		saveState:    saveState,
		getWordCount: getWordCount
	}

})();
//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - end zenpen editor


/**
 *  set savetimer
 *  To prevent too many saves, we set a 1 sec delay before saving
 * @param {bool} [publish=false]
 */
chmp.autosave_start = function (publish) {
	publish = publish || false;


	if ( typeof chmp.autosavetimer !== 'undefined' ) {
		clearTimeout(chmp.autosavetimer);
	}
	chmp.is_on_timer = true;
	chmp.autosavetimer = setTimeout(function () {
		chmp.read_dom(true, publish)
	}, 1000);
};

/**
 * Saving
 * @param {bool} [publish=false]
 */
chmp.autosave = function (publish) {
	publish = publish || false;
	chmp.read_dom(true, false);
};


/**
 * reads the chmp elements, creates a json that is identical to the one we save
 *
 * please note, module_uid is unique in template, since you can have more then one module of the same design, it's not unique in content json
 * modules in content doesn't have a unique id, it's just an array
 *
 * @param {bool} [send_save=false]
 * @param {bool} [publish=false]
 */
chmp.read_dom = function (send_save, publish) {
	publish = publish || false;
	send_save = send_save || false;

	if ( publish ) {
		send_save = true;
	}

	// check if we already are trying to save, and wait to start again
	if ( chmp.is_saving ) {

		chmp.autosave_start(publish);

	} else {

		chmp.is_saving = true;

		if ( send_save ) {
			$("#chmp-save-animation").stop().show();
		}

		var json = {},
			contentarea_attr, contentarea_uid,
			module_attr, module_uid,
			element_attr,
			module_array_id = 0;

		json.info = chmp.pageinfo;

		json.content = {};

		// - - - - - - - - - - - - - - - finds the content areas
		$('.chmp-edit-contentarea').each(function () {

			// note, jquery attr() is extended
			contentarea_attr = $(this).attr();

			contentarea_uid = contentarea_attr['data-chmp-uid'];

			json.content[contentarea_uid] = {};


			// - - - - - - - - - - - - - - -  finds the modules
			$(this).find(".chmp-edit-module").each(function () {

				//console.log($(this));

				module_attr = $(this).attr();

				/*
				 // finds attributes
				 $(this).each(function() {
				 $.each(this.attributes, function() {
				 if(this.specified) {
				 if (typeof this.name !== 'undefined' && typeof this.value !== 'undefined') {
				 //console.log(this);
				 //console.log(this.name);
				 module_attr[this.name] =  this.value;
				 } else {
				 console.warn("felet:");
				 console.log(this);

				 }
				 }
				 });
				 });
				 */

				module_uid = module_attr['data-chmp-uid'];

				json.content[contentarea_uid].modules = json.content[contentarea_uid].modules || {};

				json.content[contentarea_uid].modules[module_array_id] = {
					'uid': module_uid
				};

				//  - - - - - - - - - - - - - - - finds the texts and images

				//console.log($(this));

				$(this).find("*[data-chmp-name]").each(function () {


					element_attr = $(this).attr();


					if ( this.tagName == 'IMG' ) {

						json.content[contentarea_uid].modules[module_array_id].img = json.content[contentarea_uid].modules[module_array_id].img || {};
						json.content[contentarea_uid].modules[module_array_id].img[element_attr['data-chmp-name']] = {};
						json.content[contentarea_uid].modules[module_array_id].img[element_attr['data-chmp-name']].src = chmp.get_filename(element_attr.src);


						json.content[contentarea_uid].modules[module_array_id].img[element_attr['data-chmp-name']].orgImgId = element_attr['data-chmp-orgimgid'];
						json.content[contentarea_uid].modules[module_array_id].img[element_attr['data-chmp-name']].name = element_attr['data-chmp-name'];
						json.content[contentarea_uid].modules[module_array_id].img[element_attr['data-chmp-name']].width = element_attr.width;
						json.content[contentarea_uid].modules[module_array_id].img[element_attr['data-chmp-name']].height = element_attr.height;
						json.content[contentarea_uid].modules[module_array_id].img[element_attr['data-chmp-name']].alt = element_attr.alt;


					} else if ( chmp.chmp_cnf_texts.indexOf(this.tagName.toLowerCase()) > -1 ) { // reads texts
						json.content[contentarea_uid].modules[module_array_id].text = json.content[contentarea_uid].modules[module_array_id].text || {};
						json.content[contentarea_uid].modules[module_array_id].text[element_attr['data-chmp-name']] = chmp.remove_empty_chr($(this).html());
					}


				});
				module_array_id++;
			});

		});

		// - - - - - - - - - - - - - - - finds stuff outside the contentarea
		json.content.ext = {};
		json.content.ext.text = {};

		$("*[data-chmp-ext]").each(function () {

			element_attr = $(this).attr();

			// finds text elements
			if ( chmp.chmp_cnf_texts.indexOf(this.tagName.toLowerCase()) > -1 ) {
				json.content.ext.text[element_attr['data-chmp-name']] = chmp.remove_empty_chr($(this).html());
			}
		});

		// sending
		if ( send_save ) {
			$.ajax({
				       type:  "POST",
				       url:   chmp.path + 'chmp/ajax_savepage.php',
				       data:  json,
				       cache: false
			       })
				.done(function (data) {
					      $("#chmp-save-animation").fadeOut(1000);
					      chmp.is_saving = false;
					      chmp.is_on_timer = false;

					      if ( publish ) {

						      window.location.href = 'index.php?page=' + chmp.pageinfo.page_id + '&chmp-edit=0&do=publish&rand=' + Math.random();

					      }

				      })
				.fail(function (jqXHR, textStatus, e) {
					      console.error("fail");
					      console.groupCollapsed("Error report");
					      console.log(jqXHR);
					      console.log(textStatus);
					      console.log(e);
					      console.groupEnd();
					      chmp.ajax_errorhandler(jqXHR.status);
					      $("#chmp-save-animation").fadeOut(1000);
					      chmp.is_saving = false;
					      chmp.is_on_timer = false;
				      })
			;

		} else {
			// do something else here
			// TODO: this option is for future functions where we want to read the DOM without saving
			console.log(json);
			chmp.is_saving = false;
			chmp.is_on_timer = false;
		}
	}
};


/**
 * Replace the empty chr placeholder when saving
 * @param {string} input
 * @returns {string}
 */
chmp.remove_empty_chr = function (input) {
	var output = input.replace(String.fromCharCode(10002), '');
	return output;
};


/**
 * Gets last part from url
 * @param input
 * @returns {string}
 */
chmp.get_filename = function (input) {
	return (input.substr(input.lastIndexOf('/') + 1));
};

/**
 * listens to the image editor
 * @param image_tuid
 * @param original_img_id
 * @param new_img_id
 * @param output_w
 * @param output_h
 */
chmp.change_img = function (image_tuid, original_img_id, new_img_id, output_w, output_h) {
	$("img[data-chmp-tuid = '" + image_tuid + "']")
		.attr('src', 'chmp/assets/images/' + new_img_id)
		.attr('width', output_w)
		.attr('height', output_h)
		.attr('data-chmp-orgimgid', original_img_id);

	// close lightbox
	$('.chmp-featherlight-close').trigger('click');

	// saves
	chmp.read_dom(true, false);
};

/**
 * Shows an error message to user
 * @param errorno
 */
chmp.ajax_errorhandler = function (errorno) {
	switch (errorno) {
		case 401:
			alert('An error occured, because you are not logged in');
			break;
	}

};


/**
 * Adds a new module to a content area
 * @param content_uid
 * @param module_uid
 */
chmp.add_new_module = function (content_uid, module_uid) {
	var data = {
		'templatefile': chmp.pageinfo.templatefile,
		'content_uid':  content_uid,
		'module_uid':   module_uid
	};

	$.ajax({
		       type:  "POST",
		       url:   chmp.path + 'chmp/ajax_getdesign.php',
		       data:  data,
		       cache: false
	       })
		.done(function (data) {
			      chmp.add_new_module_insert(content_uid, data);
		      })
		.fail(function (jqXHR, textStatus, e) {
			      console.error("fail");
			      console.log(jqXHR);
			      chmp.ajax_errorhandler(jqXHR.status);
		      })
	;
};

/**
 * Adds a new module att bottom of a content
 * @param {int} content_uid
 * @param {string} design - html from template
 */
chmp.add_new_module_insert = function (content_uid, design) {
	$("#chmp-edit-contentarea-" + content_uid).append(design);

	// saves
	chmp.read_dom(true, false);
};

/**
 * Removes a module
 * @param {string} uid
 */
chmp.remove_module = function (uid) {
	$("div[data-chmp-tuid='" + uid + "']").each(function () {
		$(this).parent('li').remove();
	});

	chmp.autosave_start(false);
};


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - Start doing stuff

$(document).ready(function () {

	console.log("hello world - i'm editor");

	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - extend jquery attr();
	// from this answer http://stackoverflow.com/a/14645827
	(function (old) {
		$.fn.attr = function () {
			if ( arguments.length === 0 ) {
				if ( this.length === 0 ) {
					return null;
				}

				var obj = {};
				$.each(this[0].attributes, function () {
					if ( this.specified ) {
						obj[this.name] = this.value;
					}
				});
				return obj;
			}

			return old.apply(this, arguments);
		};
	})($.fn.attr);


	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - triggers

	/** When clicking an image a dialog box appears
	 * with options to change image or change alt text
	 */
	$(document).on('click', '.chmp-editable-img', function () {

		var chmp_title, chmp_imgvars = {}, parent = $(this).parent(), posY = $(parent).offset();

		$(this).each(function () {
			$.each(this.attributes, function () {
				// this.attributes is not a plain object, but an array
				// of attribute nodes, which contain both the name and value
				if ( this.specified ) {
					if ( this.name.substr(0, 10) == 'data-chmp-' ) {
						//console.log(this.name + ": "+this.value);
						chmp_imgvars[this.name] = this.value;
					}
					if ( this.name == 'alt' || this.name == 'title' ) {
						chmp_title = this.value;
					}
				}
			});
		});

		// scroll to top of image
		if ( posY.top < $("body").scrollTop() ) {
			window.scrollTo($("body").scrollLeft(), posY.top);
		}


		var chmp_imagebox = '<div class="chmp-powerTip chmp-powerTip-img">' +
			'<div class="chmp chmp-close"></div>' +
			'<p class="chmp chmp-tooltip-headline chmp-tooltip-element">Edit image</p>' +
			'<div class="chmp chmp-tooltip-element chmp-input chmp-input-small chmp-submit chmp-open-imgedit" data-chmp-imgvars="' + $.param(chmp_imgvars) + '&title=' + chmp_title + '"><p>Change image</p></div>' +
			'<p class="chmp chmp-tooltip-body chmp-tooltip-element">Alt/title text:</p>' +
			'<p class="chmp chmp-tooltip-body"><input type="text" class="chmp chmp-tooltip-element chmp-input chmp-input-small chmp-input-text" id="chmp-imgtext-' + chmp_imgvars['data-chmp-tuid'] + '" value="' + chmp_title + '"></p>' +
			'<div class="chmp chmp-tooltip-element chmp-input chmp-input-small chmp-submit chmp-imgtext-save" data-chmp-imgtext-save="' + chmp_imgvars['data-chmp-tuid'] + '"><p>Save text</p></div>' +
			'</div>';


		$(parent).prepend(chmp_imagebox);


	});

	// saves image text
	$(document).on('click', '.chmp-imgtext-save', function () {

		var img_tuid = $(this).attr('data-chmp-imgtext-save'),
			new_text = $("#chmp-imgtext-" + img_tuid).val();

		$("img[data-chmp-tuid = '" + img_tuid + "']").attr('alt', new_text).attr('title', new_text);

		$(this).parent().remove();

	});


	// closes image edit-box
	$(document).on('click', '.chmp-close', function () {
		$(this).parent().remove();
	});

	$(document).on('click', '.chmp-open-imgedit', function () {
		var imgvars = $(this).attr('data-chmp-imgvars');

		// closes image edit-box
		$(this).parent().remove();

		// opens lightbox
		var config = {  background: '',
			closeOnEsc:             false,
			closeIcon:              '',
			closeOnClick:           'false',
			namespace:              'chmp-featherlight'

		};
		$.featherlight('<iframe src="chmp/imageeditor.php?' + imgvars + '" width="1024" height="628" >', config);

	});


	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - adding and changing modules

	$(document).on('click', '.chmp-add-module-to', function () {

		var content_uid = $(this).attr('data-chmp-add-module-to'),
			new_module = $("#chmp-add-new-module-" + content_uid).val();

		chmp.add_new_module(content_uid, new_module);


	});


	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - dragable modules
	$(".chmp-move-modules").sortable({
		                                 handle:      '.chmp-dragicon',
		                                 placeholder: "chmp-droptarget",
		                                 start:       function (e, ui) {
			                                 ui.placeholder.height(ui.item.height());
		                                 },
		                                 update:      function () {
			                                 chmp.autosave_start(false);
		                                 }

	                                 });


	// prevents pasting stylized html into contenteditable
	// TODO: This doesn't really work in IE
	$(document).on('paste', '[contenteditable]', function (e) {
		e.preventDefault();
		var text = (e.originalEvent || e).clipboardData.getData('text/plain') || prompt('Paste something..');
		document.execCommand('insertText', false, text);
	});


	// starts zenpen editor
	chmp_zen_editor.init();

	// remove module
	$(document).on('click', '.chmp_delete_module', function () {
		var remove = $(this).attr('data-chmp-delete-mod');
		chmp.remove_module(remove);
	});


	// save changes
	$(document).on('keyup', '[contenteditable]', function () {
		chmp.autosave_start(false);
	});

	// publish
	$(document).on('click', '#chmp-do-publish', function () {


		chmp.autosave_start(true);
	});


	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - test
	$("#test-read").click(function () {
		chmp.read_dom(false, false);
	});


});