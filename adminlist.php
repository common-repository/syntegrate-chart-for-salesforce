<div class="wrap">
<div class="icon32" id="icon-users"><br></div>
<h2>
Syntegrate Chart
<?php
if(syntegratechart_authcheck_credentails() == 'w')
{
	?>
	<a class="add-new-h2" href="admin.php?page=syntegratechart_chart_add">Add New</a>
	<?php
}
?>
</h2>
<br />
<table cellspacing="0" class="wp-list-table widefat fixed bookmarks">
	<thead>
	<tr>
		<th class="manage-column column-name sortable desc" scope="col" style="width:80px !important;"><a href="javascript:void(0)"><span>S. No</span><span class="sorting-indicator"></span></a></th>
		<th style="" class="manage-column column-name sortable desc" scope="col"><a href="javascript:void(0)"><span>Chart Type</span><span class="sorting-indicator"></span></a></th>
		<th style="" class="manage-column column-url sortable desc" scope="col"><a href="javascript:void(0)"><span>Chart Object</span><span class="sorting-indicator"></span></a></th>
		<th style="" class="manage-column column-url sortable desc" scope="col"><a href="javascript:void(0)"><span> Use Shortcode</span><span class="sorting-indicator"></span></a></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th style="" class="manage-column column-name sortable desc" scope="col" ><a href="javascript:void(0)"><span>S. No</span><span class="sorting-indicator"></span></a></th>
		<th style="" class="manage-column column-name sortable desc" scope="col"><a href="javascript:void(0)"><span>Chart Type</span><span class="sorting-indicator"></span></a></th>
		<th style="" class="manage-column column-url sortable desc" scope="col"><a href="javascript:void(0)"><span>Chart Object</span><span class="sorting-indicator"></span></a></th>
		<th style="" class="manage-column column-url sortable desc" scope="col"><a href="javascript:void(0)"><span>Use Shortcode</span><span class="sorting-indicator"></span></a></th>
	</tr>
	</tfoot>

	<tbody id="the-list">
	<?php
	$query1 =  "SELECT * FROM `syntegratechart_details`";
	$run = mysql_query($query1);
	$rescount = mysql_num_rows($run);
	if($rescount > 0)
	{
		$i = 1;
		while($res = mysql_fetch_array($run))
		{
		?>
		<tr valign="middle" class="alternate" id="link-<?php echo $res['chart_id']; ?>">
			<td class="column-categories"><a href="javascript:void(0)"><?php echo $i++; ?></a></td>
			<td class="column-name"><strong><a title="Edit" href="admin.php?page=syntegratechart_chart_add&cid=<?php echo $res['chart_id']; ?>" class="row-title"><?php echo $res['chart_type']; ?></a></strong><br>
			<div class="row-actions"><span class="edit"><a href="admin.php?page=syntegratechart_chart_add&cid=<?php echo $res['chart_id']; ?>">Edit</a> | </span>
			<span class="delete"><a href="javascript:void(0)" class="submitdelete">Delete</a></span></div></td>
			<td class="column-url"><a title="<?php echo $res['table']; ?>" href="javascript:void(0)"><?php echo $res['table']; ?></a></td>
			<td class="column-categories"><a href="javascript:void(0)">[syntegratechart cid="<?php echo $res['chart_id']; ?>"]</a></td>
		</tr>
		<?php
		}
	}
	else
	{
	?>
		<tr class="no-items"><td colspan="4" class="colspanchange">No Chart found.</td></tr>
	<?php
	}
	?>
	</tbody>
</table>
</div>