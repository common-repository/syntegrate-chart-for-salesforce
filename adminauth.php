<?php
global $wpdb;
require_once('lib/SforceEnterpriseClient.php');
$mySforceConnection = new SforceEnterpriseClient();
$mySoapClient = $mySforceConnection->createConnection(plugins_url('/', __FILE__ ).'lib/enterprise.wsdl.xml');
if(isset($_POST['auth']))
{
	$wpdb->update( 'syntegratechart_auth', array('username'=> $_POST['username'],'password'=> $_POST['password'],'key'=> $_POST['key']) , array('id' => 1), $format = null, $where_format = null );
	?>
	<script>
		window.location.href = "admin.php?page=syntegratechart_admin_auth";
	</script>
	<?php
}

$res = (array)$wpdb->get_row($wpdb->prepare("SELECT * FROM `syntegratechart_auth` WHERE id='1'",''));

$username 	= $res['username'];
$password 	= $res['password'];
$key 		= $res['key'];
$msgerr = '';
try
{
	$mySforceConnection->login($username,$password.$key);
	$msgerr = 'w';
}
catch(Exception $e)
{
	$msgerr = 'e';
}
if($msgerr == 'w')
{
	$msgerrs = '<img style="margin:-8px 4px -8px -5px" src="'.plugins_url('images/w.gif', __FILE__ ).'">  <b>Credentials stored successfully. </b>';
	$col = '#7ad03a';

}
else
{
	$msgerrs = '<img style="margin:-8px 4px -8px -5px" src="'.plugins_url('images/er.png', __FILE__ ).'"> <b>Please enter your Salesforce credentials to get started.</b>';
	$col = '#F54B1D';
}
?>
<div id="adminform_syntegratechart">
<h2>Welcome to Syntegrate Chart!</h2>
<hr>
<br />
<div class="updated" style="margin:5px 0px 2px;border-left:4px solid <?php echo $col; ?>;" id="message">
	<p><strong><?php echo $msgerrs; ?></strong></p>
</div>
<form action="" method="post">
	<p>Username:</p>
	<input type="text" name="username" value="<?php echo $res['username']; ?>">
	<p>Password:</p>
	<input type="password" name="password" value="<?php echo $res['password']; ?>" >
	<p>Key:</p>
	<input type="text" name="key" value="<?php echo $res['key']; ?>">
	<br /><br />
	<input type="submit"  name="auth" value="Save">
</form>
<br /><br />
</div>