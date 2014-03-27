$(document).ready(function () {
	console.log("ready");
	console.log(chmp.logintexts);
	console.log(chmp.logintexts.login);

	(function () {
		var logindesign;

		if ( !chmp.loggedin ) {
			logindesign = '<form method="post" class="chmp-login-form">' +
				'<p>' + chmp.logintexts.login + '</p>';

			if ( typeof chmp.logintexts.inmsg !== 'undefined' ) {
				logindesign += '<p>' + chmp.logintexts.inmsg + '</p>';

			}


			logindesign += '<input type="text" name="chmp-login-user" placeholder="' + chmp.logintexts.username + '"><br>' +
				'<input type="password" name="chmp-login-password"  placeholder="' + chmp.logintexts.password + '"><br>' +
				'<input type="submit" value="' + chmp.logintexts.login + '">' +
				'<input type="hidden" name="chmp-login" do="login">' +
				'</form>';
		}
		$('#chmp-login-btn').data('powertip', logindesign).powerTip({mouseOnToPopup: true, manual: true, smartPlacement: true, placement: 'e'});


	})();


	$(document).on('click', '#chmp-login-btn', function () {
		$('#chmp-login-btn').powerTip('show');

	});

});




