<?php
// DIC configuration

$container = $app->getContainer();

// Set up environment
$dotenv = new \Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

/**
 * Use a flat-file database housed in /data/users.dat to store user information.
 *
 * @return \Flintstone\Flintstone
 */
$container['users'] = function ($c) {
	return new Flintstone\Flintstone('users', array('dir' => __DIR__ . '/../data'));
};

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

/**
 * Configure both the Realm and User APIs for Tozny so we can issue both Realm-signed and unsigned API
 * calls for the realm configured in the .env file.
 */
$container['tozny_realm'] = function ($c) {
	return new Tozny_Remote_Realm_API(getenv('REALM_KEY_ID'), getenv('REALM_SECRET'), getenv('TOZNY_API'));
};
$container['tozny_user'] = function($c) {
	return new Tozny_Remote_User_API(getenv('REALM_KEY_ID'), getenv('TOZNY_API'));
};

/**
 * Create a Phpass hashing instance for use in hashing and comparing passwords.
 *
 * @return \Hautelook\Phpass\PasswordHash
 */
$container['passwordhasher'] = function ($c) {
	return new \Hautelook\Phpass\PasswordHash(8,false);
};
/**
 * Static error messages
 *
 * The message ky is passed as a query parameter and the message body is then extracted from this array
 * and rendered to the UI.
 */
$container['errors'] = function ($c) {
	return [
		'generic'      => 'Something went wrong ... please contact support',
		'useduser'     => 'That username is already in use',
		'emptyemail'   => 'Please enter a valid email address',
		'badpassword'  => 'Your current password is invalid',
		'nomatch'      => 'Please re-enter the same password to confirm',
		'invalidlogin' => 'Invalid login. Perhaps you need to <a href="/register">register</a> first.',
		'notloggedin'  => 'You don\'t seem to be logged in'
	];
};
/**
 * Static information messages
 *
 * The message ky is passed as a query parameter and the message body is then extracted from this array
 * and rendered to the UI.
 */
$container['messages'] = function ($c) {
	return [
		'registered' => 'Your account is now registered!',
		'checkemail' => 'Please check your email for a magic link.',
		'loggedout'  => 'You have been logged out'
	];
};