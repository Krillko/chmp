/**
 * Copy of php:s date function
 * @param format
 * @param timestamp
 * @returns {*}
 */
function date(format, timestamp) {


	var that = this;
	var jsdate, f;
	// Keep this here (works, but for code commented-out below for file size reasons)
	// var tal= [];
	var txt_words = [
		'Sun', 'Mon', 'Tues', 'Wednes', 'Thurs', 'Fri', 'Satur',
		'January', 'February', 'March', 'April', 'May', 'June',
		'July', 'August', 'September', 'October', 'November', 'December'
	];
	// trailing backslash -> (dropped)
	// a backslash followed by any character (including backslash) -> the character
	// empty string -> empty string
	var formatChr = /\\?(.?)/gi;
	var formatChrCb = function (t, s) {
		return f[t] ? f[t]() : s;
	};
	var _pad = function (n, c) {
		n = String(n);
		while (n.length < c) {
			n = '0' + n;
		}
		return n;
	};
	f = {
		// Day
		d: function () { // Day of month w/leading 0; 01..31
			return _pad(f.j(), 2);
		},
		D: function () { // Shorthand day name; Mon...Sun
			return f.l()
				.slice(0, 3);
		},
		j: function () { // Day of month; 1..31
			return jsdate.getDate();
		},
		l: function () { // Full day name; Monday...Sunday
			return txt_words[f.w()] + 'day';
		},
		N: function () { // ISO-8601 day of week; 1[Mon]..7[Sun]
			return f.w() || 7;
		},
		S: function () { // Ordinal suffix for day of month; st, nd, rd, th
			var j = f.j();
			var i = j % 10;
			if ( i <= 3 && parseInt((j % 100) / 10, 10) == 1 ) {
				i = 0;
			}
			return ['st', 'nd', 'rd'][i - 1] || 'th';
		},
		w: function () { // Day of week; 0[Sun]..6[Sat]
			return jsdate.getDay();
		},
		z: function () { // Day of year; 0..365
			var a = new Date(f.Y(), f.n() - 1, f.j());
			var b = new Date(f.Y(), 0, 1);
			return Math.round((a - b) / 864e5);
		},

		// Week
		W: function () { // ISO-8601 week number
			var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3);
			var b = new Date(a.getFullYear(), 0, 4);
			return _pad(1 + Math.round((a - b) / 864e5 / 7), 2);
		},

		// Month
		F: function () { // Full month name; January...December
			return txt_words[6 + f.n()];
		},
		m: function () { // Month w/leading 0; 01...12
			return _pad(f.n(), 2);
		},
		M: function () { // Shorthand month name; Jan...Dec
			return f.F()
				.slice(0, 3);
		},
		n: function () { // Month; 1...12
			return jsdate.getMonth() + 1;
		},
		t: function () { // Days in month; 28...31
			return (new Date(f.Y(), f.n(), 0))
				.getDate();
		},

		// Year
		L: function () { // Is leap year?; 0 or 1
			var j = f.Y();
			return j % 4 === 0 & j % 100 !== 0 | j % 400 === 0;
		},
		o: function () { // ISO-8601 year
			var n = f.n();
			var W = f.W();
			var Y = f.Y();
			return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0);
		},
		Y: function () { // Full year; e.g. 1980...2010
			return jsdate.getFullYear();
		},
		y: function () { // Last two digits of year; 00...99
			return f.Y()
				.toString()
				.slice(-2);
		},

		// Time
		a: function () { // am or pm
			return jsdate.getHours() > 11 ? 'pm' : 'am';
		},
		A: function () { // AM or PM
			return f.a()
				.toUpperCase();
		},
		B: function () { // Swatch Internet time; 000..999
			var H = jsdate.getUTCHours() * 36e2;
			// Hours
			var i = jsdate.getUTCMinutes() * 60;
			// Minutes
			var s = jsdate.getUTCSeconds(); // Seconds
			return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
		},
		g: function () { // 12-Hours; 1..12
			return f.G() % 12 || 12;
		},
		G: function () { // 24-Hours; 0..23
			return jsdate.getHours();
		},
		h: function () { // 12-Hours w/leading 0; 01..12
			return _pad(f.g(), 2);
		},
		H: function () { // 24-Hours w/leading 0; 00..23
			return _pad(f.G(), 2);
		},
		i: function () { // Minutes w/leading 0; 00..59
			return _pad(jsdate.getMinutes(), 2);
		},
		s: function () { // Seconds w/leading 0; 00..59
			return _pad(jsdate.getSeconds(), 2);
		},
		u: function () { // Microseconds; 000000-999000
			return _pad(jsdate.getMilliseconds() * 1000, 6);
		},

		// Timezone
		e: function () { // Timezone identifier; e.g. Atlantic/Azores, ...
			// The following works, but requires inclusion of the very large
			// timezone_abbreviations_list() function.
			/*              return that.date_default_timezone_get();
			*/
			throw 'Not supported (see source code of date() for timezone on how to add support)';
		},
		I: function () { // DST observed?; 0 or 1
			// Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
			// If they are not equal, then DST is observed.
			var a = new Date(f.Y(), 0);
			// Jan 1
			var c = Date.UTC(f.Y(), 0);
			// Jan 1 UTC
			var b = new Date(f.Y(), 6);
			// Jul 1
			var d = Date.UTC(f.Y(), 6); // Jul 1 UTC
			return ((a - c) !== (b - d)) ? 1 : 0;
		},
		O: function () { // Difference to GMT in hour format; e.g. +0200
			var tzo = jsdate.getTimezoneOffset();
			var a = Math.abs(tzo);
			return (tzo > 0 ? '-' : '+') + _pad(Math.floor(a / 60) * 100 + a % 60, 4);
		},
		P: function () { // Difference to GMT w/colon; e.g. +02:00
			var O = f.O();
			return (O.substr(0, 3) + ':' + O.substr(3, 2));
		},
		T: function () { // Timezone abbreviation; e.g. EST, MDT, ...
			// The following works, but requires inclusion of the very
			// large timezone_abbreviations_list() function.
			/*              var abbr, i, os, _default;
			if (!tal.length) {
			tal = that.timezone_abbreviations_list();
			}
			if (that.php_js && that.php_js.default_timezone) {
			_default = that.php_js.default_timezone;
			for (abbr in tal) {
			for (i = 0; i < tal[abbr].length; i++) {
			if (tal[abbr][i].timezone_id === _default) {
			return abbr.toUpperCase();
			}
			}
			}
			}
			for (abbr in tal) {
			for (i = 0; i < tal[abbr].length; i++) {
			os = -jsdate.getTimezoneOffset() * 60;
			if (tal[abbr][i].offset === os) {
			return abbr.toUpperCase();
			}
			}
			}
			*/
			return 'UTC';
		},
		Z: function () { // Timezone offset in seconds (-43200...50400)
			return -jsdate.getTimezoneOffset() * 60;
		},

		// Full Date/Time
		c: function () { // ISO-8601 date.
			return 'Y-m-d\\TH:i:sP'.replace(formatChr, formatChrCb);
		},
		r: function () { // RFC 2822
			return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
		},
		U: function () { // Seconds since UNIX epoch
			return jsdate / 1000 | 0;
		}
	};
	this.date = function (format, timestamp) {
		that = this;
		jsdate = (timestamp === undefined ? new Date() : // Not provided
			(timestamp instanceof Date) ? new Date(timestamp) : // JS Date()
				new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
			);
		return format.replace(formatChr, formatChrCb);
	};
	return this.date(format, timestamp);
}


/**
 * Updates global variables when moving things in structure
 * @param e
 */
chmp.updateOutput = function (e) {
	var list = e.length ? e : $(e.target),
		findchildren = (list.nestable('serialize'));

	// reset current_structure
	chmp.current_structure = {};
	chmp.set_current_structure(findchildren, 0);

	// check if there is anything in trash
	chmp.trash_headline();

	if ( chmp.firstrun_complete ) {
		chmp.show_reminder();
	} else {
		chmp.firstrun_complete = true;
	}


};

/**
 * Editing has started, enable save and show reminder
 */
chmp.show_reminder = function () {
	if ( !chmp.changes_to_save ) {
		$("#struct_reminder").stop().html('You have unsaved changes').fadeIn();
		$("#stuct_save_holder").removeClass('chmp-submit-inactive');
		chmp.changes_to_save = true;


		window.onbeforeunload = function () {
			return 'You have unsaved changes, are you sure you want to leave this page?';
		};

	}
};


/**
 * Recursive function to find all pages that has children
 * turn off skip if impossible
 * sets father id
 * @param {object} findchildren
 * @param {int} father
 */
chmp.set_current_structure = function (findchildren, father) {


	for ( var i in findchildren ) {
		chmp.current_structure[findchildren[i].id] = {};
		chmp.current_structure[findchildren[i].id].father = father;

		if ( typeof findchildren[i].children !== 'undefined' ) {
			chmp.current_structure[findchildren[i].id].hasChildren = true;
			chmp.set_current_structure(findchildren[i].children, findchildren[i].id);
		} else {
			chmp.current_structure[findchildren[i].id].hasChildren = false;

			if ( chmp.structure[findchildren[i].id].skip ) {
				chmp.update_tree_cell(findchildren[i].id, true, null, null, false, null);
			}

		}
	}


};

/**
 * Saves the structure
 * After save we may force a reload
 */
chmp.save_struct = function () {
	var send_result = {};

	if ( chmp.changes_to_save ) {
		$("#struct_reminder").html('<img src="chmp/editordesign/ajax-loader.gif">');

		send_result.active = $('#chmp_structure').nestable('serialize');
		send_result.trash = $('#chmp_structure_trash').nestable('serialize');

		send_result.structure = chmp.structure;
		send_result.current_structure = chmp.current_structure;

		send_result.lang = chmp.lang;


		$.ajax({
				type:  "POST",
				url:   'chmp/ajax_save_structure.php',
				cache: false,
				data:  send_result
			})
			.done(function (data) {

					console.log(data);

					if ( data == 'reload' ) {
						window.onbeforeunload = null; // turns of reminder
						location.reload(true); // reloads page

					} else if ( data == 'ok' ) {

						$("#struct_reminder").html('Last saved ' + date('H:i:s')).delay(6000).fadeOut();
						$("#stuct_save_holder").addClass('chmp-submit-inactive');
						chmp.changes_to_save = false;
						window.onbeforeunload = null;

					} else {
						console.warn(data);

					}

				})
			.fail(function (jqXHR, textStatus, e) {
					console.error("fail");
					console.groupCollapsed("Error report");
					console.log(jqXHR);
					console.log(textStatus);
					console.log(e);
					console.groupEnd();
					/*
					chmp.ajax_errorhandler(jqXHR.status);
					*/

					alert('could not save structure, see console');
				})
		;

	}
};

/**
 * Check if there is anything in trash and shows title text
 */
chmp.trash_headline = function () {
	var test = $('#chmp_structure_trash').nestable('serialize');
	if ( test.length == 1 ) {
		$("#struct_delete_headline").html('This page will be deleted:');
	} else if ( test.length > 0 ) {
		$("#struct_delete_headline").html('These pages will be deleted:');
	} else {
		$("#struct_delete_headline").html('&nbsp;');
	}
};

/**
 * Get suggested url for page (recursive)
 * Same as php  get_autourl in classes/Read_structure.php
 * @param {int} id
 * @returns {string}
 */
chmp.get_autourl = function (id) {
	var output = chmp.urlformat(chmp.structure[id].name, chmp.rich_urls);

	if ( chmp.current_structure[id].father > 0 ) {
		return chmp.get_autourl(chmp.current_structure[id].father) + '/' + output;
	} else {
		return output;
	}

};


/**
 * Makes a url friendly string
 * Same as Tools::urlformat in classes/Tools.php
 * @param {string} input
 * @param {boolean} [utf8] allows url to contain utf8 chars
 * @returns {string}
 */
chmp.urlformat = function (input, utf8) {

	var output = input.toLowerCase();

	// replaces whitespace with underscore
	output = output.replace(/\s/g, '_');

	if ( chmp.rich_urls ) {
		output = output.replace(/[^A-Za-z0-9\s,.\u00C0-\u1FFF\u2C00-\uD7FF\w]/g, '');
	} else {
		// common replacements
		// åäæàáâãāăảȧǎȁąạḁẚầấẫẩằắẵẳǡǟǻậặǽǣ = a
		output = output.replace(/[\u00E5\u00E4\u00E6\u00E0\u00E1\u00E2\u00E3\u0101\u0103\u1EA3\u0227\u01CE\u0201\u0105\u1EA1\u1E01\u1E9A\u1EA7\u1EA5\u1EAB\u1EA9\u1EB1\u1EAF\u1EB5\u1EB3\u01E1\u01DF\u01FB\u1EAD\u1EB7\u01FD\u01E3]/g, 'a');
		// ḃɓḅḇƀƃƅ = b
		output = output.replace(/[\u1E03\u0253\u1E05\u1E07\u0180\u0183\u0185]/g, 'b');
		// ćĉċčƈçḉ = c
		output = output.replace(/[\u0107\u0109\u010B\u010D\u0188\u00E7\u1E09]/g, 'c');
		// ḋɗḍḏḑḓďđƌȡ = d
		output = output.replace(/[\u1E0B\u0257\u1E0D\u1E0F\u1E11\u1E13\u010F\u0111\u018C\u0221]/g, 'd');
		// èéêẽēĕėëẻěȅȇẹȩęḙḛềếễểḕḗệḝǝɛ = e
		output = output.replace(/[\u00E8\u00E9\u00EA\u1EBD\u0113\u0115\u0117\u00EB\u1EBB\u011B\u0205\u0207\u1EB9\u0229\u0119\u1E19\u1E1B\u1EC1\u1EBF\u1EC5\u1EC3\u1E15\u1E17\u1EC7\u1E1D\u01DD\u025B]/g, 'e');
		// ḟƒ = f
		output = output.replace(/[\u1E1F\u0192]/g, 'f');
		// ǵĝḡğġǧɠģǥ = g
		output = output.replace(/[\u01F5\u011D\u1E21\u011F\u0121\u01E7\u0260\u0123\u01E5]/g, 'g');
		// ĥḣḧȟƕḥḩḫẖħ = h
		output = output.replace(/[\u0125\u1E23\u1E27\u021F\u0195\u1E25\u1E29\u1E2B\u1E96\u0127]/g, 'h');
		// ìíîĩīĭıïỉǐịȉȋḭɨḯ = i
		output = output.replace(/[\u00EC\u00ED\u00EE\u0129\u012B\u012D\u0131\u00EF\u1EC9\u01D0\u1ECB\u0209\u020B\u1E2D\u0268\u1E2F]/g, 'i');

		// ĵǰ = j
		output = output.replace(/[\u0135\u01F0]/g, 'j');
		// ḱǩḵƙḳķ = k
		output = output.replace(/[\u1E31\u01E9\u1E35\u0199\u1E33\u0137]/g, 'k');
		// ĺḻḷļḽľŀłƚḹȴ = l
		output = output.replace(/[\u013A\u1E3B\u1E37\u013C\u1E3D\u013E\u0140\u0142\u019A\u1E39\u0234]/g, 'l');
		// ḿṁṃɯ = m
		output = output.replace(/[\u1E3F\u1E41\u1E43\u026F]/g, 'm');
		// ǹńñṅňŋɲṇņṋṉŉƞȵ = n
		output = output.replace(/[\u01F9\u0144\u00F1\u1E45\u0148\u014B\u0272\u1E47\u0146\u1E4B\u1E49\u0149\u019E\u0235]/g, 'n');
		// òóôõōŏȯöỏőǒȍȏơǫọɵøồốỗổȱȫȭṍṏṑṓờớỡởợǭộǿɔ = o
		output = output.replace(/[\u00F2\u00F3\u00F4\u00F5\u014D\u014F\u022F\u00F6\u1ECF\u0151\u01D2\u020D\u020F\u01A1\u01EB\u1ECD\u0275\u00F8\u1ED3\u1ED1\u1ED7\u1ED5\u0231\u022B\u022D\u1E4D\u1E4F\u1E51\u1E53\u1EDD\u1EDB\u1EE1\u1EDF\u1EE3\u01ED\u1ED9\u01FF\u0254]/g, 'o');

		// ṕṗƥ = p
		output = output.replace(/[\u1E55\u1E57\u01A5]/g, 'p');
		// ŕṙřȑȓṛŗṟṝ = r
		output = output.replace(/[\u0155\u1E59\u0159\u0211\u0213\u1E5B\u0157\u1E5F\u1E5D]/g, 'r');
		// śŝṡšṣșşṥṧṩſẛ = s
		output = output.replace(/[\u015B\u015D\u1E61\u0161\u1E63\u0219\u015F\u1E65\u1E67\u1E69\u017F\u1E9B]/g, 's');
		// ß = ss
		output = output.replace(/[\u00DF]/g, 'ss');
		// ṫẗťƭʈƫṭțţṱṯŧȶ = t
		output = output.replace(/[\u1E6B\u1E97\u0165\u01AD\u0288\u01AB\u1E6D\u021B\u0163\u1E71\u1E6F\u0167\u0236]/g, 't');
		// ùúûũūŭüủůűǔȕȗưụṳųṷṵṹṻǜǘǖǚừứữửự = u
		output = output.replace(/[\u00F9\u00FA\u00FB\u0169\u016B\u016D\u00FC\u1EE7\u016F\u0171\u01D4\u0215\u0217\u01B0\u1EE5\u1E73\u0173\u1E77\u1E75\u1E79\u1E7B\u01DC\u01D8\u01D6\u01DA\u1EEB\u1EE9\u1EEF\u1EED\u1EF1]/g, 'u');
		// ṽṿ = v
		output = output.replace(/[ṽṿ]/g, 'v');
		// ẁẃŵẇẅẘẉ = w
		output = output.replace(/[\u1E81\u1E83\u0175\u1E87\u1E85\u1E98\u1E89]/g, 'w');
		// ẋẍ = x
		output = output.replace(/[\u1E8B\u1E8D]/g, 'x');
		// ỳýŷȳẏÿỷẙƴỵ = y
		output = output.replace(/[\u1EF3\u00FD\u0177\u0233\u1E8F\u00FF\u1EF7\u1E99\u01B4\u1EF5]/g, 'y');
		// źẑżžȥẓẕƶ = z
		output = output.replace(/[\u017A\u1E91\u017C\u017E\u0225\u1E93\u1E95\u01B6]/g, 'z');

		// ligatures
		// ĳ = ij
		output = output.replace(/[\u0133]/g, 'ij');
		// ﬀ = ff
		output = output.replace(/[\uFB00]/g, 'ff');
		// ﬁ = fi
		output = output.replace(/[\uFB01]/g, 'fi');
		// ﬂ = ff
		output = output.replace(/[\uFB02]/g, 'fl');
		// ﬃ = ffi
		output = output.replace(/[\uFB03]/g, 'ffi');
		// ﬄ = ffl
		output = output.replace(/[\uFB04]/g, 'ffl');
		// œ = oe
		output = output.replace(/[\u0153]/g, 'oe');
		// ĳ = ij
		output = output.replace(/[\u0133]/g, 'ij');

		output = output.replace(/[^A-Za-z0-9\s._,]/g, '');
	}

	while (output.indexOf("__") > -1) {
		output = output.replace('__', '_');
	}

	return output;

};

/**
 * Updates all the pages in structure
 */
chmp.update_tree_all = function () {
	for ( var i in chmp.structure ) {
		chmp.update_tree_cell(parseInt(i, 10), false, chmp.structure[i].status, chmp.structure[i].hidden, chmp.structure[i].skip, chmp.structure[i].name);
	}
};


/**
 * Updata a single cell in structure tree
 * Use null to keep current value
 * @param {int|null} id - null: use currently_editing
 * @param {boolean} [update_json = false] - true: update design and global variable, false: design only
 * @param {string|null} [status]
 * @param {boolean|null} [hidden]
 * @param {boolean|null} [skip]
 * @param {string|null} [name]
 */
chmp.update_tree_cell = function (id, update_json, status, hidden, skip, name) {
	if ( id === null ) {
		id = chmp.currently_editing;
	}
	if ( !is_defined(update_json) ) {
		update_json = false;
	}
	if ( !is_defined(name) ) {
		name = null;
	}
	if ( !is_defined(status) ) {
		status = null;
	}
	if ( !is_defined(skip) ) {
		skip = null;
	}

	var active = $('[data-id="' + id + '"]'),
		active_box = $(active).children('.dd3-content'),
		active_handle = $(active).children('.dd3-handle'),
		active_info = $(active_box).children('.chmp-struct-title');

	if ( name !== null ) {
		$(active_info).children('.chmp-struct-text').html(name);
		if ( update_json ) {
			chmp.structure[id].name = name;
		}
	}

	if ( status !== null ) {

		$(active_info).removeClass('chmp-struct-published').removeClass('chmp-struct-edited').removeClass('chmp-struct-unpublished').addClass('chmp-struct-' + status);

		if ( status == 'unpublished' ) {
			$(active_box).addClass('chmp-struct-hidden');
		}

		if ( update_json ) {
			chmp.structure[id].status = status;
		}

	}

	if ( hidden !== null || status == 'unpublished' ) {

		// skipped pages can't be hidden
		if ( (hidden && skip !== true) || status == 'unpublished' ) {
			$(active_box).addClass('chmp-struct-hidden');

			$(active_handle).addClass('chmp-struct-hidden-handle');

		} else if ( status != 'unpublished' ) {
			$(active_box).removeClass('chmp-struct-hidden');

			$(active_handle).removeClass('chmp-struct-hidden-handle');
		}

		if ( update_json ) {
			chmp.structure[id].hidden = hidden;
		}

	}


	if ( skip !== null ) {

		if ( skip ) {
			$(active_info).addClass('chmp-struct-skip');
		} else {
			$(active_info).removeClass('chmp-struct-skip');
		}

		if ( update_json ) {
			chmp.structure[id].skip = skip;
		}


	}


};


/**
 * Sets right panel to specific page
 * @param {int} id
 */
chmp.set_right = function (id) {

	var autourl;

	chmp.currently_editing = id;


	if ( !chmp.rightExpandedStart ) {
		$("#struct_pageinfo").slideDown('fast');
		$("#struct_actions").slideDown('fast');
		$("#struct_advanced").slideDown('fast'); // remove this after testing, advances should start closed
		chmp.rightExpandedStart = true;
	}

	// name
	$("#struct_name").val(chmp.structure[id].name);

	// template
	if ( is_defined(chmp.structure[id].template) ) {
		$("#struct_template").val(chmp.structure[id].template);
	} else {
		// sets to first option
		$("#struct_template").val($("#struct_template option:first").val());

	}

	// autourl
	autourl = chmp.get_autourl(id);
	$("#url_accept_suggestion").html(autourl);

	if ( chmp.structure[id].custom_url ) {
		$("#struct_url").val(chmp.structure[id].url).removeClass('url_autocomplete');
		$("#url_suggestion").slideDown('fast');
	} else {
		$("#struct_url").val(autourl).addClass('url_autocomplete');
		$("#url_suggestion").slideUp('fast');
	}

	// check if the page can be skipped
	if ( chmp.current_structure[id].hasChildren ) {
		$("#struct_skip_label").css('opacity', '1').html('Skip this page and jump to the first page below');

		if ( chmp.structure[id].skip ) {
			$("#struct_skip").prop("checked", true).prop("disabled", false);
		} else {
			$("#struct_skip").prop("checked", false).prop("disabled", false);
		}

	} else {
		$("#struct_skip").prop("checked", false).prop("disabled", true);
		$("#struct_skip_label").css('opacity', '0.5').html('Only pages with children can be skipped');
	}

	// hidden
	if ( chmp.structure[id].status != 'unpublished' && chmp.structure[id].hidden ) {
		$("#struct_action_hide").removeClass('chmp-submit-inactive').children('p').html('UNHIDE PAGE');

	} else if ( chmp.structure[id].status == 'unpublished' ) {
		// hide button
		$("#struct_action_hide").addClass('chmp-submit-inactive').children('p').html('HIDE PAGE');

	} else {
		$("#struct_action_hide").removeClass('chmp-submit-inactive').children('p').html('HIDE PAGE');
	}

	// duplicate - you can't copy a new page
	if ( chmp.structure[id].new_page ) {
		$("#struct_action_duplicate").addClass('chmp-submit-inactive');
	} else {
		$("#struct_action_duplicate").removeClass('chmp-submit-inactive');
	}


};

/**
 * Sets Hide/Unhide button accoring to chmp.currently_editing
 */
chmp.toggle_hidden_button = function () {
	if ( chmp.structure[chmp.currently_editing].hidden ) {
		chmp.update_tree_cell(null, true, null, false, null, null);
		$("#struct_action_hide p").html('HIDE PAGE');

	} else {
		chmp.update_tree_cell(null, true, null, true, null, null);
		$("#struct_action_hide p").html('UNHIDE PAGE');

	}
};


/**
 * Test if something is defined
 * @param input
 * @returns {boolean}
 */
function is_defined(input) {
	if ( typeof input !== 'undefined' ) {
		return true;
	}
	return false;
}

/**
 * Adds a new page at the bottom of the structure
 * @param {int} [copy_of] - default false: brand new page, int: copy a page
 */
chmp.add_page = function (copy_of) {
	copy_of = copy_of || false;

	var page_name = 'New page', json = {}, namecheck = {};
	json.lang = chmp.lang;


	$.ajax({
			type:  "POST",
			url:   'chmp/ajax_add_page.php',
			cache: false,
			data:  json
		})
		.done(function (data) {
			data = parseInt(data, 10);

			if ( copy_of !== false ) {
				page_name = chmp.structure[copy_of].name + ' copy';
			}


			var new_page = '<li class="dd-item dd3-item" data-id="' + data + '">' +
				'<div class="dd-handle dd3-handle chmp-struct-hidden-handle"></div>' +
				'	<div class="dd3-content chmp-struct-hidden">' +
				'		<div class="chmp-struct-title chmp-struct-unpublished">' +
				'			<div class="chmp-struct-icon"></div>' +
				'			<div class="chmp-struct-text">' + page_name + '</div>' +
				'			<div class="chmp-struct-skip-icon"></div>' +
				'		</div>' +
				//  '		<div class="chmp-struct-goto"><a href="javascript:;">Show</a></div>' +
				'	</div>' +
				'</li>';

			$("#chmp_structure_active").append(new_page);

			chmp.structure[data] = {};
			chmp.structure[data].name = page_name;
			chmp.structure[data].status = 'unpublished';
			chmp.structure[data].new_page = true; // marks the page as new, the actual id number is
			// checked on save, and only then is the real id set
			// and json files created

			chmp.structure[data].copy_of = copy_of;

			console.log(chmp.templates[0].file);

			chmp.structure[data].template = chmp.templates[0].file;

			chmp.current_structure[data] = {};
			chmp.current_structure[data].father = 0;
			chmp.current_structure[data].hasChildren = false;

			// check that the name is valid

			namecheck.url = chmp.urlformat(page_name, false);
			namecheck.lang = chmp.lang;
              namecheck.used = [];

		for ( var i in chmp.structure) {

			console.log(chmp.structure[i]);

			if (typeof chmp.structure[i].url !== 'undefined') {
			namecheck.used.push(chmp.structure[i].url);
			}
		}
					console.log(chmp.structure);
					console.log(namecheck);

			$.ajax({
						type:  "POST",
						url:   'chmp/ajax_test_name.php',
						cache: false,
						data:  namecheck
					})
				.done(function (data2) {

							console.log(data);
							console.log(data2);

							chmp.structure[data].url = data2;
							chmp.set_right(data);


						}).fail(function (jqXHR, textStatus, e) {
									console.error("fail namecheck");
									console.groupCollapsed("Error report");
									console.log(jqXHR);
									console.log(textStatus);
									console.log(e);
									console.groupEnd();
									/*
									chmp.ajax_errorhandler(jqXHR.status);
									*/

									alert('could not add page, see console');
								});


			})
		.fail(function (jqXHR, textStatus, e) {
				console.error("fail");
				console.groupCollapsed("Error report");
				console.log(jqXHR);
				console.log(textStatus);
				console.log(e);
				console.groupEnd();
				/*
				chmp.ajax_errorhandler(jqXHR.status);
				*/

				alert('could not add page, see console');
			})
	;


};


$(document).ready(function () {

	// start nestable
	$('#chmp_structure').nestable({
									group: 1
								}).on('change', chmp.updateOutput);

	$('#chmp_structure_trash').nestable({
											group: 1
										});

	// output initial serialised data
	chmp.updateOutput($('#chmp_structure').data('output', $('#chmp_structure-output')));

	// setting up structure
	chmp.update_tree_all();


	$(document).on('click', '.dd3-content', function () {

		var id = $(this).parent().attr('data-id');
		$('.chmp-struct-active').removeClass('chmp-struct-active');
		$(this).addClass('chmp-struct-active');
		chmp.set_right(id);
	});

	$(document).on('click', '#toggleAdvanced', function () {
		if ( chmp.rightExpandedStart ) {
			$("#struct_advanced").slideToggle();
		}

	});

	// change name
	$(document).on('keyup', '#struct_name', function () {
		var input = $(this).val(),
			autourl;
		chmp.update_tree_cell(null, true, null, null, null, input);

		autourl = chmp.get_autourl(chmp.currently_editing);

		$("#url_accept_suggestion").html(autourl);

		if ( !chmp.structure[chmp.currently_editing].custom_url ) {
			$("#struct_url").val(autourl);
		}
		chmp.show_reminder();

	});

	// change template
	$(document).on('change', '#struct_template', function () {
		var input = $(this).val();
		chmp.structure[chmp.currently_editing].template = input;
		chmp.show_reminder();
	});

	// change skip
	$(document).on('change', '#struct_skip', function () {
		var input = $(this).is(':checked');
		chmp.update_tree_cell(null, true, null, null, input, null);
		chmp.show_reminder();
	});

	// url suggestion
	$(document).on('keyup', '#struct_url', function () {
		chmp.structure[chmp.currently_editing].url = $(this).val();
		chmp.structure[chmp.currently_editing].custom_url = true;
		$("#struct_url").removeClass('url_autocomplete');
		$("#url_suggestion").slideDown('fast');
		chmp.show_reminder();
	});

	$(document).on('click', '#url_accept_suggestion', function () {
		var input = $(this).html();
		$("#struct_url").addClass('url_autocomplete').val(input);
		$("#url_suggestion").slideUp('fast');

		chmp.structure[chmp.currently_editing].custom_url = false;
		delete chmp.structure[chmp.currently_editing].url;
		chmp.show_reminder();
	});

	// add a page
	$(document).on('click', '#struct_add_page', function () {
		chmp.add_page();
		chmp.show_reminder();
	});

	// hide unhide
	$(document).on('click', '#struct_action_hide', function () {
		if ( !$(this).hasClass('chmp-submit-inactive') ) {
			chmp.toggle_hidden_button();
			chmp.show_reminder();
		}

	});

	// duplicate
	$(document).on('click', '#struct_action_duplicate', function () {
		if ( !$(this).hasClass('chmp-submit-inactive') ) {
			chmp.add_page(chmp.currently_editing);
			chmp.show_reminder();
		}

	});

	// save
	$(document).on('click', '#struct_save', function () {
		chmp.save_struct();

	});


});
