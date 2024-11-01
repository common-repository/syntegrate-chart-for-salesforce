<?php
global $wpdb;
include('config.php');
$cid = $args['cid'];

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

//DB Settings
$data_refresh_v  = $res['data_refresh_v'];
$data_refresh_t  = $res['data_refresh_t'];

//current time
$cdate = current_time('mysql');
$date = $res['current_date'];

if($data_refresh_t == 'hours')
{
	$hrs = $data_refresh_v;
	$hrsmin = $hrs*60;
	$currentDate = strtotime($date);
	$futureDate = $currentDate+(60*$hrsmin);
	echo $formatDate = date("Y-m-d H:i:s", $futureDate);
}

if($data_refresh_t == 'minutes')
{
	$currentDate = strtotime($date);
	$futureDate = $currentDate+(60*$data_refresh_v);
	$formatDate = date("Y-m-d H:i:s", $futureDate);
}

if($data_refresh_t == 'days')
{
	$formatDate = date('Y-m-d H:i:s', strtotime($date. ' + '.$data_refresh_v.' days'));
}

if($data_refresh_t == 'weeks')
{
	$formatDate = date('Y-m-d H:i:s', strtotime('+ '.$data_refresh_v.' week'));
}


$olddate 	 = strtotime($formatDate);
$currentdate = strtotime($cdate);

if($olddate < $currentdate)
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
				//table,label,value
				//$query =  "SELECT b.".$table_field.", a.".$value_field.",a.".$label_field." FROM ".$label_table." a, a.".$table." b, a.".$table." b";
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
				 $html = " ".$label." like '%".$value1."%' ".$con_type."";
			}
			if($con == 'DoesNotContain')
			{
				$html = " Not ".$label." like '%".$value1."%' ".$con_type."";
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
	$wpdb->update( 'syntegratechart_details', array('data'=> $s,'current_date' => $cdatex) , array('chart_id' => $cid), $format = null, $where_format = null );
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

	$response = json_decode($res['data']);
	if(array_key_exists('error',$response))
	{
		$str = '<table border="0" cellpadding="0" cellspacing="0"><tr><th>No data found!</th></tr><tr><td> Please refine your field selection and try again.</td></tr></table>';
		$modes = 1;

	}
	else
	{
		if($response)
	{
			$j = 1;
			$strs = '';
			foreach($val1ssss as $keys => $vals)
			{
				if($j == 1)
				{
					if(in_array($chartt,array('Pie','Doughnut','Bubble')))
					{
					$strs .= '["'.$label_field.'",';
					}
					else
					{
					$strs .= '["'.$label_field.'",';
					}
					$strs .= '"'.$vals.'"';
				}
				else
				{
					$strs .= ',"'.$vals.'"';
				}
				if($j == count($val1ssss))
				{
					$strs .= "]@";
				}
				$j++;
			}

			$i = 0;
			foreach($response as $key => $val)
			{
				$j = 1;
				foreach($val1ssss as $keys => $vals)
				{
					if($val->$vals != '')
					{
						$vl = $val->$vals;
					}
					else
					{
						$vl = 0;
					}
					if($j == 1)
					{
						if(in_array($chartt,array('Pie','Doughnut','Bubble')))
						{
							$strs .= '["'.$val->$label_field.'",';
						}
						else
						{
							$strs .= '["'.$val->$label_field.'",';
						}
						if(is_numeric($vl))
						{
							$strs .= $vl;
							$str_int .=  '';
						}
						else
						{
							$strs .= $vl;
							$str_int .=  '1';
						}
					}
					else
					{
						if(is_numeric($vl))
						{
							$strs .= ','.$vl;
						}
						else
						{
							$strs .= ','.$vl;
							$str_int .=  '1';
						}
					}
					if($j == count($val1ssss))
					{
						$strs .= ']@';
					}
					$j++;
				}
				$i++;
			}
			if($str_int == '')
			{
				$str = substr($strs,0,-1);
			}
			else
			{
				$str = '<table border="0" cellpadding="0" cellspacing="0"><tr><th>No data found!</th></tr><tr><td> Please refine your field selection and try again.</td></tr></table>';
				$modes = 1;
			}
		}
		else
		{
			$str = '<table border="0" cellpadding="0" cellspacing="0"><tr><th>No data found!</th></tr><tr><td> Please refine your field selection and try again.</td></tr></table>';
			$modes = 1;
		}
	}

}
catch(Exception $e)
{
    $str = '<table border="0" cellpadding="0" cellspacing="0"><tr><th>No data found!</th></tr><tr><td> Please refine your field selection and try again.</td></tr></table>';
	$modes = 1;
}
}
else
{
	$str = '<table border="0" cellpadding="0" cellspacing="0"><tr><th>No data found!</th></tr><tr><td> Please refine your field selection and try again.</td></tr></table>';
	$modes = 1;
}
$str = $modes.'*'.$str;
$str = explode('*',$str);

/////////////////////////////////////////
///////////////CODE//////////////////////
////////////////////////////////////////
$chart_type = $res['chart_type'];
$res1 = (array)$wpdb->get_row($wpdb->prepare("SELECT * FROM `syntegratechart_settings` WHERE chart_id='".$cid."'",''));
?>

<script type="text/javascript">
google.load("visualization", "1", {packages:["corechart"]});
<?php
if($str[0] == '')
{
?>
var common = '<?php echo $str[1]; ?>';
google.setOnLoadCallback(drawChart<?php echo $cid;?>);
<?php
}
else
{
$status = $str[1];
}
?>

function drawChart<?php echo $cid;?>() {
var demo=[];
var extra=common.split('@');
for(var i=0;i<extra.length;i++)
{
	var array = eval(extra[i]);
	demo.push(array);
}
var data = google.visualization.arrayToDataTable(demo);

<?php
$tb = 'false';
$ti = 'false';
if($res1['font_style'] == 'bold' || $res1['font_style'] == 'Italic')
{
	if($res1['font_style'] == 'bold')
	{
		$tb = 'true';
	}
	else if($res1['font_style'] == 'Italic')
	{
		$ti = 'true';
	}
}


if(in_array($chart_type,array('Pie','Doughnut')))
{
	?>
	var options =
	{
		<?php
		if($res1['title'] != '')
		{
		?>
		title:'<?php echo $res1['title']; ?>',
		<?php
		}
		?>
		width:<?php if($res1['width'] == ''){ echo '600'; }else{ echo $res1['width']; } ?>,
		height:<?php if($res1['height'] == ''){ echo '400'; }else{ echo $res1['height']; } ?>,
		backgroundColor: "<?php if($res1['background_color'] == ''){ echo 'transparent'; }else{ echo $res1['background_color']; } ?>",
		legend : {
				<?php
				if($res1['legend_position'] != ''){
				?>
				position: '<?php echo $res1['legend_position']; ?>',
				<?php
				}
				if($res1['legend_alignment'] != '')
				{
				?>
				alignment: '<?php echo $res1['legend_alignment']; ?>',
				<?php
				}
				?>
				textStyle: {
					<?php
					if($res1['legend_text_color'] != '')
					{
					?>
					color: '<?php echo $res1['legend_text_color']; ?>',
					<?php
					}
					if($res1['legend_font_size'] != '')
					{
					?>
					fontSize: <?php echo $res1['legend_font_size']; ?>
					<?php
					}
					?>
					}
			},
		<?php
		if($res1['3d'] != ''){
		?>
		is3D: <?php echo $res1['3d']; ?>
		<?php
		}
		?>
	};
	var chart = new google.visualization.PieChart(document.getElementById('chart_div<?php echo $cid;?>'));
	<?php
}
else
{
	?>
	var options =
		{
			legend : {
				<?php
				if($res1['legend_position'] != ''){
				?>
				position: '<?php echo $res1['legend_position']; ?>',
				<?php
				}
				if($res1['legend_alignment'] != '')
				{
				?>
				alignment: '<?php echo $res1['legend_alignment']; ?>',
				<?php
				}
				?>
				textStyle: {
					<?php
					if($res1['legend_text_color'] != '')
					{
					?>
					color: '<?php echo $res1['legend_text_color']; ?>',
					<?php
					}
					if($res1['legend_font_size'] != '')
					{
					?>
					fontSize: <?php echo $res1['legend_font_size']; ?>
					<?php
					}
					?>
					}
			},
			<?php
			if($res1['orientation'] != '')
			{
			?>
			orientation:'<?php echo $res1['orientation']; ?>',
			<?php
			}
			if($res1['title'] != '')
			{
			?>
			title:'<?php echo $res1['title']; ?>',
			<?php
			}
			if($res1['position'] != '')
			{
			?>
			titlePosition: '<?php echo $res1['position']; ?>',
			<?php
			}
			?>
			hAxis: {
				<?php
				if($res1['haxis_angle'] != '')
				{
				?>
				slantedText :true,
				slantedTextAngle:<?php echo $res1['haxis_angle']; ?>,
				<?php
				}
				if($res1['haxis_position'] != '')
				{
				?>
				textPosition: '<?php echo $res1['haxis_position']; ?>',
				<?php
				}
				if($res1['haxis_title'] != '')
				{
				?>
				title: '<?php echo $res1['haxis_title']; ?>'
				<?php
				}
				?>
				},
			vAxis: {
				<?php
				if($res1['vaxis_position'] != '')
				{
				?>
				textPosition: '<?php echo $res1['vaxis_position']; ?>',
				<?php
				}
				if($res1['vaxis_title'] != '')
				{
				?>
				title: '<?php echo $res1['vaxis_title']; ?>'
				<?php
				}
				?>
				},
			width:<?php if($res1['width'] == ''){ echo '600'; }else{ echo $res1['width']; } ?>,
			height:<?php if($res1['height'] == ''){ echo '400'; }else{ echo $res1['height']; } ?>,
			titleTextStyle:
				{
				<?php
				if($res1['font_color'] != '')
				{
				?>
				color: '<?php echo $res1['font_color']; ?>',
				<?php
				}
				?>
				fontName: 'arail',
				<?php
				if($res1['font_size'] != '')
				{
				?>
				fontSize: <?php echo $res1['font_size']; ?>,
				<?php
				}
				?>
				bold: <?php echo $tb; ?>,
				italic: <?php echo $ti; ?>
				},
			backgroundColor: "<?php if($res1['background_color'] == ''){ echo 'transparent'; }else{ echo $res1['background_color']; } ?>"
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
	var chart = new google.visualization.<?php echo $chrt; ?>Chart(document.getElementById('chart_div<?php echo $cid;?>'));
	<?php
}
?>
chart.draw(data, options);
}

</script>
<span id="chart_div<?php echo $cid;?>"><?php echo $status; ?></span>
