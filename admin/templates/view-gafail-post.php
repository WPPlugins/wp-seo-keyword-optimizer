<?php
if (!defined('ABSPATH')) exit;

if ($token)
{
	?>
	<div style="text-align:right; margin-top:10px;">
	<a class="button" href="<?=admin_url('admin.php?page=wsko_main_view')?>">Authenticate with Google</a>
	</div>
	<?php
	}
else
{
	?>Google API has caused an error. 
	<br />
	<div style="text-align:right; margin-top:10px;">
	<a class="button" href="<?=admin_url('admin.php?page=wsko_main_view')?>">See more</a>
	</div>
	<?php
	}
?>