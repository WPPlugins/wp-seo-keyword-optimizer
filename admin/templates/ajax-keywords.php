<?php
if (!defined('ABSPATH')) exit;

$res = wsko_url_get_title($url);
?>
<div class="wsko_modal_top" style="margin-bottom:20px;">
<div><label>Title: <?=$res->title?></label></div>
<div><span class="font-unimportant"><?=$url?></span></div>
<div style="margin-top:20px;"><span class="font-unimportant">Values are shown for the time span <?=date('d.m.Y', $time)?> to <?=date('d.m.Y', $time2)?>.</span></div>
</div>
<table id="wsko_tables_page" class="table table-striped table-bordered wsko_modal_table wsko_tables table-condensed" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th>Keyword</th>
				<th>Clicks</th>
				<th>Position</th>
				<th>Impressions</th>
				<th>CTR</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Keyword</th>
				<th>Clicks</th>
				<th>Position</th>
				<th>Impressions</th>
				<th>CTR</th>
			</tr>
		</tfoot>
		<tbody>
		
	<?php
	foreach ($rows as $row)
	{
		?>
		<tr>
		<td><?=$row->keys[0]?></td>
		<td><?=$row->clicks?></td>
		<td><?=round($row->position, 2)?></td>
		<td><?=$row->impressions?></td>
		<td><?=round($row->ctr, 2)?> %</td>
		</tr><?php
	}
	?>
	</tbody>
	</table>
