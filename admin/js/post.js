jQuery(document).ready(function($)
{
	var wsko_max_groups = 10;
	
	jQuery.wsko_init_post = function wsko_init_post()
	{
		$('#wsko_suggest_kw').on('keypress', function(e) {
			return e.which !== 13;
		});
		
		$('.keyword_group_wrapper').each(function(index){
			wsko_set_keyword_row($(this));
		});
		
		$('#wsko_select_keywords_btn').click(function(event){
			event.preventDefault();
			$('#modal_track_keywords').modal('show');
		});
		
		$('#wsko_add_track_keyword').click(function(event){
			event.preventDefault();
			var $kw_field = $('#wsko_add_track_keyword_value'),
			val = $kw_field.val(),		
			isFree = ($(".wsko-keywords-anchor").filter(function() { return $(this).text() === val; }).length == 0);
			
			$('#wsko_error_add_keyword').hide().html('');
			
			if (isFree)
			{
				if (val != undefined && val != '')
				{
					var $wrap = $('#wsko_custom_keywords');
					$kw_field.val('');
					var $item = $wrap.find('.wsko-kw-template').clone(true).removeClass('wsko-kw-template').appendTo('#wsko_kw_wrapper').show();
					$item.find('.wsko-kw-value').attr('name', 'keywords_new[' + val + '][value]').val(val).iCheck({
					  checkboxClass: 'icheckbox_line-blue wsko_icheck_toggle',
					  radioClass: 'iradio_line-blue',
					  insert: '<div class="icheck_line-icon"></div> Target'
					});
					$item.find('.keyword_group_select').attr('name', 'keywords_new[' + val + '][group]');
					$item.find('.keyword_group_set_main').attr('name', 'keywords_new[' + val + '][group_main]').iCheck({
						checkboxClass: 'icheckbox_line-blue wsko_main_keyword',
						radioClass: 'iradio_line-blue',
						insert: '<div class="icheck_line-icon"></div> Main Keyword'
					}); 
					$item.find('.wsko-kw-focus').attr('name', 'keywords_new[' + val + '][focus]').iCheck({
					  checkboxClass: 'icheckbox_line-blue wsko_icheck_toggle',
					  radioClass: 'iradio_line-blue',
					  insert: '<div class="icheck_line-icon"></div> Focus'
					});
					$item.find('.wsko-kw-title').html(val);
					$('#wsko_kw_wrapper').find('.wsko-empty-row').hide();
					wsko_set_keyword_row($item);
					
					$('#wsko_add_track_keyword_view').click();
				}
				else
				{
					$('#wsko_error_add_keyword').show().html('<div class="bs-callout bs-callout-danger" style="background-color:#fff;padding:10px;">Please enter a keyword.</div>');
				}
			}
			else
			{
				$('#wsko_error_add_keyword').show().html('<div class="bs-callout bs-callout-danger" style="background-color:#fff;padding:10px;">Keyword already exists!</div>');
			}
		});	
		
		$('#wsko_track_keywords_form').submit(function(event){
			event.preventDefault();
			if (confirm('Page will be reloaded. Not saved changes will be lost! Are you sure you want to continue?'))
			{
				var $this = $(this);
				if (($this.find('.wsko-cb-target:checked').length + $this.find('.wsko-cb-target-custom:checked').length) > 0 && ($this.find('.wsko-cb-focus:checked').length + $this.find('.wsko-cb-focus-custom:checked').length) == 0)
				{
					alert('You have to choose at least one focus keyword.');
					return false;
				}
				
				$('.wsko-cb-target').iCheck('enable');
				$('.wsko-cb-focus').iCheck('enable');
				$('.wsko-cb-target-custom').iCheck('enable');
				$('.wsko-cb-focus-custom').iCheck('enable');
				
				$.ajax({
					url: ajaxurl,
					type: 'post',
					data: {
						form_data: $this.serialize() + '&' + $('#wsko_tables_page').DataTable().$('input').serialize(),
						action : 'wsko_track_keywords',
					},
					beforeSend: function()
					{
					},
					success: function(res)
					{
						location.reload(true);
					}
				});
			}
		});
	};
	var isKwGroupSetting = false;
	
	function wsko_set_keyword_row($group_wrapper)
	{
		$group_wrapper.find('.keyword_group_set_main').each(function(index){
			var $this = $(this);
			$this.on('ifChecked', function(event){
				var group = $this.attr('data-group');
				
				var $par = $this.parents('.keyword_group_wrapper');
				$par.find('.wsko-cb-target').iCheck('enable');
				$par.find('.wsko-cb-focus').iCheck('enable');
				$par.find('.wsko-cb-target-custom').iCheck('enable');
				$par.find('.wsko-cb-focus-custom').iCheck('enable');
				
				$('.keyword_group_set_main').each(function(index)
				{
					if (!$(this).is($this) && $(this).attr('data-group') == group)
					{
						var tr = $(this).prop('checked');
						isKwGroupSetting = true;
						$(this).iCheck('uncheck');
						isKwGroupSetting = false;
						if (tr)
							$(this).iCheck('update');
						
						var $par = $(this).parents('.keyword_group_wrapper');
						$par.find('.wsko-cb-target').iCheck('disable');
						$par.find('.wsko-cb-focus').iCheck('disable');
						$par.find('.wsko-cb-target-custom').iCheck('disable');
						$par.find('.wsko-cb-focus-custom').iCheck('disable');
					}
				});
				wsko_set_keyword_defaults();
			});
			
			$this.on('ifUnchecked', function(event){
				if (!isKwGroupSetting)
				{
					var group = $this.attr('data-group');
					
					$('.keyword_group_set_main').each(function(index)
					{
						if ($(this).attr('data-group') == group)
						{
							var $par = $(this).parents('.keyword_group_wrapper');
							$par.find('.wsko-cb-target').iCheck('enable');
							$par.find('.wsko-cb-focus').iCheck('enable');
							$par.find('.wsko-cb-target-custom').iCheck('enable');
							$par.find('.wsko-cb-focus-custom').iCheck('enable');
						}
					});
					wsko_set_keyword_defaults();
				}
			});
		});
		
		$group_wrapper.find('.wsko-cb-focus').each(function(index){
			$(this).on('ifChecked', function(event){
				$(this).parents('.keyword_group_wrapper').find('.wsko-cb-target').iCheck('check');
			});
		});
		
		$group_wrapper.find('.wsko-cb-target').each(function(index){
			$(this).on('ifChecked', function(event){
				var $par = $(this).parents('.keyword_group_wrapper');
				$par.find('.keyword_group_select').attr('disabled', false);
			});
			$(this).on('ifUnchecked', function(event){
				var $par = $(this).parents('.keyword_group_wrapper');
				$par.find('.wsko-cb-focus').iCheck('uncheck');
				$par.find('.keyword_group_set_main').iCheck('uncheck').iCheck('update');
				$par.find('.keyword_group_select').val('0').attr('disabled', true).trigger("change");
			});
		});
		
		$group_wrapper.find('.wsko-cb-focus-custom').each(function(index){
			$(this).on('ifChecked', function(event){
				$(this).parents('.keyword_group_wrapper').find('.wsko-cb-target-custom').iCheck('check');
			});
		});
		
		$group_wrapper.find('.wsko-cb-target-custom').each(function(index){
			$(this).on('ifChecked', function(event){
				var $par = $(this).parents('.keyword_group_wrapper');
				$par.find('.keyword_group_select').attr('disabled', false);
			});
			$(this).on('ifUnchecked', function(event){
				var $par = $(this).parents('.keyword_group_wrapper');
				$par.find('.wsko-cb-focus-custom').iCheck('uncheck');
				$par.find('.keyword_group_set_main').iCheck('uncheck').iCheck('update');
				$par.find('.keyword_group_select').val('0').attr('disabled', true).trigger("change");
			});
		});
		
		$group_wrapper.find('.keyword_group_select').each(function(index){
			$(this).change(function(){
				var $this = $(this),
				$par = $this.parents('.keyword_group_wrapper'),
				val = $this.val();
				
				if ($par.find('.keyword_group_set_main').prop('checked'))
				{
					$par.find('.keyword_group_set_main').iCheck('uncheck').iCheck('update');
				}
				
				for (var i = 0; i <= wsko_max_groups; i++)
				{
					$this.removeClass('keyword_group_' + i);
				}
				$this.addClass('keyword_group_' + val);
				$par.find('.keyword_group_set_main').attr('data-group', val);
				
				if (val == 0)
				{
					$par.find('.keyword_group_set_main').iCheck('disable');
				}
				else
				{
					$par.find('.keyword_group_set_main').iCheck('enable');
				}
				$par.find('.keyword_group_set_main').iCheck('uncheck');
				if ($('.keyword_group_set_main[data-group="' + val + '"]:checked').length > 0)
				{
					$par.find('.wsko-cb-target').iCheck('disable');
					$par.find('.wsko-cb-focus').iCheck('disable');
					$par.find('.wsko-cb-target-custom').iCheck('disable');
					$par.find('.wsko-cb-focus-custom').iCheck('disable');
				}
				else
				{
					$par.find('.wsko-cb-target').iCheck('enable');
					$par.find('.wsko-cb-focus').iCheck('enable');
					$par.find('.wsko-cb-target-custom').iCheck('enable');
					$par.find('.wsko-cb-focus-custom').iCheck('enable');
				}
				wsko_set_keyword_defaults();
			});
		});
	}
	
	function wsko_set_keyword_defaults()
	{
		$('.keyword_group_set_main').each(function(index){
			$(this).parents('.wsko_main_keyword').removeClass('wsko-kw-group-default-main').parents('.keyword_group_wrapper').find('.wsko-default-main').hide();
		});
		for (var i = 1; i <= wsko_max_groups; i++)
		{
			var $items = $('.keyword_group_set_main[data-group="' + i + '"]:checked');
			if ($items.length == 0)
			{
				var max_check = -1;
				var $max_item = null;
				$('.keyword_group_set_main[data-group="' + i + '"]').each(function(index){
					var $this = $(this),
					val = parseInt($this.attr('data-check'));
					if (val > max_check)
					{
						max_check = val;
						$max_item = $this;
					}
				});
				
				if ($max_item != null)
				{
					$max_item.parents('.wsko_main_keyword').addClass('wsko-kw-group-default-main').parents('.keyword_group_wrapper').find('.wsko-default-main').show();
				}
			}
		}
	}
	wsko_set_keyword_defaults();
});