<?php
require_once('lib/SforceEnterpriseClient.php');
$auth = syntegratechart_getAuth();
$username 	= $auth['username'];
$password 	= $auth['password'];
$key 		= $auth['key'];

if(syntegratechart_authcheck())
{
	$mySforceConnection = new SforceEnterpriseClient();
	$mySoapClient = $mySforceConnection->createConnection(plugins_url('/', __FILE__ ).'lib/enterprise.wsdl.xml');
	$mylogin = $mySforceConnection->login($username,$password.$key);
}
?>
