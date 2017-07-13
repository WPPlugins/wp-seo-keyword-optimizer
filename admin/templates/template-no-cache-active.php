<div class="wsko_no_cache_wrapper">
<?php if (current_user_can('manage_options'))
{
	?><i class="fa fa-tasks" style="font-size:80px; color:#ddd;" aria-hidden="true"></i></br>
	<span style="margin:10px;">Please enable caching in the <a href="<?=admin_url('admin.php?page=wsko_settings_view')?>">Settings</a>. This information isn't available with live data.</span><?php
}
else
{
	?>
	<i class="fa fa-times" style="font-size:80px; color:#ddd;" aria-hidden="true"></i></br>
	<span>No Data available. Your admin has to enable caching for this plugin in order to activate this view.</span>
	<?php
} ?>

</div>