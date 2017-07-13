<div class="wsko_no_cache_wrapper">
<?php if (current_user_can('manage_options'))
{
	?><i class="fa fa-retweet" style="font-size:80px; color:#ddd;" aria-hidden="true"></i></br>
	<a class="wsko_update_cache_btn button" style="margin:10px;" href="#"><i class="fa fa-spinner fa-pulse" style="display:none;"></i> Update Cache manually</a><?php
}
else
{
	?>
	<i class="fa fa-times" style="font-size:80px; color:#ddd;" aria-hidden="true"></i></br>
	<span>No Data available</span>
	<?php
} ?>
</div>