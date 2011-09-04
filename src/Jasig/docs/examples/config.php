<?php

// The purpose of this central config file is configuring all examples
// in one place with minimal work for your working environment

$phpcas_path = '../../source/';

///////////////////////////////////////
// Basic Config of the phpCAS client //
///////////////////////////////////////

// Full Hostname of your CAS Server
$cas_host = '192.168.178.254';

// Context of the CAS Server
$cas_context = '/cas';

// Port of your CAS server. Normally for a https server it's 443
$cas_port = 8443;

// Path to the ca chain that issued the cas server certificate
$cas_server_ca_cert_path = '/usr/local/share/ca-certificates/3some-cacert.crt';

//////////////////////////////////////////
// Advanced Config for special purposes //
//////////////////////////////////////////

// The "real" hosts of clustered cas server that send SAML logout messages
// Assumes the cas server is load balanced across multiple hosts
$cas_real_hosts = array (
	'192.168.178.254'
);

// Database config for PGT Storage
//$db = 'pgsql:host=localhost;dbname=phpcas';
$db = 'mysql:host=localhost;dbname=phpcas';
//$db_user = 'phpcas';
//$db_password = '1938r132rwv12cadl';
$db_user = 'root';
$db_password = 'test1234';
$db_table = 'phpcas';

///////////////////////////////////////////
// End Configuration -- Don't edit below //
///////////////////////////////////////////

// Generating the URLS for the local cas example services for proxy testing
if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'){
	$curbase = 'https://'.$_SERVER['SERVER_NAME'];
}else{
	$curbase = 'http://'.$_SERVER['SERVER_NAME'];
}
if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443)
	$curbase .= ':'.$_SERVER['SERVER_PORT'];

$curdir = dirname($_SERVER['REQUEST_URI'])."/";

// access to a single service
$serviceUrl = $curbase.$curdir.'example_service.php';
// access to a second service
$serviceUrl2 = $curbase.$curdir.'example_service_that_proxies.php';

$cas_url = 'https://'.$cas_host;
if ($cas_port != '443')
{
	$cas_url = $cas_url.':'.$cas_port;
}
$cas_url = $cas_url.$cas_context;


// Set the session-name to be unique to the current script so that the client script
// doesn't share its session with a proxied script.
// This is just useful when running the example code, but not normally.
session_name('session_for:'.preg_replace('/[^a-z0-9-]/i', '_', basename($_SERVER['SCRIPT_NAME'])));
?>
