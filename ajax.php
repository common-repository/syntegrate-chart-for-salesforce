<?php
global $wpdb;
require_once('lib/SforceEnterpriseClient.php');
$auth = (array)$wpdb->get_row($wpdb->prepare("SELECT * FROM `syntegratechart_auth` WHERE id='1'",''));

$username 	= $auth['username'];
$password 	= $auth['password'];
$key 		= $auth['key'];

$mySforceConnection = new SforceEnterpriseClient();
$mySoapClient = $mySforceConnection->createConnection(plugins_url('/', __FILE__ ).'lib/enterprise.wsdl.xml');
$mylogin = $mySforceConnection->login($username,$password.$key);

$mode = $_GET['mode'];

if($mode == 'filter_condition')
{
	$str_labels 		=   explode("~",$_GET['label']);
	$str_label_ones 	=   explode("^",$str_labels[1]);
	$tbname = $str_label_ones[0];
	$fdname = $str_labels[0];
	$tables_fields = $mySforceConnection->describeSObject($tbname);
	foreach($tables_fields->fields as $key => $val)
	{
		if($val->name == $fdname)
		{
			if(in_array($val->soapType, array("xsd:int","xsd:double")))
			{
				$htmls = '<option value="-1">--select--</option>
						 <option value="Greaterthan">is greater than</option>
						 <option value="Lessthan" >is less than</option>
						 <option value="Greaterthanorequalto" >is greater than or equal to</option>
					 	 <option value="Lessthanorequalto" >is less than or equal to</option>
						 <option value="Equal">is equal to</option>
						 <option value="NotEqualTo">is not equal to</option>';
			}
			else
			{
				$htmls = '<option value="-1">--select--</option>
						 <option value="Equal">is equal to</option>
						 <option value="NotEqualTo">is not equal to</option>
						 <option value="Contains">contains</option>
						 <option value="DoesNotContain">does not contain</option>';
			}
		}
	}
	echo $htmls;
}

if($mode == 'filterdel')
{
	$wpdb->delete( 'syntegratechart_filter', array( 'id' => $_GET['id'] ) );
}

if($mode == 'filteradd')
{
	$data = array();
	$data['chart_id'] = $_GET['cid'];
	$data['filter_field'] = $_GET['label'];
	$data['filter'] = $_GET['filter'];
	$data['filter_value'] = $_GET['filterval'];
	$wpdb->insert( 'syntegratechart_filter', $data, $format = null );


	$html .= '<table border="0" style="border:2px solid #257AB6;width:98%;" cellpadding="0" cellspacing="2">';
	$html .= '<tr style="background:#257AB6;color:#fff;">';
	$html .= '<th>Name</th><th>Filter</th><th>Value</th><th>Action</th></tr>';
	$html .= '<tbody>';

	$res2 = $wpdb->get_results($wpdb->prepare("SELECT * FROM `syntegratechart_filter` WHERE chart_id='".$_GET['cid']."'",''));
	$run = json_decode(json_encode($res2), true);
	foreach($run as $k => $detail)
	{
		$str_label 		=   explode("~",$detail['filter_field']);
		$str_label_one  =   explode("^",$str_label[1]);
		$label_field 	=	$str_label[0];
		$html .= '<tr id="filter_'.$detail['id'].'"><td>'.$label_field.'</td><td>'.$detail['filter'].'</td><td>'.$detail['filter_value'].'</td><td  align="center"><a href="javascript:void(0)"  onclick=filter_delete('.$detail['id'].') ><img src="https://cdn1.iconfinder.com/data/icons/tiny-icons/delete.png"></a></td></tr>';
	}
	$html .= '</tbody>
	</table>';
	echo $html;
	exit;
}

if($mode == 'tables_fields_label'){
$cid = $_GET['cid'];
$detail = (array)$wpdb->get_row($wpdb->prepare("SELECT * FROM `syntegratechart_details` WHERE chart_id='".$cid."'",''));

$res2 = $wpdb->get_results($wpdb->prepare("SELECT * FROM `syntegratechart_details_value` WHERE chart_id='".$cid."'",''));
$run2 = json_decode(json_encode($res2), true);
$strg = '';
foreach($run2 as $k => $detail2)
{
	$strg .= '!'.$detail2['value'];
}
$strg = substr($strg,1);
$strg = explode("!",$strg);

$id   = $_GET['id'];
$related   = $_GET['related'];
$tables_fields=$mySforceConnection->describeSObject($id);
	foreach($tables_fields->fields as $key => $val)
	{
		if($val->name != 'Id')
		{

			if($detail['label'] == $val->name.'~'.$id.'^')
			{
			?>
				<option selected="selected" value="<?php echo $val->name.'~'.$id.'^';?>" ><?php echo $val->label;?> </option>
			<?php
			}
			else
			{
			?>
				<option value="<?php echo $val->name.'~'.$id.'^';?>" ><?php echo $val->label;?> </option>
			<?php
			}
		}
	}
	if($related == 1)
	{
		echo '<optgroup label="--RELATED OBJECT FIELDS--">';
		if(count($tables_fields->childRelationships) > 0)
		{
			foreach($tables_fields->childRelationships as $keys => $vals)
			{
				$tables_fieldss=$mySforceConnection->describeSObject($vals->childSObject);
				foreach($tables_fieldss->fields as $keya => $vala)
				{
					if($vals->relationshipName != '')
					{
						if($vala->name != 'Id')
						{
							if($detail['label'] == $vala->name.'~'.$vals->childSObject.'^'.$vals->field)
							{
							?>
								<option selected="selected" value="<?php echo $vala->name.'~'.$vals->childSObject.'^'.$vals->field;?>" ><?php echo $vala->label.' ('.$vals->childSObject.')';?> </option>
							<?php
							}
							else
							{
							?>
								<option value="<?php echo $vala->name.'~'.$vals->childSObject.'^'.$vals->field;?>" ><?php echo $vala->label.' ('.$vals->childSObject.')';?> </option>
							<?php
							}
						}
					}
				}
			}
		}
		echo '</optgroup>';
	}
	echo '#*#';
	foreach($tables_fields->fields as $key => $val)
	{
		if($val->name != 'Id')
		{
			$v = $val->name.'~'.$id.'^';
			if (in_array($v, $strg))
			{
			?>
				<option selected="selected" value="<?php echo $val->name.'~'.$id.'^';?>" ><?php echo $val->label;?> </option>
			<?php
			}
			else
			{
			?>
				<option value="<?php echo $val->name.'~'.$id.'^';?>" ><?php echo $val->label;?> </option>
			<?php
			}
		}
	}
	if($related == 1)
	{
		echo '<optgroup label="--RELATED OBJECT FIELDS--">';
		if(count($tables_fields->childRelationships) > 0)
		{
			foreach($tables_fields->childRelationships as $keys => $vals)
			{
				$tables_fieldss=$mySforceConnection->describeSObject($vals->childSObject);
				foreach($tables_fieldss->fields as $keya => $vala)
				{
					if($vals->relationshipName != '')
					{
						if($vala->name != 'Id')
						{
							$v = $vala->name.'~'.$vals->childSObject.'^'.$vals->field;
							if(in_array($v, $strg))
							{
							?>
								<option selected="selected" value="<?php echo $vala->name.'~'.$vals->childSObject.'^'.$vals->field;?>" ><?php echo $vala->label.' ('.$vals->childSObject.')';?> </option>
							<?php
							}
							else
							{
							?>
								<option value="<?php echo $vala->name.'~'.$vals->childSObject.'^'.$vals->field;?>" ><?php echo $vala->label.' ('.$vals->childSObject.')';?> </option>
							<?php
							}
						}
					}
				}
			}
		}
		echo '</optgroup>';
	}

}

if($mode == 'fetchdata')
{
$cid = $_GET['cid'];
$detail = (array)$wpdb->get_row($wpdb->prepare("SELECT * FROM `syntegratechart_details` WHERE chart_id='".$cid."'",''));

$type	 			 = $_GET['type'];
$table	 			 = $_POST['tables'];
$tables_fields_label = $_POST['tables_fields_label'];
$tables_fields_value = $_POST['tables_fields_value'];
$chartt = $detail['chart_type'];
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
			// echo 1;
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
			//table,value
			$query =  "SELECT b.".$table_field.",".$val1.",b.".$label_field." FROM ".$value_table." a, a.".$table." b";
		}
		else if($table == $value_table && $table != $label_table)
		{
			// echo 2;
			//done
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
			//value,table
			$query = "SELECT b.".$table_field.",".$val1.",a.".$label_field." FROM ".$label_table." a, a.".$table." b";
			}
		else
		{
			// echo 3;
			//done
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
			//exit;
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
	$response = $mySforceConnection->query($query);
	$response = (array)$response->records;

	if($response)
	{
		if($type == 'ch')
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
			$j = 1;
			foreach($val1ssss as $keys => $vals)
			{
				if($j == 1)
				{
					$strs .= '<tr style="background:#ccc;"><th>&nbsp;'.$label_field.'&nbsp;</th>';
				}
				$strs .= '<th>&nbsp;'.$vals.'&nbsp;</th>';
				if($j == count($val1ssss))
				{
					$strs .= '</tr>';
				}
				$j++;
			}

			$str .= '<table border="0" cellpadding="0" cellspacing="2">'.$strs;
			$i = 0;
			foreach($response as $key => $val)
			{
				$j = 1;
				foreach($val1ssss as $keys => $vals)
				{
					if($j == 1)
					{
						$str .= '<tr><td>&nbsp;'.$val->$label_field.'&nbsp;</td>';
					}
					$str .= '<td>&nbsp;'.$val->$vals.'&nbsp;</td>';
					if($j == count($val1ssss))
					{
						$str .= '</tr>';
					}
					$j++;
				}
				$i++;
			}
			$str .= '</table>';
		}
	}
	else
	{
		$str = '<table border="0" cellpadding="0" cellspacing="0"><tr><th>No data found!</th></tr><tr><td> Please refine your field selection and try again.</td></tr></table>';
		$modes = 1;
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
echo $modes.'*'.$str;

}

if($mode == 'fetchdatatab4')
{
$cid = $_GET['cid'];
$detail = (array)$wpdb->get_row($wpdb->prepare("SELECT * FROM `syntegratechart_details` WHERE chart_id='".$cid."'",''));

if($detail['filter_and_or'] != '')
{
	$con_type = $detail['filter_and_or'];
}
else
{
	$con_type = 'or';
}

$arr = array();
$res2 = $wpdb->get_results($wpdb->prepare("SELECT * FROM `syntegratechart_details_value` WHERE chart_id='".$cid."'",''));
$run = json_decode(json_encode($res2), true);
foreach($run as $k => $details)
{
	$arr[] = $details['value'];
}

$type	 			 = $_GET['type'];
$table	 			 = $detail['table'];
$tables_fields_label = $detail['label'];
$tables_fields_value = $arr;
$chartt = $detail['chart_type'];
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
			// echo 1;
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
			//table,value
			$query =  "SELECT b.".$table_field.",".$val1.",b.".$label_field." FROM ".$value_table." a, a.".$table." b";
		}
		else if($table == $value_table && $table != $label_table)
		{
			// echo 2;
			//done
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
			//value,table
			$query = "SELECT b.".$table_field.",".$val1.",a.".$label_field." FROM ".$label_table." a, a.".$table." b";
			}
		else
		{
			// echo 3;
			//done
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
			//exit;
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

	function condition_Adds($con,$label,$value,$con_type)
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
				$condition .= condition_Adds($con,$label,$value,$con_type);
			}
			else if($i == $to)
			{
				$condition .= condition_Adds($con,$label,$value,'');
			}
			else
			{
				$condition .= condition_Adds($con,$label,$value,$con_type);
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

	if($response)
	{
		if($type == 'ch')
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
			$j = 1;
			foreach($val1ssss as $keys => $vals)
			{
				if($j == 1)
				{
					$strs .= '<tr style="background:#ccc;"><th>&nbsp;'.$label_field.'&nbsp;</th>';
				}
				$strs .= '<th>&nbsp;'.$vals.'&nbsp;</th>';
				if($j == count($val1ssss))
				{
					$strs .= '</tr>';
				}
				$j++;
			}

			$str .= '<table border="0" cellpadding="0" cellspacing="2">'.$strs;
			$i = 0;
			foreach($response as $key => $val)
			{
				$j = 1;
				foreach($val1ssss as $keys => $vals)
				{
					if($j == 1)
					{
						$str .= '<tr><td>&nbsp;'.$val->$label_field.'&nbsp;</td>';
					}
					$str .= '<td>&nbsp;'.$val->$vals.'&nbsp;</td>';
					if($j == count($val1ssss))
					{
						$str .= '</tr>';
					}
					$j++;
				}
				$i++;
			}
			$str .= '</table>';
		}
	}
	else
	{
		$str = '<table border="0" cellpadding="0" cellspacing="0"><tr><th>No data found!</th></tr><tr><td> Please refine your field selection and try again.</td></tr></table>';
		$modes = 1;
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
echo $modes.'*'.$str;

}
?>