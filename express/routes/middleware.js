'use strict';

function fromBase64( data ) {
	var fromUrlSafe = data
			.replace( /-/g, '+' )
			.replace( /_/g, '/' ) + '=';
	return new Buffer( fromUrlSafe, 'base64' );
}

module.exports = {
	'passwordAuthentication': function(req, res, next) {
		let username = req.body.username;
		let password = req.body.password;

		if ( undefined === username ||undefined === password) {
			next();
		} else if (username && undefined !== req.body.magiclink ) {
			next();
		} else {
			let user = req.app.db.get('users').find({username: username}).value();

			if ( user ) {
				if ( req.app.hasher.compareSync(password, user.password ) ) {
					req.session.username = username;
					req.session.save();

					return res.redirect('/authenticated');
				}
			}

			return res.redirect('/?error=invalidLogin');
		}
	},
	'toznyAuthentication': function(req, res, next) {
		let toznyo = req.query.toznyo;
		let toznyr = req.query.toznyr;

		if (undefined === toznyo || undefined === toznyr) {
			next();
		} else {
			return req.app.tozny_user.linkResult( toznyo ).then( function ( validated ) {
				if ( undefined !== validated.signed_data) {
					let data = validated.signed_data;

					let decoded = fromBase64(data);
					let deserialized = JSON.parse( decoded.toString('utf8') );

					if (undefined !== deserialized.data) {
						let realm_data = JSON.parse( deserialized.data );

						if ( undefined !== realm_data.username ) {
							let username = realm_data.username;
							let user = req.app.db.get('users').find({username: username}).value();

							if ( user ) {
								req.session.username = username;
								req.session.save();

								return res.redirect('/authenticated');
							}
						}
					}
				}

				return next();
			} );
		}
	}
};