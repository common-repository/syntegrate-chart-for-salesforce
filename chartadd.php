<?php
global $wpdb;
include('config.php');
$cid = $_GET['cid'];
$tab= $_GET['tab'];
$detail = syntegratechart_getdetails($cid);

//Chart Type saved
if(isset($_POST['chart_type']))
{
	if($cid == '')
	{
		$data = array();
		$data['chart_type'] = $_POST['chart_type'];
		$data['table'] = '';
		$data['filter_and_or'] = '';
		$data['related'] = '';
		$data['label'] = '';

		$wpdb->insert( 'syntegratechart_details', $data, $format = null );
		$lastid = mysql_insert_id();
	}
	else
	{
		$wpdb->update( 'syntegratechart_details', array('chart_type'=> $_POST['chart_type']) , array('chart_id' => $cid), $format = null, $where_format = null );
		$lastid = $cid;
	}
	redirect_url($lastid,$_POST['tabid']);
}

//Chart Object saved
if(isset($_POST['tables']))
{
	$wpdb->delete( 'syntegratechart_details_value', array( 'chart_id' => $cid ) );
	foreach($_POST['tables_fields_value'] as $key => $val)
	{
		$wpdb->insert( 'syntegratechart_details_value', array('value'=> $val, 'chart_id'=> $cid), $format = null );
	}


/*******************************************************************
*****************************START CODE*****************************
*******************************************************************/
$res = (array)$wpdb->get_row($wpdb->prepare("SELECT * FROM `syntegratechart_details` WHERE chart_id='".$cid."'",''));
$aa = '';
$res2 = $wpdb->get_results($wpdb->prepare("SELECT * FROM `syntegratechart_details_value` WHERE chart_id='".$cid."'",''));
foreach($res2 as $k => $v)
{
	$aa .= '+'.$v->value;
}
$aa = substr($aa,1);
$aa = explode('+',$aa);
$table	 			 = $res['table'];
$tables_fields_label = $res['label'];
$tables_fields_value = $aa;
$chartt = $res['chart_type'];
if($res['filter_and_or'] != '')
{
	$con_type = $res['filter_and_or'];
}
else
{
	$con_type = 'or';
}

/////////////////////////////////////////
///////////////CODE//////////////////////
////////////////////////////////////////

//label
$str_label = explode("~",$tables_fields_label);
$str_label_one  = explode("^",$str_label[1]);
$label_field 	=	$str_label[0];
$label_table	=	$str_label_one[0];
$label_relation =	$str_label_one[1];

$count_val = count($tables_fields_value);
$count_val = ($count_val)-1;
$count_last = ($count_val)-1;
$val_strtable = '';

for ($x=0; $x <= $count_val; $x++)
{
	//value
	$str_value = explode("~",$tables_fields_value[$x]);
	$str_value_one  = explode("^",$str_value[1]);
	$value_field 	=	$str_value[0];
	$value_table	=	$str_value_one[0];
	$value_relation =	$str_value_one[1];
	$val_strtable .= '*'.$value_table;
}
$val_strtable = explode('*',$val_strtable);
$val_strtable = array_filter($val_strtable);
$val_strtable = array_unique($val_strtable);
$value_table  = $val_strtable[1];

if(count($val_strtable) == 1)
{
$val_str1 = '';
for ($x=0; $x <= $count_val; $x++)
{
	$str_value = explode("~",$tables_fields_value[$x]);
	$str_value_one  = explode("^",$str_value[1]);
	$value_field 	=	$str_value[0];
	$val_str1 .= ','.$value_field;
}
$tval = $val_str1;
$tval = substr($tval,1);
$val1 = explode(',',$tval);
$val1 = array_filter($val1);
$val1ssss = array_unique($val1);

$table_field = 'Id';
try
{
	if($table == $label_table || $table == $value_table)
	{
		if($table == $label_table && $table != $value_table)
		{
			//done
			$val_str1 = '';
			for ($x=0; $x <= $count_val; $x++)
			{
				$str_value = explode("~",$tables_fields_value[$x]);
				$str_value_one  = explode("^",$str_value[1]);
				$value_field 	=	$str_value[0];
				$val_str1 .= ',a.'.$value_field;
			}
			$tval = $val_str1;
			$tval = substr($tval,1);
			$val1 = explode(',',$tval);
			$val1 = array_filter($val1);
			$val1s = array_unique($val1);
			$val1 = implode(',',$val1s);
			$query =  "SELECT b.".$table_field.",".$val1.",b.".$label_field." FROM ".$value_table." a, a.".$table." b";
		}
		else if($table == $value_table && $table != $label_table)
		{
			// echo 2;
			$val_str1 = '';
			for ($x=0; $x <= $count_val; $x++)
			{
				$str_value = explode("~",$tables_fields_value[$x]);
				$str_value_one  = explode("^",$str_value[1]);
				$value_field 	=	$str_value[0];
				$val_str1 .= ','.$value_field;
			}
			$tval = $val_str1;
			$tval = substr($tval,1);
			$val1 = explode(',',$tval);
			$val1 = array_filter($val1);
			$val1 = array_unique($val1);
			$val1 = implode(',',$val1);
			$query = "SELECT b.".$table_field.",".$val1.",a.".$label_field." FROM ".$label_table." a, a.".$table." b";
			}
		else
		{
			// echo 3;
			$val_str = '';
			for ($x=0; $x <= $count_val; $x++)
			{
				$str_value = explode("~",$tables_fields_value[$x]);
				$str_value_one  = explode("^",$str_value[1]);
				$value_field 	=	$str_value[0];
				$val_str .= ','.$value_field;
			}
			$tval = $val_str;
			$tval = substr($tval,1);
			$val1 = explode(',',$tval);
			$val1 = array_filter($val1);
			$val1 = array_unique($val1);
			$val1 = implode(',',$val1);
			//table
			$query =  "SELECT ".$table_field.",".$val1.",".$label_field." FROM ".$table;
		}
	}
	else
	{
		if($label_table == $value_table)
		{
			// echo 4;
			$val_str1 = '';
			for ($x=0; $x <= $count_val; $x++)
			{
				$str_value = explode("~",$tables_fields_value[$x]);
				$str_value_one  = explode("^",$str_value[1]);
				$value_field 	=	$str_value[0];
				$val_str1 .= ',b.'.$value_field;
			}
			$tval = $val_str1;
			$tval = substr($tval,1);
			$val1 = explode(',',$tval);
			$val1 = array_filter($val1);
			$val1 = array_unique($val1);
			$val1 = implode(',',$val1);
			//table,label
			$query =  "SELECT b.".$table_field.", ".$val1." FROM ".$label_table." a, a.".$table." b";
		}
		else
		{
			// echo 5;
			continue;
		}
	}
	function condition_Add($con,$label,$value,$con_type)
	{
		if(is_numeric($value))
		{
			$value1 = $value;
			$value = $value;
		}
		else
		{
			$value1 = $value;
			$value = "'".$value."'";
		}

		if($con == 'Greaterthan')
		{
			$html = " ".$label." > ".$value." ".$con_type."";
		}
		if($con == 'Lessthan')
		{
			$html = " ".$label." < ".$value." ".$con_type."";
		}
		if($con == 'Greaterthanorequalto')
		{
			$html = " ".$label." >= ".$value." ".$con_type."";
		}
		if($con == 'Lessthanorequalto')
		{
			$html = " ".$label." <= ".$value." ".$con_type."";
		}

		if($con == 'Equal')
		{
			$html = " ".$label." = ".$value." ".$con_type."";
		}
		if($con == 'NotEqualTo')
		{
			$html = " ".$label." != ".$value." ".$con_type."";
		}
		if($con == 'StartsWith')
		{
			$html = " ".$label." like '".$value1."_%' ".$con_type."";
		}
		if($con == 'Contains')
		{
			$html = " ".$label." IN (".$value.") ".$con_type."";
		}
		if($con == 'DoesNotContain')
		{
			$html = " ".$label." NOT IN (".$value.") ".$con_type."";
		}
		return $html;
	}

	$condition = '';
	$res2 = $wpdb->get_results($wpdb->prepare("SELECT * FROM `syntegratechart_filter` WHERE chart_id='".$cid."'",''));
	$run = json_decode(json_encode($res2), true);
	$details = count($run);
	if($details > 0)
	{
		$i = 0;
		foreach($run as $k => $detail)
		{
			$str_label 		=   explode("~",$detail['filter_field']);
			$str_label_one  =   explode("^",$str_label[1]);
			$label 	=	$str_label[0];
			$con = $detail['filter'];
			$value = $detail['filter_value'];
			if($i == 0)
			{
				$condition .= ' where';
				$condition .= condition_Add($con,$label,$value,$con_type);
			}
			else if($i == $to)
			{
				$condition .= condition_Add($con,$label,$value,'');
			}
			else
			{
				$condition .= condition_Add($con,$label,$value,$con_type);
			}
		$i++;
		}
		if($con_type == 'and')
		{
			$d = -3;
		}
		else
		{
			$d = -2;
		}
		$condition = substr($condition, 0, $d);
	}

	$query = $query.$condition;
	$response = $mySforceConnection->query($query);
	$response = (array)$response->records;


}
catch(Exception $e)
{

	$response = array('error'=> 'Please refine your field selection and try again.');
}
}
else
{
	$response = array('error'=> 'Please refine your field selection and try again.');
}

$s = json_encode($response);

	$cdate = current_time('mysql');
	$d = array();
	$d['current_date'] = $cdate;
	$d['data'] = $s;
	$d['data_refresh_t'] = $_POST['data_refresh_t'];
	$d['data_refresh_v'] = $_POST['data_refresh_v'];
	$d['related'] = $_POST['related_fields'];
	$d['table'] = $_POST['tables'];
	$d['label'] = $_POST['tables_fields_label'];
	$wpdb->update( 'syntegratechart_details', $d , array('chart_id' => $cid), $format = null, $where_format = null );


	/*******************************************************************
	****************************END CODE***********************************
	*******************************************************************/

	redirect_url($cid,$_POST['tabid']);
}

	//Chart look and feel data save
	if(isset($_POST['height']))
	{
		$res2 = $wpdb->get_results($wpdb->prepare("SELECT * FROM `syntegratechart_settings` WHERE chart_id='".$cid."'",''));
		$run = json_decode(json_encode($res2), true);
		$res1 = count($run);

		$data = array();
		$data['chart_id'] = $cid;
		$data['height'] = $_POST['height'];
		$data['width'] = $_POST['width'];
		$data['title'] = $_POST['title'];
		$data['font_size'] = $_POST['font_size'];
		$data['font_style'] = $_POST['font_style'];
		$data['font_color'] = $_POST['font_color'];
		$data['position'] =	$_POST['position'];
		$data['background_color'] = $_POST['background_color'];
		$data['3d'] = $_POST['3d'];
		$data['orientation'] = $_POST['orientation'];
		$data['legend_position'] = $_POST['legend_position'];
		$data['legend_alignment'] = $_POST['legend_alignment'];
		$data['legend_text_color'] = $_POST['legend_text_color'];
		$data['legend_font_size'] = $_POST['legend_font_size'];
		$data['haxis_position'] = $_POST['haxis_position'];
		$data['vaxis_position'] = $_POST['vaxis_position'];
		$data['haxis_title'] = $_POST['haxis_title'];
		$data['vaxis_title'] = $_POST['vaxis_title'];
		$data['bubble_size'] = $_POST['bubble_size'];
		$data['haxis_angle'] = $_POST['haxis_angle'];

		if($res1 == '')
		{
			$wpdb->insert( 'syntegratechart_settings', $data, $format = null );
		}
		else
		{
			$wpdb->update( 'syntegratechart_settings', $data , array('chart_id' => $cid), $format = null, $where_format = null );
		}
		redirect_url($cid,$_POST['tabid']);
	}

//Update filter data and or
if(isset($_POST['filter_and_or']))
{
	/*******************************************************************
	*****************************START CODE*****************************
	*******************************************************************/
	$res = (array)$wpdb->get_row($wpdb->prepare("SELECT * FROM `syntegratechart_details` WHERE chart_id='".$cid."'",''));

	$aa = '';
	$res2 = $wpdb->get_results($wpdb->prepare("SELECT * FROM `syntegratechart_details_value` WHERE chart_id='".$cid."'",''));
	foreach($res2 as $k => $v)
	{
		$aa .= '+'.$v->value;
	}
	$aa = substr($aa,1);
	$aa = explode('+',$aa);

	$table	 			 = $res['table'];
	$tables_fields_label = $res['label'];
	$tables_fields_value = $aa;
	$chartt = $res['chart_type'];
	if($res['filter_and_or'] != '')
	{
		$con_type = $res['filter_and_or'];
	}
	else
	{
		$con_type = 'or';
	}

	/////////////////////////////////////////
	///////////////CODE//////////////////////
	////////////////////////////////////////

	//label
	$str_label = explode("~",$tables_fields_label);
	$str_label_one  = explode("^",$str_label[1]);
	$label_field 	=	$str_label[0];
	$label_table	=	$str_label_one[0];
	$label_relation =	$str_label_one[1];

	$count_val = count($tables_fields_value);
	$count_val = ($count_val)-1;
	$count_last = ($count_val)-1;
	$val_strtable = '';

	for ($x=0; $x <= $count_val; $x++)
	{
		//value
		$str_value = explode("~",$tables_fields_value[$x]);
		$str_value_one  = explode("^",$str_value[1]);
		$value_field 	=	$str_value[0];
		$value_table	=	$str_value_one[0];
		$value_relation =	$str_value_one[1];
		$val_strtable .= '*'.$value_table;
	}
	$val_strtable = explode('*',$val_strtable);
	$val_strtable = array_filter($val_strtable);
	$val_strtable = array_unique($val_strtable);
	$value_table  = $val_strtable[1];

	if(count($val_strtable) == 1)
	{
	$val_str1 = '';
	for ($x=0; $x <= $count_val; $x++)
	{
		$str_value = explode("~",$tables_fields_value[$x]);
		$str_value_one  = explode("^",$str_value[1]);
		$value_field 	=	$str_value[0];
		$val_str1 .= ','.$value_field;
	}
	$tval = $val_str1;
	$tval = substr($tval,1);
	$val1 = explode(',',$tval);
	$val1 = array_filter($val1);
	$val1ssss = array_unique($val1);

	$table_field = 'Id';
	try
	{
		if($table == $label_table || $table == $value_table)
		{
			if($table == $label_table && $table != $value_table)
			{
				//done
				$val_str1 = '';
				for ($x=0; $x <= $count_val; $x++)
				{
					$str_value = explode("~",$tables_fields_value[$x]);
					$str_value_one  = explode("^",$str_value[1]);
					$value_field 	=	$str_value[0];
					$val_str1 .= ',a.'.$value_field;
				}
				$tval = $val_str1;
				$tval = substr($tval,1);
				$val1 = explode(',',$tval);
				$val1 = array_filter($val1);
				$val1s = array_unique($val1);
				$val1 = implode(',',$val1s);
				$query =  "SELECT b.".$table_field.",".$val1.",b.".$label_field." FROM ".$value_table." a, a.".$table." b";
			}
			else if($table == $value_table && $table != $label_table)
			{
				// echo 2;
				$val_str1 = '';
				for ($x=0; $x <= $count_val; $x++)
				{
					$str_value = explode("~",$tables_fields_value[$x]);
					$str_value_one  = explode("^",$str_value[1]);
					$value_field 	=	$str_value[0];
					$val_str1 .= ','.$value_field;
				}
				$tval = $val_str1;
				$tval = substr($tval,1);
				$val1 = explode(',',$tval);
				$val1 = array_filter($val1);
				$val1 = array_unique($val1);
				$val1 = implode(',',$val1);
				$query = "SELECT b.".$table_field.",".$val1.",a.".$label_field." FROM ".$label_table." a, a.".$table." b";
				}
			else
			{
				// echo 3;
				$val_str = '';
				for ($x=0; $x <= $count_val; $x++)
				{
					$str_value = explode("~",$tables_fields_value[$x]);
					$str_value_one  = explode("^",$str_value[1]);
					$value_field 	=	$str_value[0];
					$val_str .= ','.$value_field;
				}
				$tval = $val_str;
				$tval = substr($tval,1);
				$val1 = explode(',',$tval);
				$val1 = array_filter($val1);
				$val1 = array_unique($val1);
				$val1 = implode(',',$val1);
				//table
				$query =  "SELECT ".$table_field.",".$val1.",".$label_field." FROM ".$table;
			}
		}
		else
		{
			if($label_table == $value_table)
			{
				// echo 4;
				$val_str1 = '';
				for ($x=0; $x <= $count_val; $x++)
				{
					$str_value = explode("~",$tables_fields_value[$x]);
					$str_value_one  = explode("^",$str_value[1]);
					$value_field 	=	$str_value[0];
					$val_str1 .= ',b.'.$value_field;
				}
				$tval = $val_str1;
				$tval = substr($tval,1);
				$val1 = explode(',',$tval);
				$val1 = array_filter($val1);
				$val1 = array_unique($val1);
				$val1 = implode(',',$val1);
				//table,label
				$query =  "SELECT b.".$table_field.", ".$val1." FROM ".$label_table." a, a.".$table." b";
			}
			else
			{
				// echo 5;
				continue;
			}
		}
		function condition_Add($con,$label,$value,$con_type)
		{
			if(is_numeric($value))
			{
				$value1 = $value;
				$value = $value;
			}
			else
			{
				$value1 = $value;
				$value = "'".$value."'";
			}

			if($con == 'Greaterthan')
			{
				$html = " ".$label." > ".$value." ".$con_type."";
			}
			if($con == 'Lessthan')
			{
				$html = " ".$label." < ".$value." ".$con_type."";
			}
			if($con == 'Greaterthanorequalto')
			{
				$html = " ".$label." >= ".$value." ".$con_type."";
			}
			if($con == 'Lessthanorequalto')
			{
				$html = " ".$label." <= ".$value." ".$con_type."";
			}

			if($con == 'Equal')
			{
				$html = " ".$label." = ".$value." ".$con_type."";
			}
			if($con == 'NotEqualTo')
			{
				$html = " ".$label." != ".$value." ".$con_type."";
			}
			if($con == 'StartsWith')
			{
				$html = " ".$label." like '".$value1."_%' ".$con_type."";
			}
			if($con == 'Contains')
			{
				$html = " ".$label." IN (".$value.") ".$con_type."";
			}
			if($con == 'DoesNotContain')
			{
				$html = " ".$label." NOT IN (".$value.") ".$con_type."";
			}
			return $html;
		}




		$condition = '';
		$res2 = $wpdb->get_results($wpdb->prepare("SELECT * FROM `syntegratechart_filter` WHERE chart_id='".$cid."'",''));
		$run = json_decode(json_encode($res2), true);
		$details = count($run);
		if($details > 0)
		{
			$i = 0;
			foreach($run as $k => $detail)
			{
				$str_label 		=   explode("~",$detail['filter_field']);
				$str_label_one  =   explode("^",$str_label[1]);
				$label 	=	$str_label[0];
				$con = $detail['filter'];
				$value = $detail['filter_value'];
				if($i == 0)
				{
					$condition .= ' where';
					$condition .= condition_Add($con,$label,$value,$con_type);
				}
				else if($i == $to)
				{
					$condition .= condition_Add($con,$label,$value,'');
				}
				else
				{
					$condition .= condition_Add($con,$label,$value,$con_type);
				}
			$i++;
			}
			if($con_type == 'and')
			{
				$d = -3;
			}
			else
			{
				$d = -2;
			}
			$condition = substr($condition, 0, $d);
		}

		$query = $query.$condition;
		$response = $mySforceConnection->query($query);
		$response = (array)$response->records;


	}
	catch(Exception $e)
	{

		$response = array('error'=> 'Please refine your field selection and try again.');
	}
	}
	else
	{
		$response = array('error'=> 'Please refine your field selection and try again.');
	}

	$s = json_encode($response);

	/*******************************************************************
	****************************END CODE***********************************
	*******************************************************************/

	$cdatex = current_time('mysql');
	$wpdb->update( 'syntegratechart_details', array('data'=> $s ,'current_date' => $cdatex,'filter_and_or' => $_POST['filter_and_or']) , array('chart_id' => $cid), $format = null, $where_format = null );


	redirect_url($cid,$_POST['tabid']);
}

if($detail['table'] != '')
{
	$tname = $detail['table'];
}
else
{
	$tname = "Account";
}

//Message
$msg = '';
$msg[1] = 'A critical error has occurred. An unexpected error has occurred ';
if($_GET['msg'] != '')
{
	?>
	<br />
	<div class="updated" style="margin:5px 0px 2px;border-left:4px solid #F54B1D;width:96%" id="message">
		<p><strong><?php echo $msg[$_GET['msg']]; ?></strong></p>
	</div>
	<?php
}
if($cid != '')
{
?>
	<br />
	<div class="updated" style="margin:5px 0px 2px;width:96%" id="message">
		<p><strong>Shortcode for post/page :&nbsp; [syntegratechart cid="<?php echo $cid; ?>"]</strong></p>
	</div>

<?php
}

if($tab == 't1' || $tab == '')
{
	$strr = 1;
}
else
{
	$strr = str_replace('t','',$tab);
}
$strr = $strr;
$strr1 = 'frm'.$strr
?>
<script>
function imgShow(s)
{
	jQuery('#chart_type').val(s);
	jQuery("#cimagestyle img").each(function() {
		jQuery('#cimagestyle img').css('border','2px solid #ccc');
	});
	jQuery('#c'+s).css('border','3px solid green');
}

</script>
<h1>Syntegrate Chart</h1>
<div id="syntegratechart_main">
	<ul id="tb">
		<li <?php if($tab == 't1' || $tab == ''){ echo 'class="tabActive"';}else { echo 'onclick=frmsubmit("'.$strr1.'","click","t1")';} ?>>Step 1: Start</li>
		<li <?php if($tab == 't2'){ echo 'class="tabActive"';}else { echo 'onclick=frmsubmit("'.$strr1.'","click","t2")';} ?>>Step 2: Pick Your Data</li>
		<li <?php if($tab == 't3'){ echo 'class="tabActive"';}else { echo 'onclick=frmsubmit("'.$strr1.'","click","t3")';} ?>>Step 3: Filter Your Data (Optional)</li>
		<li <?php if($tab == 't4'){ echo 'class="tabActive"';}else { echo 'onclick=frmsubmit("'.$strr1.'","click","t4")';} ?>>Step 4: Look and feel (Optional)</li>
	</ul>

	<div id="tabs_content" style="margin:50px 10px;">
		<?php if($tab == 't1' || $tab == ''){ ?>
		<!----------TAB 1 Start-------------->
		<div id='t1' class="tbs" <?php if($tab == 't1' || $tab == ''){ echo 'style="display:block"';} ?>>
			<h2>Welcome to Syntegrate Chart!</h2>
			<span style="color:red" id="selcharterr"></span>
			<style>
			#cimagestyle img
			{
				width:150px;
				margin:10px;
				padding:10px;
				border:2px solid #ccc;
				border-radius:2px;
				cursor:pointer;
			}
			</style>
			<div id='cimagestyle'>
				<img <?php if($detail['chart_type'] == 'Pie'){ echo 'style="border:3px solid green;"'; }?> onclick="imgShow('Pie')" id='cPie' src="<?php echo plugins_url('chart_images/Pie.png', __FILE__ );?>">
				<img <?php if($detail['chart_type'] == 'Bar'){ echo 'style="border:3px solid green;"'; }?> onclick="imgShow('Bar')" id='cBar'  src="<?php echo plugins_url('chart_images/Bar.png', __FILE__ );?>">
				<img <?php if($detail['chart_type'] == 'Bubble'){ echo 'style="border:3px solid green;"'; }?> onclick="imgShow('Bubble')" id='cBubble'  src="<?php echo plugins_url('chart_images/Bubble.png', __FILE__ );?>">
				<img <?php if($detail['chart_type'] == 'Spline'){ echo 'style="border:3px solid green;"'; }?> onclick="imgShow('Spline')" id='cSpline'  src="<?php echo plugins_url('chart_images/Spline.png', __FILE__ );?>">
				<img <?php if($detail['chart_type'] == 'Column'){ echo 'style="border:3px solid green;"'; }?> onclick="imgShow('Column')" id='cColumn' src="<?php echo plugins_url('chart_images/Column.png', __FILE__ );?>">
				<img <?php if($detail['chart_type'] == 'Doughnut'){ echo 'style="border:3px solid green;"'; }?> onclick="imgShow('Doughnut')" id='cDoughnut'  src="<?php echo plugins_url('chart_images/Doughnut.png', __FILE__ );?>">
				<img <?php if($detail['chart_type'] == 'Line'){ echo 'style="border:3px solid green;"'; }?> onclick="imgShow('Line')" id='cLine'  src="<?php echo plugins_url('chart_images/Line.png', __FILE__ );?>">
			</div>


			<form action="" method="post" id="frm1" style='display:none'>
				<p>Please select a Chart type:</p>
					<input type='hidden' name='tabid' id='tabid' value='<?php echo $tab; ?>'>
					<input type='hidden' name="chart_type" id="chart_type" value='<?php if($detail['chart_type'] != ''){ echo $detail['chart_type']; }else{ echo '0'; }?>'>
					<br /><br />
			</form>


		</div>
		<!----------TAB 1 End-------------->
		<?php } ?>



		<?php if($tab == 't2'){ ?>
		<script>
		var common;
		function fetchlabel()
		{
			var table 		  = jQuery("#tables").val();
			if(table != '-')
			{
				if(jQuery("#related_fields").is(':checked'))
				{
					var check = 1;
				}else{
					var check = 0;
				}
				jQuery("#loader").html('<img src="<?php echo plugins_url('images/loader.gif', __FILE__ );?>" >');
				jQuery.ajax({
					url:"<?php admin_url(); ?>admin-ajax.php?&mode=tables_fields_label&id="+table+"&related="+check+"&cid=<?php echo $cid; ?>",
					data:{action:'syntegratechart_ajax'},
					type:'POST',
					async:false,
					success:function(result){
						var spt = result.split("#*#");
						jQuery("#tables_fields_label").html(spt[0]);
						jQuery("#tables_fields_value").html(spt[1]);
						jQuery("#loader").html('');
					}
				});
			}
		}

		function fetchdata(ctype)
		{
			jQuery("#chart_div").css("display","none");
			var table 		  		 = jQuery("#tables").val();
			var tables_fields_label  = jQuery("#tables_fields_label").val();
			var tables_fields_value  = jQuery("#tables_fields_value").val();
			jQuery("#loader_chart").css("display","block");
			jQuery("#loader_chart").html('<img src="<?php echo plugins_url('images/loader_chart.gif', __FILE__ );?>" >');
			jQuery.ajax({
				url: '<?php admin_url(); ?>admin-ajax.php?mode=fetchdata&type='+ctype+'&cid=<?php echo $cid; ?>',
				type:'POST',
				data: jQuery('#frm2').serialize(),
				async:false,
				success:function(result){
				result=result.substr(0,(result.length-1));
					var data=result.split('*');
					if(data[0] == '')
					{
						common=data[1];
					}
					else
					{
						common=data[1];
					}
					if(ctype == 'ch')
					{
						if(data[0] != '')
						{
							jQuery("#chart_div").html(common);
						}
						else
						{
							jQuery('#cl').trigger('click');
						}
					}
					else
					{
						jQuery("#chart_div").html(common);
					}
					jQuery("#loader_chart").html('');
					jQuery("#loader_chart").css("display","none");
					jQuery("#chart_div").css("display","block");
				}
			});
			return false;
		}
		//Ready functions
		jQuery( document ).ready(function() {
		  fetchlabel();
		});
		</script>
		<a id="cl" style="display:none;" href="javascript:void(0)">s</a>
		<script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(function () {
			var table 		  = jQuery("#tables").val();
			if(table != '-')
			{
				jQuery("#cl").click(drawChart);
			}
		});

		function drawChart() {
		var demo=[];
		var extra=common.split('@');
		for(var i=0;i<extra.length;i++)
		{
			var array = eval(extra[i]);
			demo.push(array);
		}
		var data = google.visualization.arrayToDataTable(demo);
		<?php
		$chart_type = $detail['chart_type'];
		if(in_array($chart_type,array('Pie','Doughnut')))
		{
			if($chart_type == "Doughnut")
			{
				?>
				var options = {
					'title':'',
					'width':400,
					legend: { position: "none" },
					'height':300,
					backgroundColor: "transparent",
					pieHole: 0.4
				};
				<?php
			}
			else
			{
				?>
				var options = {
					'title':'',
					'width':400,
					legend: { position: "none" },
					'height':300,
					backgroundColor: "transparent",
				};
				<?php
			}
			?>
			var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
			<?php
		}
		else
		{
			?>
			var options = {
				width: 500,
				height: 400,
				legend: { position: 'top', maxLines: 5 },
				bar: { groupWidth: '50%' },
				legend: { position: "none" },
				backgroundColor: "transparent",
				sizeAxis: {maxSize: 5}
			};
			<?php
			if($chart_type == "Spline")
			{
				$chrt = "Area";
			}
			else
			{
				$chrt = $chart_type;
			}
			?>
			var chart = new google.visualization.<?php echo $chrt; ?>Chart(document.getElementById('chart_div'));
			<?php
		}
		?>
		chart.draw(data, options);
		}

		</script>
		<!----------TAB 2 Start-------------->
		<div id='t2' class="tbs" <?php if($tab == 't2'){ echo 'style="display:block"';} ?>>
			<div class='sleft' >
			<form action="" method="post" id="frm2">
			<input type="hidden" name="action" value="syntegratechart_ajax">
				<input type='hidden' name='tabid' id='tabid' value='<?php echo $tab; ?>'>
				Select the Salesforce object you'd like to report on: <br />
				<select id="tables" name="tables" style="float:left" onchange="fetchlabel()">
				<?php
				$tables=$mySforceConnection->describeGlobal();
				echo '<optgroup label="--STANDARD OBJECTS--">';
				foreach($tables->sobjects as $key => $val)
				{
					if($val->custom == '')
					{
						if($detail['table'] == $val->name)
						{
						?>
							<option selected="selected" value="<?php echo $val->name;?>" ><?php echo $val->label;?> </option>
						<?php
						}else
						{
						?>
							<option  value="<?php echo $val->name;?>" ><?php echo $val->label;?> </option>
						<?php
						}
					}
				}
				echo '</optgroup>';
				echo '<optgroup label="--CUSTOM OBJECTS--">';
				foreach($tables->sobjects as $key => $val)
				{
					if($val->custom != '')
					{
						if($detail['table'] == $val->name)
						{
						?>
							<option selected="selected" value="<?php echo $val->name;?>" ><?php echo $val->label;?> </option>
						<?php
						}
						else
						{
						?>
							<option  value="<?php echo $val->name;?>" ><?php echo $val->label;?> </option>
						<?php
						}
					}
				}
				echo '</optgroup>';
				?>
				</select>&nbsp;<span id='loader' style="vertical-align: middle; display: block; float: left;margin:5px;width:16px;height:16px;"></span><input <?php if($detail['related'] == '1'){ echo 'checked="checked"'; }?> style="margin:5px;" onclick="fetchlabel()" id="related_fields" type="checkbox" name="related_fields" value="1"> Show Related Fields
				<br /><br />
				<?php
				if(in_array($detail['chart_type'],array('Pie','Doughnut')))
				{
				?>
				Select your slice label field:
				<?php }else{ ?>
				Select your x-axis value:
				<?php
				}
				?>
				<br />
				<select id="tables_fields_label" name="tables_fields_label">
				<option value="-">----Select----</option>
				</select>

				<br /><br />
				<?php
				if(in_array($detail['chart_type'],array('Pie','Doughnut')))
				{
				?>
				Select your slice value field:
				<?php }else{ ?>
				Select your y-axis value(s):
				<?php
				}
				?>
				<br />
				<?php
				if(!in_array($detail['chart_type'],array('Pie','Doughnut')))
				{
				?>
				<select id="tables_fields_value" name="tables_fields_value[]" multiple>
				<?php
				}else
				{
				?>
				<select id="tables_fields_value" name="tables_fields_value[]">
				<?php
				}
				?>
				<option value="-">----Select----</option>
				</select>
				<br /><br />
				How often would you like this data to be updated?<br />
				Every <input type="text" value="<?php if($detail['data_refresh_v'] != ''){ echo $detail['data_refresh_v']; }else{ echo 1;} ?>" name="data_refresh_v" style="margin: 10px 0px; vertical-align: middle; height: 29px; width: 60px;">&nbsp;
				<select id="data_refresh_t" name="data_refresh_t" >
					<option value="minutes" <?php if($detail['data_refresh_t'] == 'minutes'){ echo 'selected="selected"';} ?> >Minute(s)</option>
					<option value="hours" <?php if($detail['data_refresh_t'] == 'hours'){ echo 'selected="selected"';} ?> >Hour(s)</option>
					<option value="days" <?php if($detail['data_refresh_t'] == 'days'){ echo 'selected="selected"';} ?> >Day(s)</option>
					<option value="weeks" <?php if($detail['data_refresh_t']  == 'weeks'){ echo 'selected="selected"';} ?>  >Week(s)</option>
				</select>

				<br /><br />
				<input type="button" onclick="return fetchdata('c')" name="view" value="Preview Data">
				<input type="button" onclick="return fetchdata('ch')" name="view" value="Preview <?php echo $detail['chart_type']; ?> Chart">
				<input type="button"  onclick="frmsubmit('frm2','submit','t2')"   name="chartdatasave" value="Save">
			</form>
			<br />
			</div>
			<div class='sright'>
				<span id='loader_chart' style="float: right; display: block; width: 300px; margin: 100px 0px;"></span>
				<span id="chart_div" style=' overflow-y: hidden; width: 565px;'></span>
			</div>
		</div>
		<!----------TAB 2 End-------------->
		<?php } ?>

		<?php if($tab == 't3'){ ?>
		<script>
		var common;
		function fetchdata(ctype)
		{
			jQuery("#chart_div").css("display","none");
			jQuery("#loader_chart").css("display","block");
			jQuery("#loader_chart").html('<img src="<?php echo plugins_url('images/loader_chart.gif', __FILE__ );?>" >');
			jQuery.ajax({
				url: '<?php admin_url(); ?>admin-ajax.php?mode=fetchdatatab4&type='+ctype+'&cid=<?php echo $cid; ?>',
				data:{action:'syntegratechart_ajax'},
				type:'POST',
				async:false,
				success:function(result){
				result=result.substr(0,(result.length-1));
					var data=result.split('*');
					if(data[0] == '')
					{
						common=data[1];
					}
					else
					{
						common=data[1];
					}
					if(ctype == 'ch')
					{
						if(data[0] != '')
						{
							jQuery("#chart_div").html(common);
						}
						else
						{
							jQuery('#cl').trigger('click');
						}
					}
					else
					{
						jQuery("#chart_div").html(common);
					}
					jQuery("#loader_chart").html('');
					jQuery("#loader_chart").css("display","none");
					jQuery("#chart_div").css("display","block");
				}
			});
			return false;
		}
		</script>
		<a id="cl" style="display:none;" href="javascript:void(0)">s</a>
		<script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(function () {
			var table 		  = jQuery("#tables").val();
			if(table != '-')
			{
				jQuery("#cl").click(drawChart);
			}
		});

		function drawChart() {
		var demo=[];
		var extra=common.split('@');
		for(var i=0;i<extra.length;i++)
		{
			var array = eval(extra[i]);
			demo.push(array);
		}
		var data = google.visualization.arrayToDataTable(demo);
		<?php
		$chart_type = $detail['chart_type'];
		if(in_array($chart_type,array('Pie','Doughnut')))
		{
			if($chart_type == "Doughnut")
			{
				?>
				var options = {
					'title':'',
					'width':400,
					legend: { position: "none" },
					'height':300,
					backgroundColor: "transparent",
					pieHole: 0.4
				};
				<?php
			}
			else
			{
				?>
				var options = {
					'title':'',
					'width':400,
					legend: { position: "none" },
					'height':300,
					backgroundColor: "transparent",
				};
				<?php
			}
			?>
			var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
			<?php
		}
		else
		{
			?>
			var options = {
				width: 500,
				height: 400,
				legend: { position: 'top', maxLines: 5 },
				bar: { groupWidth: '50%' },
				legend: { position: "none" },
				backgroundColor: "transparent",
				sizeAxis: {maxSize: 5}
			};
			<?php
			if($chart_type == "Spline")
			{
				$chrt = "Area";
			}
			else
			{
				$chrt = $chart_type;
			}
			?>
			var chart = new google.visualization.<?php echo $chrt; ?>Chart(document.getElementById('chart_div'));
			<?php
		}
		?>
		chart.draw(data, options);
		}

		</script>
		<script>
		function filteradd()
		{
			var label 		  = jQuery("#tables_fields_labels_filter").val();
			var filtername 		  = jQuery("#chart_filter").val();
			var filterval 		  = jQuery("#val_filter").val();
			jQuery("#loader1").html('<img src="<?php echo plugins_url('images/loader.gif', __FILE__ );?>" >');
			jQuery.ajax({
				url:"<?php admin_url(); ?>admin-ajax.php?mode=filteradd&label="+label+"&filter="+filtername+"&filterval="+filterval+"&cid=<?php echo $cid; ?>",
				data:{action:'syntegratechart_ajax'},
				type:'POST',
				async:false,
				success:function(result){
					jQuery("#filter_datas").html('');
					jQuery("#filter_datas").html(result);
					jQuery("#loader1").html('');
				}
			});
		}
		// [soapType] => xsd:int
		function filter_delete(id)
		{
			jQuery.ajax({
				url:"<?php admin_url(); ?>admin-ajax.php?mode=filterdel&id="+id,
				data:{action:'syntegratechart_ajax'},
				type:'POST',
				async:false,
				success:function(result){
					jQuery("#filter_"+id).css('display','none');
				}
			});
		}
		</script>
		<!----------TAB 3 Start-------------->
		<div id='t3' class="tbs" <?php if($tab == 't3'){ echo 'style="display:block"';} ?>>
		<script>
		function filter_condition()
		{
			var label 		  = jQuery("#tables_fields_labels_filter").val();
			jQuery.ajax({
				url:"<?php admin_url(); ?>admin-ajax.php?mode=filter_condition&label="+label,
				data:{action:'syntegratechart_ajax'},
				type:'POST',
				async:false,
				success:function(result){
					 // alert(result);
					jQuery("#chart_filter").html(result);
				}
			});
		}
		jQuery( document ).ready(function() {
		  filter_condition();
		});

		</script>
			<div class='sleft'>
			<span>Add any filters you'd like to apply to your data:</span><br />
			<?php
			$tables_fields=$mySforceConnection->describeSObject($tname);
			?>
			<select id="tables_fields_labels_filter" style='width:120px;' name="tables_fields_labels" onchange="filter_condition()">
			<?php
				foreach($tables_fields->fields as $key => $val)
				{
					if($val->name != 'Id')
					{
					?>
						<option value="<?php echo $val->name.'~'.$tname.'^';?>" ><?php echo $val->label;?> </option>
					<?php
					}
				}
				if($related == 1)
				{
					echo '<optgroup label="----Related Fields----">';
					if(count($tables_fields->childRelationships) > 0)
					{
						foreach($tables_fields->childRelationships as $keys => $vals)
						{
							$tables_fieldss=$mySforceConnection->describeSObject($vals->childSObject);
							foreach($tables_fieldss->fields as $keya => $vala)
							{
								if($vala->name != 'Id')
								{
									?>
										<option value="<?php echo $vala->name.'~'.$vals->childSObject.'^'.$vals->field;?>" ><?php echo $vala->label.' ('.$vals->childSObject.')';?> </option>
									<?php
								}
							}
						}
					}
					echo '</optgroup>';
				}
				?>
			</select>&nbsp; &nbsp;
			<select name="chart_filter" id="chart_filter" style="width:100px;">
				<option value="-1">--select--</option>
			</select>&nbsp; &nbsp;
			<input type="text" name="val_filter" id="val_filter" style="width:100px;" value="">&nbsp; &nbsp;
			<input type="button" onclick="return filteradd();" name="chartasdd" value="Add Filter">&nbsp; <span id='loader1' style="vertical-align:middle"></span>
			<br /><br />
			<form action="" method="post" id="frm3">
			<input type='hidden' name='tabid' id='tabid' value='<?php echo $tab; ?>'>
			<input type="radio" checked="checked" <?php if($detail['filter_and_or'] == 'and'){ echo 'checked="checked"';} ?> name="filter_and_or" value="and"> AND
			<input type="radio" <?php if($detail['filter_and_or'] == 'or'){ echo 'checked="checked"';} ?> name="filter_and_or" value="or"> OR
			 &nbsp; <input type="submit" name="filter_add" onclick="frmsubmit('frm3','submit','t3')"  value="save">
			</form>

			<br />

			<span id="filter_datas">
			<?php
			$html = '';
			$res2 = $wpdb->get_results($wpdb->prepare("SELECT * FROM `syntegratechart_filter` WHERE chart_id='".$cid."'",''));
			$run = json_decode(json_encode($res2), true);
			$detailss = count($run);

			if($detailss > 0)
			{
			?>
				<table border="0" style="border:2px solid #257AB6;width:98%;" cellpadding="0" cellspacing="2">
				<tr style="background:#257AB6;color:#fff;">
				<th>Name</th><th>Filter</th><th>Value</th><th>Action</th></tr>
				<tbody>
				<?php
				foreach($run as $k => $details)
				{
					$str_label 		=   explode("~",$details['filter_field']);
					$str_label_one  =   explode("^",$str_label[1]);
					$label_field 	=	$str_label[0];
					$html .= '<tr id="filter_'.$details['id'].'"><td>'.$label_field.'</td><td>'.$details['filter'].'</td><td>'.$details['filter_value'].'</td><td  align="center"><a href="javascript:void(0)"  onclick=filter_delete('.$details['id'].') ><img src="https://cdn1.iconfinder.com/data/icons/tiny-icons/delete.png"></a></td></tr>';
				}
				echo $html;
				?>
				</tbody>
				</table>
			<?php
			}
			?>
			</span>
			<br />

			<input type="button" onclick="return fetchdata('c')" name="view" value="Preview Data">
			<input type="button" onclick="return fetchdata('ch')" name="view" value="Preview <?php echo $detail['chart_type']; ?> Chart">
			<br /><br />


			</div>
			<div class='sright'>
				<span id='loader_chart' style="float: right; display: block; width: 300px; margin: 100px 0px;"></span>
				<span id="chart_div" style=' overflow-y: hidden; width: 455px;'></span>
			</div>
		</div>
		<!----------TAB 3 End-------------->
		<?php } ?>











		<?php if($tab == 't4'){ ?>
		<!----------TAB 4 Start-------------->
		<script>
		var common;
		function fetchdata(ctype)
		{
			jQuery("#chart_div").css("display","none");
			jQuery("#loader_chart").css("display","block");
			jQuery("#loader_chart").html('<img src="<?php echo plugins_url('images/loader_chart.gif', __FILE__ );?>" >');
			jQuery.ajax({
				url: '<?php admin_url(); ?>admin-ajax.php?mode=fetchdatatab4&type='+ctype+'&cid=<?php echo $cid; ?>',
				async:false,
				data:{action:'syntegratechart_ajax'},
				type:'POST',
				success:function(result){
				result=result.substr(0,(result.length-1));
					var data=result.split('*');
					if(data[0] == '')
					{
						common=data[1];
					}
					else
					{
						common=data[1];
					}
					if(ctype == 'ch')
					{
						if(data[0] != '')
						{
							jQuery("#chart_div").html(common);
						}
						else
						{
							jQuery('#cl').trigger('click');
						}
					}
					else
					{
						jQuery("#chart_div").html(common);
					}
					jQuery("#loader_chart").html('');
					jQuery("#loader_chart").css("display","none");
					jQuery("#chart_div").css("display","block");
				}
			});
			return false;
		}
		</script>
		<a id="cl" style="display:none;" href="javascript:void(0)">s</a>
		<script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(function () {
			var table 		  = jQuery("#tables").val();
			if(table != '-')
			{
				jQuery("#cl").click(drawChart);
			}
		});

		function drawChart() {
			var height 				= document.getElementById('height').value;
			var width 				= document.getElementById('width').value;
			var title 				= document.getElementById('title').value;
			var font_size 			= document.getElementById('font_size').value;
			var font_style 			= document.getElementById('font_style').value;
			var font_color 			= document.getElementById('font_color').value;
			var background_color 	= document.getElementById('background_color').value;
			var position 			= document.getElementById('position').value;
			var orientation 		= document.getElementById('orientation').value;
			var legend_position 	= document.getElementById('legend_position').value;
			var legend_alignment 	= document.getElementById('legend_alignment').value;
			var legend_text_color 	= document.getElementById('legend_text_color').value;
			var legend_font_size 	= document.getElementById('legend_font_size').value;
			var threed = jQuery('input[name=3d]:checked').map(function()
			{
				return jQuery(this).val();
			}).get();

			var haxis_title 	= document.getElementById('haxis_title').value;
			var haxis_position 	= document.getElementById('haxis_position').value;
			var vaxis_title 	= document.getElementById('vaxis_title').value;
			var vaxis_position 	= document.getElementById('vaxis_position').value;
			var haxis_angle 	= document.getElementById('haxis_angle').value;




			var demo=[];
			var extra=common.split('@');
			for(var i=0;i<extra.length;i++)
			{
				var array = eval(extra[i]);
				demo.push(array);
			}
			var data = google.visualization.arrayToDataTable(demo);

			var  fsb = 'false';
			var  fsi = 'false';
			if(font_style == 'Bold' || font_style == 'Italic')
			{
				if(font_style == 'Bold')
				{
					fsb = 'true';
				}
				else if(font_style == 'Italic')
				{
					fsi = 'true';
				}
			}
			<?php
			$chart_type = $detail['chart_type'];
			if(in_array($chart_type,array('Pie','Doughnut')))
			{
				?>
				var options =
				{
					title:title,
					width:width,
					height:height,
					backgroundColor:background_color,
					legend : {
						position: legend_position,
						alignment: legend_alignment,
						textStyle: {
							color: legend_text_color,
							fontSize: legend_font_size
							}
						},
					titleTextStyle: {
							color: font_color,
							fontName: 'arail',
							fontSize: font_size,
							bold: fsb,
							italic: fsi
						},
					is3D: threed
				};
				var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
				<?php
			}
			else
			{
			?>
				var options =
					{
						legend : {
								position: legend_position,
								alignment: legend_alignment,
							textStyle: {
									color: legend_text_color,
									fontSize:legend_font_size
								}
						},
						orientation:orientation,
						title:title,
						titlePosition: position,
						hAxis: {
								textPosition: haxis_position,
								slantedText :true,
								slantedTextAngle:haxis_angle,
								title:haxis_title
							},
						vAxis: {
								textPosition: vaxis_position,
								title: vaxis_title
							},
						width:width,
						height:height,
						titleTextStyle: {
								color: font_color,
								fontName: 'arail',
								fontSize: font_size,
								bold: fsb,
								italic: fsi
							},
						backgroundColor:background_color
					};
				<?php
				if($chart_type == "Spline")
				{
					$chrt = "Area";
				}
				else
				{
					$chrt = $chart_type;
				}
				?>
				var chart = new google.visualization.<?php echo $chrt; ?>Chart(document.getElementById('chart_div'));
				<?php
			}
			?>
			chart.draw(data, options);
		}

		</script>
		<div id='t4' class="tbs" <?php if($tab == 't4'){ echo 'style="display:block"';} ?>>
			<div class='sleft'  style="width:50%">
			<?php
			$res12 = (array)$wpdb->get_row($wpdb->prepare("SELECT * FROM `syntegratechart_settings` WHERE chart_id='".$cid."'",''));
			?>
			<h2>Customize the look and feel of your <?php echo $chrt; ?> chart:</h2>
			<p>
			<form action="" method="post" id="frm4">
			<input type='hidden' name='tabid' id='tabid' value='<?php echo $tab; ?>'>
			<table>
				<tbody>
					<tr class="subHeadingTR">
						<td colspan="2"><b>Dimensions</b></td>
					</tr>
					<tr>
						<td style="width:150px;">Height (px):</td>
						<td class="style2"><input type="text" value="<?php echo $res12['height']; ?>" name="height" id="height"></td>
					</tr>
					<tr>
						<td>Width (px):</td>
						<td class="style2"><input type="text" value="<?php echo $res12['width']; ?>" name="width" id="width"></td>
					</tr>
					<tr class="subHeadingTR">
						<td colspan="2"><br /><b>Title Options</b></td>
					</tr>
					<tr>
						<td>Title:</td>
						<td class="style2"><input type="text" value="<?php echo $res12['title']; ?>" name="title" id="title"></td>
					</tr>
					<tr>
						<td>Font size:</td>
						<td><input type="text" value="<?php echo $res12['font_size']; ?>" name="font_size" id="font_size"></td>
					</tr>
					<tr>
						<td>Font style:</td>
						<td>
						<select name="font_style" id="font_style">
							<option <?php if($res12['font_style'] == 'Regular'){ echo 'selected="selected"';} ?> value="Regular">Regular</option>
							<option <?php if($res12['font_style'] == 'Bold'){ echo 'selected="selected"';} ?> value="Bold">Bold</option>
							<option <?php if($res12['font_style'] == 'Italic'){ echo 'selected="selected"';} ?> value="Italic">Italic</option>
						</select></td>
					</tr>
					<tr>
						<td>Font Color:</td>
						<td><input type="text" value="<?php echo $res12['font_color']; ?>" name="font_color" id="font_color"></td>
					</tr>
					<tr>
						<td>Background Color:</td>
						<td><input type="text" value="<?php echo $res12['background_color']; ?>" name="background_color" id="background_color"></td>
					</tr>
					<tr>
						<td>Position:</td>
						<td>
							<select name="position" id="position">
								<option <?php if($res12['position'] == 'in'){ echo 'selected="selected"';} ?> value="in">In</option>
								<option <?php if($res12['position'] == 'out'){ echo 'selected="selected"';} ?> value="out">Out</option>
							</select>
						</td>
					</tr>
					<tr>
						<td style="width:150px;">Orientation:</td>
						<td>
						<select name="orientation" id="orientation">
							<option <?php if($res12['orientation'] == 'horizontal'){ echo 'selected="selected"';} ?> value="horizontal">Horizontal</option>
							<option <?php if($res12['orientation'] == 'vertical'){ echo 'selected="selected"';} ?> value="vertical">vertical</option>
						</select>
						</td>
					</tr>
					<tr>
						<td style="width:150px;">X-axis label angle:</td>
						<td>
						<select name="haxis_angle" id="haxis_angle">
						<option <?php if($res12['haxis_angle'] == '0'){ echo 'selected="selected"';} ?> value="0">0</option>
						<option <?php if($res12['haxis_angle'] == '45'){ echo 'selected="selected"';} ?>  value="45">45</option>
						<option <?php if($res12['haxis_angle'] == '90'){ echo 'selected="selected"';} ?>  value="90">90</option>
						<option <?php if($res12['haxis_angle'] == '-45'){ echo 'selected="selected"';} ?>  value="-45">-45</option>
						<option <?php if($res12['haxis_angle'] == '-90'){ echo 'selected="selected"';} ?>  value="-90">-90</option>
						</select>
						</td>
					</tr>



					<tr class="subHeadingTR">
						<td colspan="2"><br /><b>Legend</b></td>
					</tr>
					<tr>
						<td>Legend Position:</td>
					<td>
						<select name="legend_position" id="legend_position">
							<option <?php if($res12['legend_position'] == 'none'){ echo 'selected="selected"';} ?> value="none">None</option>
							<option <?php if($res12['legend_position'] == 'bottom'){ echo 'selected="selected"';} ?> value="bottom">Bottom</option>
							<option <?php if($res12['legend_position'] == 'left'){ echo 'selected="selected"';} ?> value="left">Left</option>
							<option <?php if($res12['legend_position'] == 'in'){ echo 'selected="selected"';} ?> value="in">In</option>
							<option <?php if($res12['legend_position'] == 'right'){ echo 'selected="selected"';} ?> value="right">Right</option>
							<option <?php if($res12['legend_position'] == 'top'){ echo 'selected="selected"';} ?> value="top">Top</option>
						</select>
					</td>
					</tr>
					<tr>
						<td>Legend Alignment:</td>
						<td>
							<select name="legend_alignment" id="legend_alignment">
								<option <?php if($res12['legend_alignment'] == 'start'){ echo 'selected="selected"';} ?> value="start">Start</option>
								<option <?php if($res12['legend_alignment'] == 'center'){ echo 'selected="selected"';} ?> value="center">Center</option>
								<option <?php if($res12['legend_alignment'] == 'end'){ echo 'selected="selected"';} ?> value="end">End</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>Legend Text Color:</td>
						<td><input type="text" value="<?php echo $res12['legend_text_color']; ?>" name="legend_text_color" id="legend_text_color"></td>
					</tr>
					<tr>
						<td>Legend Font Size:</td>
						<td><input type="text" value="<?php echo $res12['legend_font_size']; ?>" name="legend_font_size" id="legend_font_size"></td>
					</tr>
				</tbody>
			</table>
			<?php
			$css = '';
			$css1 = '';
			$css2 = '';
			$css3 = '';
			if(in_array($chart_type,array('Pie','Doughnut')))
			{
				$css = 'display:block';
				$css1 = 'display:none;';
				if($chart_type == 'Pie')
				{
					$css2 = 'display:none;';
				}
				else if($chart_type == 'Doughnut')
				{
					$css = 'display:none';
					$css1 = 'display:none;';
					$css2 = 'display:none;';
				}
				else
				{
					$css2 = 'display:block;';
				}
			}
			else
			{
				$css = 'display:none;';
				$css1 = 'display:block;';
				if($chart_type == 'Bubble')
				{
					$css3 = 'display:block;';
				}
				else
				{
					$css3 = 'display:none;';
				}
			}
			?>

			<!------Pie and Doughnut chart start------------>
			<table style="<?php echo $css; ?>">
			<tbody>
				<tr class="subHeadingTR">
					<td colspan="2"><b><?php echo $chrt; ?> Chart Option</b></td>
				</tr>
				<tr>
					<td width="150px">3D:</td>
					<td>
						2D:  <input type="radio" <?php if($res12['3d'] != true){ echo 'checked="checked"';} ?> value="false" name="3d" id="3d">&nbsp; &nbsp;
						3D:  <input type="radio" <?php if($res12['3d'] == true){ echo 'checked="checked"';} ?> value="true"  name="3d" id="3d">
					</td>
				</tr>
			</tbody>
			</table>
			<!------Pie and Doughnut chart end------------>


			<!------Pie and Doughnut chart start------------>
			<br />
			<table style="<?php echo $css1; ?>">
				<tbody>
					<tr class="subHeadingTR">
						<td colspan="2"><b><?php echo $chrt; ?> Chart Option</b></td>
					</tr>
					<tr class="subHeadingTR">
						<td colspan="2"><br /><b>hAxis</b></td>
					</tr>
					<tr>
						<td style='width: 150px;'>hAxis Title:</td>
						<td><input type="text" value="<?php echo $res12['haxis_title']; ?>" name="haxis_title" id="haxis_title"></td>
					</tr>
					<tr>
						<td>hAxis Position:</td>
						<td>
							<select name="haxis_position" id="haxis_position">
								<option <?php if($res12['haxis_position'] == 'out'){ echo 'selected="selected"';} ?> value="out">Out</option>
								<option <?php if($res12['haxis_position'] == 'in'){ echo 'selected="selected"';} ?> value="in">In</option>
							</select>
						</td>
					</tr>
					<tr class="subHeadingTR">
						<td colspan="2"><br /><b>vAxis</b></td>
					</tr>
					<tr>
						<td>vAxis Title:</td>
						<td><input type="text" value="<?php echo $res12['vaxis_title']; ?>" name="vaxis_title" id="vaxis_title"></td>
					</tr>
					<tr>
						<td>vAxis Position:</td>
						<td>
							<select name="vaxis_position" id="vaxis_position">
								<option <?php if($res12['vaxis_position'] == 'out'){ echo 'selected="selected"';} ?> value="out">Out</option>
								<option <?php if($res12['vaxis_position'] == 'in'){ echo 'selected="selected"';} ?> value="in">In</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<!------Pie and Doughnut chart end------------>
			<table style="margin:10px;">
				<tbody>
					<tr>
						<td colspan="2">
							<input type="button" onclick="return fetchdata('c')" name="view" value="Preview Data">
							<input type="button" onclick="return fetchdata('ch')" name="view" value="Preview <?php echo $detail['chart_type']; ?> Chart">
							<input type="button"  onclick="frmsubmit('frm4','submit','t4')"   name="chartsettingsave" value="Save">
						</td>
					</tr>
				</tbody>
			</table>
			</form>
			</p>
			<br />
			</div>
			<div class='sright'>
				<div style=' width: 455px;'>
					<span id='loader_chart' style="float: right; display: block; width: 300px; margin: 100px 0px;">&nbsp;</span>
					<span id="chart_div" style='  overflow-y: hidden;width: 455px; '></span>
				</div>
			</div>
		</div>
		<!----------TAB 4 End-------------->
		<?php } ?>
	</div>
</div>

<script>
function frmsubmit(id,tabid,fid)
{
	jQuery('#tabid').val(fid);
	var tab = jQuery('#tabid').val();
	if(id == 'frm1')
	{
		var chart_type = jQuery('#chart_type').val();
		if(chart_type == '0')
		{
			jQuery("#selcharterr").html('&nbsp;  Please choose the chart type first!!');
			return false;
		}
	}

	if(id == 'frm1' || id == 'frm2' || id == 'frm3' || id == 'frm4')
	{
		document.getElementById(id).submit();
	}
	else
	{
		window.location.href = "admin.php?page=syntegratechart_chart_add&cid=<?php echo $cid; ?>&tab="+tab;
	}
}
</script>