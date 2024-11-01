<?php
/*
Plugin Name: Syntegrate Chart
Description: Unleash your Salesforce data in easy-to-use charts and graphs for your Wordpress site.
Version: 1.0
Author: Visual Antidote
Plugin URI: http://www.syntegrate.it/
*/
/* register activation code start */
function syntegratechart_db_activate()
{
	global $wpdb;

	$wpdb->query("DROP TABLE IF EXISTS `syntegratechart_auth`;");
	$wpdb->query("DROP TABLE IF EXISTS `syntegratechart_settings`;");
	$wpdb->query("DROP TABLE IF EXISTS `syntegratechart_details`;");
	$wpdb->query("DROP TABLE IF EXISTS `syntegratechart_details_value`;");
	$wpdb->query("DROP TABLE IF EXISTS `syntegratechart_filter`;");

	$wpdb->query("CREATE TABLE IF NOT EXISTS `syntegratechart_auth` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`username` varchar(255),
	`password` varchar(255),
	`key` varchar(255),
	 PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

	$wpdb->query($wpdb->prepare("INSERT INTO `syntegratechart_auth` (`id`) VALUES (1)",''));

	$wpdb->query("CREATE TABLE IF NOT EXISTS `syntegratechart_details` (
	`chart_id` int(11) NOT NULL AUTO_INCREMENT,
	`chart_type` varchar(255),
	`table` varchar(255),
	`filter_and_or` varchar(255),
	`related` varchar(255),
	`label` varchar(255),
	`data_refresh_v` varchar(255),
	`data_refresh_t` varchar(255),
	`current_date` datetime,
	`data` longtext,
	 PRIMARY KEY (`chart_id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

	$wpdb->query("CREATE TABLE IF NOT EXISTS `syntegratechart_details_value` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`chart_id` varchar(255),
	`value` varchar(255),
	 PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

	$wpdb->query("CREATE TABLE IF NOT EXISTS `syntegratechart_settings` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`chart_id` varchar(255),
	`height` varchar(255),
	`width` varchar(255),
	`title` varchar(255),
	`font_size` varchar(255),
	`font_style` varchar(255),
	`font_color` varchar(255),
	`position` varchar(255),
	`background_color` varchar(255),
	`3d` varchar(255),
	`orientation` varchar(255),
	`haxis_angle` varchar(255),
	`legend_position` varchar(255),
	`legend_alignment` varchar(255),
	`legend_text_color` varchar(255),
	`legend_font_size` varchar(255),
	`haxis_position` varchar(255),
	`vaxis_position` varchar(255),
	`haxis_title` varchar(255),
	`vaxis_title` varchar(255),
	`bubble_size` varchar(255),
	 PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

	 $wpdb->query("CREATE TABLE IF NOT EXISTS `syntegratechart_filter` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`chart_id` varchar(255),
	`filter_field` varchar(255),
	`filter` varchar(255),
	`filter_value` varchar(255),
	 PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

}
register_activation_hook( __FILE__,'syntegratechart_db_activate');
/* register activation code end */


/* register deactivation code start */
function syntegratechart_db_deactivation()
{
	global $wpdb;
	$wpdb->query("DROP TABLE IF EXISTS `syntegratechart_auth`;");
	$wpdb->query("DROP TABLE IF EXISTS `syntegratechart_settings`;");
	$wpdb->query("DROP TABLE IF EXISTS `syntegratechart_details`;");
	$wpdb->query("DROP TABLE IF EXISTS `syntegratechart_details_value`;");
	$wpdb->query("DROP TABLE IF EXISTS `syntegratechart_filter`;");
}
register_deactivation_hook( __FILE__,'syntegratechart_db_deactivation');
/* register deactivation code end */


/* admin header start */
function editor_admin_heads()
{
	wp_register_style('syntegratechart', plugins_url('css/syntegratechart.css', __FILE__ ), false, '1.0.0');
	wp_register_style('syntegratechart_custom', plugins_url('css/syntegratechart_custom.css', __FILE__ ), false, '1.0.0');
	wp_enqueue_style('syntegratechart');
	wp_enqueue_style('syntegratechart_custom');
	wp_enqueue_script('main_chart', plugins_url( 'js/main_chart.js', __FILE__ ));
}
// add_action('admin_head', 'editor_admin_heads');
add_action( 'admin_enqueue_scripts', 'editor_admin_heads' );
/* admin header end */

/* Front header start */
function front_heads()
{
	wp_enqueue_script('main_chart', plugins_url( 'js/main_chart.js', __FILE__ ));
}
add_action('wp_enqueue_scripts', 'front_heads');
/* front header end */

//Check auth Table Data data empty or not
function syntegratechart_authcheck()
{
	global $wpdb;
	$res = (array)$wpdb->get_row($wpdb->prepare("SELECT * FROM `syntegratechart_auth` WHERE id='1'",''));
	$check = '';
	if($res['username'] != '' || $res['password'] != '' || $res['key'] != '')
	{
		$check = 1;
	}
	return $check;
}


function syntegratechart_authcheck_credentails()
{
	global $wpdb;
	require_once('lib/SforceEnterpriseClient.php');
	$mySforceConnection = new SforceEnterpriseClient();
	$mySoapClient = $mySforceConnection->createConnection(plugins_url('lib/enterprise.wsdl.xml', __FILE__ ));
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
	return $msgerr;
}

function syntegratechart_getAuth()
{
	global $wpdb;
	$res = (array)$wpdb->get_row($wpdb->prepare("SELECT * FROM `syntegratechart_auth` WHERE id='1'",''));
	return $res;
}

function syntegratechart_getdetails($cid)
{
	global $wpdb;
	$res = (array)$wpdb->get_row($wpdb->prepare("SELECT * FROM `syntegratechart_details` WHERE chart_id='".$cid."'",''));
	return $res;
}

function redirect_url($cid,$tab)
{
	global $wpdb;
	?>
	<script>
	window.location.href = "admin.php?page=syntegratechart_chart_add&cid=<?php echo $cid ?>&tab=<?php echo $tab ?>";
	</script>
	<?php
}

function syntegratechart_admin_auth()
{
	require_once('adminauth.php');
}

function syntegratechart_chart_list()
{
	require_once('adminlist.php');
}

function syntegratechart_chart_add()
{
	require_once('chartadd.php');
}

function syntegratechart_frontend($args)
{
	require_once('frontend.php');
}

function syntegratechart_ajax()
{
	require_once('ajax.php');
}
add_action('wp_ajax_syntegratechart_ajax','syntegratechart_ajax');

add_shortcode( 'syntegratechart', 'syntegratechart_frontend' );

/* admin menu start */
function admin_menu_syntegratechart()
{
	add_menu_page("syntegratechart", "Syntegrate Chart", 8, "syntegratechart_admin_auth","syntegratechart_admin_auth",plugins_url('images/icon.png', __FILE__ ));
	add_submenu_page("syntegratechart_admin_auth", "Syntegrate Chart", "Account Settings", 8, "syntegratechart_admin_auth", "syntegratechart_admin_auth");
	if(syntegratechart_authcheck_credentails() == 'w')
	{
		add_submenu_page("syntegratechart_admin_auth", "Syntegrate Chart", "Add Chart", 1, "syntegratechart_chart_add", "syntegratechart_chart_add");
	}
	add_submenu_page("syntegratechart_admin_auth", "Syntegrate Chart", "Manage Charts", 8, "syntegratechart_chart_list", "syntegratechart_chart_list");

}
add_action('admin_menu', 'admin_menu_syntegratechart');
/* admin menu end */
?>