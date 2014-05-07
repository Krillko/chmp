/**
 * Created by kristoffer on 2014-03-25.
 */

var chmp_imgedit = chmp_imgedit || [];

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - upload files
function sendFileToServer(formData, status) {
	var uploadURL = "imageeditor_upload.php"; //Upload URL
	var extraData = {}; //Extra Data.
	var jqXHR = $.ajax({
		                   xhr:         function () {
			                   var xhrobj = $.ajaxSettings.xhr();
			                   if ( xhrobj.upload ) {
				                   xhrobj.upload.addEventListener('progress', function (event) {
					                   var percent = 0;
					                   var position = event.loaded || event.position;
					                   var total = event.total;
					                   if ( event.lengthComputable ) {
						                   percent = Math.ceil(position / total * 100);
					                   }
					                   //Set progress
					                   status.setProgress(percent);
				                   }, false);
			                   }
			                   return xhrobj;
		                   },
		                   url:         uploadURL,
		                   type:        "POST",
		                   contentType: false,
		                   processData: false,
		                   cache:       false,
		                   data:        formData,
		                   success:     function (data) {
			                   status.setProgress(100);
			                   //	console.log(data);

			                   $("#original_img_id").val(data);
			                   $("#chmp_use_img").submit();

		                   },
		                   error:       function (data) {

			                   // TODO: Better error handling
			                   alert("Upload fail, are you sure it is an image file");
		                   }
	                   });

	status.setAbort(jqXHR);
}
function createStatusbar(obj) {

	this.statusbar = $("<div class='chmp-statusbar'></div>");
	this.filename = $("<div class='filename'></div>").appendTo(this.statusbar);
	this.size = $("<div class='filesize'></div>").appendTo(this.statusbar);
	this.progressBar = $("<div class='chmp-progressBar'><div></div></div>").appendTo(this.statusbar);
	this.abort = $("<div class='abort'>Abort</div>").appendTo(this.statusbar);
	obj.append(this.statusbar);

	this.setFileNameSize = function (name, size) {
		var sizeStr = "";
		var sizeKB = size / 1024;
		if ( parseInt(sizeKB) > 1024 ) {
			var sizeMB = sizeKB / 1024;
			sizeStr = sizeMB.toFixed(2) + " MB";
		}
		else {
			sizeStr = sizeKB.toFixed(2) + " KB";
		}

		this.filename.html(name);
		this.size.html(sizeStr);
	};
	this.setProgress = function (progress) {
		var progressBarWidth = progress * this.progressBar.width() / 100;
		this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "% ");
		if ( parseInt(progress) >= 100 ) {
			this.abort.hide();
		}
	};
	this.setAbort = function (jqxhr) {
		var sb = this.statusbar;
		this.abort.click(function () {
			jqxhr.abort();
			sb.hide();
		});
	};
}
function handleFileUpload(files, obj) {

	// removes all bindings
	$(obj).off();

	for ( var i = 0; i < files.length; i++ ) {
		var fd = new FormData();
		fd.append('file', files[i]);

		var status = new createStatusbar(obj); //Using this we can set progress.
		status.setFileNameSize(files[i].name, files[i].size);
		sendFileToServer(fd, status);

	}
}


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - crop image
function showCoords(c) {
	$('#x1').val(c.x);
	$('#y1').val(c.y);
	$('#x2').val(c.x2);
	$('#y2').val(c.y2);
	$('#w').val(c.w);
	$('#h').val(c.h);
};


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - save the image

/**
 * Takes values, sends to parent. Parent closes the lightbox
 */
chmp_imgedit.save_close = function () {
	var image_tuid = $("#image_tuid").val(),
		original_img_id = $("#original_img_id").val(),
		new_img_id = $("#new_img_id").val(),
		output_w = $("#output_w").val(),
		output_h = $("#output_h").val();

	console.log(image_tuid);


	parent.chmp.change_img(image_tuid, original_img_id, new_img_id, output_w, output_h);
};


$(document).ready(function () {

	// select and upload page
	if ( chmp.view == 'select' ) {
		var obj = $("#chmp-dropbox");
		obj.on('dragenter', function (e) {
			e.stopPropagation();
			e.preventDefault();
			$(this).addClass('chmp-dropActive');
		});
		obj.on('dragover', function (e) {
			e.stopPropagation();
			e.preventDefault();
		});
		obj.on('drop', function (e) {

			$(this).removeClass('chmp-dropActive');
			e.preventDefault();
			var files = e.originalEvent.dataTransfer.files;

			//We need to send dropped files to Server
			handleFileUpload(files, obj);
		});
		$(document).on('dragenter', function (e) {
			e.stopPropagation();
			e.preventDefault();
		});
		$(document).on('dragover', function (e) {
			e.stopPropagation();
			e.preventDefault();
			obj.removeClass('chmp-dropActive');
		});
		$(document).on('drop', function (e) {
			e.stopPropagation();
			e.preventDefault();
		});


		// select existing image
		$(document).on('click', '.chmp-imgedit-oldimg', function () {
			$("#original_img_id").val($(this).attr('data-chmp-useoldimage'));
			$("#chmp_use_img").submit();
		});


	}

	// crop page
	if ( chmp.view == 'crop' ) {

		// TODO: polluting global - i dont like it
		var jcrop_api;
		var config = {
			onChange:  showCoords,
			onSelect:  showCoords,
			setSelect: chmp.startsize,
			trueSize:  chmp.truesize
		};

		if ( typeof chmp.ratio !== 'undefined' ) {
			config.aspectRatio = chmp.ratio;
		}

		if ( typeof chmp.minSize !== 'undefined' ) {
			config.minSize = chmp.minSize;
		}
		if ( typeof chmp.maxSize !== 'undefined' ) {
			config.maxSize = chmp.maxSize;
		}

		// console.log(config);

		$('#chmp-cropimg').Jcrop(config, function () {
			jcrop_api = this;

		});


		// TODO: build this function
		$(document).on('click', '#disable_lowres', function () {
			var hires = $(this).is(':checked');
			jcrop_api.setOptions(this.checked ? {
				minSize: [ 400, 400 ]
			} : {
				minSize: [ 0, 0 ],
				maxSize: [ 0, 0 ]
			});
			jcrop_api.focus();
		});

		$(document).on('click', '#chmp-do-crop', function () {

			$('*').css('cursor', 'wait');
			$("#chmp_use_img").submit();

		});


	}

	// general
	$(document).on('click', '#chmp-arrownav-select', function () {

		var change_image = confirm("Discard changes and choose another image?");
		if ( change_image ) {
			$("#from_page").val('startover');
			$("#chmp_use_img").submit();
		} else {
			return false;
		}


	});

	$(document).on('click', '#chmp-arrownav-crop', function () {
		$("#chmp_use_img").submit();
	});

	// scale page
	$(document).on('click', '#chmp-do-scale', function () {
		chmp_imgedit.save_close();
	});


});
