<?php
// Get a DI reference
$container = $app->getContainer();

// Routes

$app->get('/', function($request, $response, $args) {
	/** @var \Slim\Http\Request $request */
	/** @var \Slim\Http\Response $response */

	$error = $request->getQueryParam( 'error' );
	if ( ! empty( $this->errors[ $error ] ) ) {
		$args[ 'error' ] = $this->errors[ $error ];
	}
	$message = $request->getQueryParam( 'message' );
	if ( ! empty( $this->messages[ $message ] ) ) {
		$args[ 'message' ] = $this->messages[ $message ];
	}
	// Render index view
	return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/register', function($request, $response, $args) {
	/** @var \Slim\Http\Request $request */
	/** @var \Slim\Http\Response $response */

	$error = $request->getQueryParam( 'error' );
	if ( ! empty( $this->errors[ $error ] ) ) {
		$args[ 'error' ] = $this->errors[ $error ];
	}
	// Render registration view
	return $this->renderer->render($response, 'register.phtml', $args);
});
$app->post('/register', function($request, $response, $args) {
	/** @var \Slim\Http\Request $request */
	/** @var \Slim\Http\Response $response */

	$fname = $request->getParam('fname');
	$lname = $request->getParam('lname');
	$username = $request->getParam('username');
	$email = $request->getParam('email');
	$password = $request->getParam('password');
	$cpassword = $request->getParam('password_confirm');
	if ( $this->users->get( $username ) ) {
		return $response->withRedirect('/register?error=useduser');
	}
	if ( empty( $email ) ) {
		return $response->withRedirect('/register?error=emptyemail');
	}
	if (! hash_equals($password, $cpassword) ) {
		return $response->withRedirect('/register?error=nomatch');
	}
	$user = [
		'firstName' => $fname,
		'lastName'  => $lname,
		'username'  => $username,
		'email'     => $email,
		'password'  => $this->passwordhasher->HashPassword( $password ),
	];
	$this->users->set( $username, json_encode( $user ) );

	$_SESSION['username'] = $username;

	return $response->withRedirect('/authenticated');
});

$app->any('/login', function($request, $response, $args) {
	/** @var \Slim\Http\Request $request */
	/** @var \Slim\Http\Response $response */

	if ( $request->getParam('magiclink') ) {
		$username = $request->getParam('username');

		if ( ! empty( $username ) ) {
			$user = $this->users->get( $username );

			if ( $user ) {
				$user_data = json_decode( $user, true );

				$sent = $this->tozny_realm->realmLinkChallenge(
					$user_data['email'],
					getenv('SITEURL') . '/login',
					null,
					'authenticate',
					true,
					json_encode( ['username' => $username])
				);

				if ( 'ok' === $sent['return']) {
					return $response->withRedirect('/?message=checkemail');
				}
			}
		}
	}

	return $response->withRedirect('/?error=invalidlogin');
})->add( new PasswordAuthentication($container) )->add( new MagicLinkAuthentication($container) );

$app->get('/authenticated', function($request, $response, $args) {
	/** @var \Slim\Http\Request $request */
	/** @var \Slim\Http\Response $response */

	if ( ! isset( $_SESSION['username'] ) || ! $this->users->get( $_SESSION['username'] ) ) {
		return $response->withRedirect('/?error=notloggedin');
	}
	$error = $request->getQueryParam( 'error' );
	if ( ! empty( $this->errors[ $error ] ) ) {
		$args[ 'error' ] = $this->errors[ $error ];
	}
	$message = $request->getQueryParam( 'message' );
	if ( ! empty( $this->messages[ $message ] ) ) {
		$args[ 'message' ] = $this->messages[ $message ];
	}

	$user_data = $this->users->get( $_SESSION['username'] );
	$user = json_decode( $user_data, true );
	$args['fName'] = $user['firstName'];
	$args['lName'] = $user['lastName'];

	return $this->renderer->render($response, 'authenticated.phtml', $args);
});

/**
 * Logging out is a matter of clearing the PHP session and redirecting to the homepage.
 */
$app->get('/logout', function($request, $response, $args) {
	/** @var \Slim\Http\Request $request */
	/** @var \Slim\Http\Response $response */

	session_destroy();

	return $response->withRedirect('/?loggedout');
} );
