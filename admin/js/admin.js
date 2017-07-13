jQuery(document).ready(function($)
{
	window.wsko_is_caching = false;
	
	jQuery.wsko_init_admin = function wsko_init_admin()
	{
		$('.wsko_update_cache_btn').each(function(index){
			$(this).click(function(event){
				event.preventDefault();
				var $this = $(this);
				
				if (window.wsko_is_caching)
					return;
				
				window.wsko_is_caching = true;
				
				window.onbeforeunload = function(){
					 return 'The cache update is still running. This process can take up to 1 minute if you are refreshing your whole 90 days cache. Do you really want to leave this page?';
				};
				$this.find('i').show();
				
				$.ajax({
					url: ajaxurl,
					type: 'post',
					data: {
						nonce: $this.data('nonce'),
						action : 'wsko_update_cache',
					},
					beforeSend: function()
					{
					},
					success: function(res)
					{
						window.onbeforeunload = function(){ };
						window.wsko_is_caching = false;
						$this.find('i').hide();
						
						if (res.success)
						{
							location.reload(true);
						}
					}
				});
			});
		});
		$('.wsko-icheck').each(function(index){
			var self = $(this)
			if (!self.hasClass('wsko-icheck-ready'))
			{
				var label = self.next(),
				label_text = label.text();
				
				label.remove();
				self.iCheck({
				  radioClass: 'iradio_line-blue wsko-icheck-' + self.data('color'),
				  insert: '<div class="icheck_line-icon"></div>' + label_text
				});
				self.addClass('wsko-icheck-ready');
			}
		});
	};
	jQuery.wsko_init_admin();
    function wsko_format_time_scope(start, end)
	{
        $('#wsko_time_scope span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        $('#wsko_time_scope .wsko-start-time').val(start.utc().startOf('day').unix());
        $('#wsko_time_scope .wsko-end-time').val(end.utc().endOf('day').unix()); //new Date(end.format('MM-D-YYYY')).getTime() / 1000
    }
	var start = moment.unix(parseInt($('#wsko_time_scope .wsko-start-time').val())).utc().startOf('day');
	var end = moment.unix(parseInt($('#wsko_time_scope .wsko-end-time').val())).utc().startOf('day');
	$('#wsko_time_scope').daterangepicker({
		startDate: start,
        endDate: end,
		minDate: moment.unix(parseInt($('#wsko_time_scope').data('start'))).utc().startOf('day'),
		maxDate: moment().utc().startOf('day').subtract(3, 'days'),
        ranges: {
           //'Today': [moment(), moment()],
           //'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().utc().startOf('day').subtract(3 + 6, 'days'), moment().utc().startOf('day').subtract(3, 'days')],
           'Last 28 Days': [moment().utc().startOf('day').subtract(3 + 27, 'days'), moment().utc().startOf('day').subtract(3, 'days')],
           'This Month': [moment().utc().startOf('month'), moment().utc().endOf('month')],
           'Last Month': [moment().utc().subtract(1, 'month').startOf('month'), moment().utc().subtract(1, 'month').endOf('month')]
		}
	}, wsko_format_time_scope).on('apply.daterangepicker', function(ev, picker) {
		$('#wsko_reload_data_form').submit();
	});

	wsko_format_time_scope(start, end);
	
	/*$('#wsko_reload_data').click(function(event){
		event.preventDefault();
		
	});*/
	
	jQuery.wsko_init();
});	