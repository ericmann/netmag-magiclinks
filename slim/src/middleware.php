<?php
// Route-specific Middleware
class PasswordAuthentication {
	/**
	 * @var \Slim\Container
	 */
	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	/**
	 * If a username/password pair are submitted, authenticate that way.
	 *
	 * @param \Slim\Http\Request  $request
	 * @param \Slim\Http\Response $response
	 * @param callable            $next
	 *
	 * @return \Slim\Http\Response
	 */
	public function __invoke($request, $response, $next) {
		$username = $request->getParam( 'username' );
		$password = $request->getParam( 'password' );

		// If no username/password, more to the next stack
		if ( empty($username) || empty($password) ) {
			$response = $next( $request, $response );
		} else {
			$user = $this->container->users->get($username);

			if ( $user ) {
				/**
				 * Once we have a user, we need to compare the provided password (during login) with the stored
				 * hash in the database. If they match, great! If not, move on and set a generic "invalid login"
				 * error.
				 */
				$user_data = json_decode( $user, true );
				if ($this->container->passwordhasher->checkPassword( $password, $user_data['password'] ) ) {
					$_SESSION['username'] = $username;

					return $response = $response->withRedirect('/authenticated');
				}
			}
			return $response = $response->withRedirect('/?error=invalidlogin');
		}

		return $response;
	}
}

class MagicLinkAuthentication {
	/**
	 * @var \Slim\Container
	 */
	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	/**
	 * If a toznyo/toznyr pair are submitted, authenticate that way.
	 *
	 * @param \Slim\Http\Request  $request
	 * @param \Slim\Http\Response $response
	 * @param callable            $next
	 *
	 * @return \Slim\Http\Response
	 */
	public function __invoke($request, $response, $next) {
		$toznyo = $request->getQueryParam('toznyo');
		$toznyr = $request->getQueryParam('toznyr');

		// If no toznyo/toznyr, more to the next stack
		if ( empty($toznyo) || empty($toznyr) ) {
			$response = $next( $request, $response );
		} else {
			$validated = $this->container->tozny_user->userLinkResult($toznyo);

			if ( isset( $validated['signed_data'] ) ) {
				$data = $validated['signed_data'];

				$decoded = Tozny_Remote_Realm_API::base64UrlDecode( $data );
				$deserialized = json_decode( $decoded, true );

				if ( isset( $deserialized['data'] ) ) {
					$realm_data = json_decode( $deserialized['data'], true );

					if ( isset( $realm_data['username'] ) ) {
						$username = $realm_data['username'];
						$user = $this->container->users->get( $username );

						if ( $user ) {
							$_SESSION['username'] = $username;

							return $response = $response->withRedirect('/authenticated');
						}
					}
				}
			}

			$response = $next( $request, $response );
		}

		return $response;
	}
}
