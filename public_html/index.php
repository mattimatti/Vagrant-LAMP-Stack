<?php

require_once 'vendor/slim/Slim/Slim.php';
require_once 'vendor/slim/Slim-Extras/Views/TwigView.php';
require_once 'vendor/slim/Slim-Extras/LogWriters/TimestampLogFileWriter.php';
require_once 'vendor/rb.php';

require_once 'vendor/PHPMailer/class.phpmailer.php';
require_once 'vendor/Twig/Autoloader.php';

require_once 'vendor/Twig/Autoloader.php';
Twig_Autoloader::register();

require_once 'vendor/Twig/Extensions/Autoloader.php';
Twig_Extensions_Autoloader::register();

//require_once 'model/Fake.php';

// SESSION START

session_cache_limiter(false);
session_start();

// SET ENVIRONMENT BASED ON HOST NAME

require_once 'config/hosts.php';

// DEFINE SEPCIFIC SETTINGS DEPENDING ON ENVIRONMENT

if (in_array($_SERVER['HTTP_HOST'], $devlist)) {

	define("ENVIRONMENT", 'development');
	define("BASE_FOLDER", '');
	error_reporting(E_ALL);
	ini_set('display_errors', "1");

} else if (in_array($_SERVER['HTTP_HOST'], $prodlist)) {

	define("ENVIRONMENT", 'production');
	define("BASE_FOLDER", '');

	error_reporting(E_ERROR);
	ini_set('display_errors', 0);

} else if (in_array($_SERVER['HTTP_HOST'], $staginglist)) {

	define("ENVIRONMENT", 'staging');
	define("BASE_FOLDER", '');
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

}

// TRANSLATIONS

// setup language
$availableLanguages = array('fr' => 'fr_FR.utf8', 'en' => 'en_GB.utf8', 'default' => 'en_GB.utf8');

if (!function_exists("gettext")) {
	die("gettext is not installed\n");
}

$lang = "en";

if (isset($_SESSION["lang"])) {
	$lang = $_SESSION["lang"];
}

// Setup language
$locale = (isset($lang) && array_key_exists($lang, $availableLanguages)) ? $availableLanguages[$lang] : $availableLanguages['default'];

$te = 'LC_ALL=$locale';
putenv($te);

$ta = $locale;
setlocale(LC_ALL, $ta);

//http://stackoverflow.com/questions/1776205/php-gettext-problems
// if we are on local development machine, OSX.. different syntax
if (ENVIRONMENT == 'development') {
	putenv('LC_ALL=' . $locale);
	setlocale(LC_ALL, '$ta');
}

$domain = "messages";
$locales_root = "includes/locale";

$filename = "$locales_root/$lang/LC_MESSAGES/$domain.mo";
$mtime = filemtime($filename); // check its modification time
// our new unique .MO file
$filename_new = "$locales_root/$lang/LC_MESSAGES/{$domain}_{$mtime}.mo";

if (!file_exists($filename_new)) { // check if we have created it before
// if not, create it now, by copying the original
	copy($filename, $filename_new);
}

// compute the new domain name
$domain_new = "{$domain}_{$mtime}";

bindtextdomain($domain_new, $locales_root);
bind_textdomain_codeset($domain_new, 'UTF-8');
textdomain($domain_new);

// SETUP TWIG VIEWS

// setup twig
TwigView::$twigDirectory = dirname(__FILE__) . '/vendor/Twig';
TwigView::$twigExtensions = array("Twig_Extensions_Extension_I18n");
//TwigView::$twigOptions = array('cache' => 'tmp/cache/', 'auto_reload' => true);
TwigView::$twigOptions = array('cache' => false, 'auto_reload' => true);

// SETUP DATABASE CONNECTION
// http://redbeanphp.com/manual/setup

if (ENVIRONMENT == 'development') {
	include 'config/development/dbconnection.php';
} else if (ENVIRONMENT == 'production') {
	include 'config/development/dbconnection.php';
	//R::freeze();
} else if (ENVIRONMENT == 'staging') {
	include 'config/production/dbconnection.php';
	//R::freeze();
} else {
	throw new Exception("No environment found");
}

// SETUP SLIM APP

$app = new Slim(
		array('templates.path' => __DIR__ . '/view/', 'log.writer' => new TimestampLogFileWriter(), 'view' => 'TwigView', 'cookies.secret_key' => 'MY_SALTY_PEPPER', 'cookies.lifetime' => time() + (1 * 24 * 60 * 60), 
		// = 1 day
		'cookies.cipher' => MCRYPT_RIJNDAEL_256, 'cookies.cipher_mode' => MCRYPT_MODE_CBC));

$app->config('debug', (ENVIRONMENT == 'development') ? true : false);
$app->config('mode', ENVIRONMENT);

// in case we want to debug sql queries
//R::debug(true);

// this must be on top.
include 'controller/auth.php';
include 'controller/baseapp.php';
include 'controller/hf_application.php';
include 'controller/hf2_application.php';
include 'controller/freegame_application.php';
include 'controller/novartis_csu.php';

// SETUP SLIM APP

// wrap the errors in try catch and send email..
try {
	$app->run();
} catch (Exception $exception) {
	$mailText = 'File :' . $exception->getFile() . 'line: ' . $exception->getLine() . "\n\n" . "------MESSAGE----\n\n" . $exception->getMessage() . "\n\n-------------------" . "\n\n------TRACE--------" . $exception->getTraceAsString() . "\n\n-------------------";
	@mail("mmonti@gmail.com", "Errore app hf " . ENVIRONMENT, $mailText);
	throw $exception;
}

// UTILITY FNCTIONS

function dump($obj) {
	echo "<pre>";
	print_r($obj);
	die();
}

function trace($msg) {
	echo $msg . "<br/>";

}
