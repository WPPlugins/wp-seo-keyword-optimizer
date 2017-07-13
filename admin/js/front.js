jQuery(document).ready(function($)
{
	//Settings (allways first)
	$('#wsko_settings_save_form').submit(function(event) {
		event.preventDefault();
		
		var $this = $(this);
		$this.find('[type="submit"] > i').show();
		
		$.ajax({
			url: ajaxurl,
			type: 'post',
			data: {
				nonce: $this.data('nonce'),
				form_data: $this.serialize(),
				action : 'wsko_save_settings',
			},
			beforeSend: function()
			{
			},
			success: function(res)
			{
				$this.find('[type="submit"] > i').hide();
				window.location.href = window.location.href.replace( /[\?#].*|$/, "?page=wsko_settings_view&res=" + res.success + "&msg=" + encodeURIComponent(res.msg));
			}
		});
	});
	
	$('.wsko-give-feedback').click(function(event) {
		event.preventDefault();
		
		var $this = $(this),
		$modal = $('#modal_feedback').modal('show');
		
		$modal.find('.wsko-feedback-msg').val('');
		$modal.find('.wsko-feedback-title').val('');
	});
	
	$('#wsko_feedback_form').submit(function(event) {
		event.preventDefault();
		
		var $this = $(this);
		$this.find('[type="submit"] > i').show();
		
		$.ajax({
			url: ajaxurl,
			type: 'post',
			data: {
				nonce: $this.data('nonce'),
				form_data: $this.serialize(),
				action : 'wsko_feedback',
			},
			beforeSend: function()
			{
			},
			success: function(res)
			{
				$this.find('[type="submit"] > i').hide();
				alert('Thank you very much :)');
				$('#modal_feedback').modal('hide');
			}
		});
	});
	
	$('#wsko_delete_cache_btn').click(function(event) {
		event.preventDefault();
		
		var $this = $(this);
		
		if (confirm('You are about to delete your whole Cache. Every dataset older than 90 days is lost forever! Are you sure you want to continue?'))
		{
			$this.find('i').show();
			$.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					nonce: $this.data('nonce'),
					action : 'wsko_delete_cache',
				},
				beforeSend: function()
				{
				},
				success: function(res)
				{
					$this.find('i').hide();
					window.location.href = window.location.href.replace( /[\?#].*|$/, "?page=wsko_settings_view&res=" + res.success + "&msg=" + encodeURIComponent(res.msg));
				}
			});
		}
	});
	
	$('#wsko_delete_recent_cache_btn').click(function(event) {
		event.preventDefault();
		
		var $this = $(this);
		
		if (confirm('You are about to delete your 90-Days-Cache. Are you sure you want to continue?'))
		{
			$this.find('i').show();
			$.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					nonce: $this.data('nonce'),
					action : 'wsko_delete_recent_cache',
				},
				beforeSend: function()
				{
				},
				success: function(res)
				{
					$this.find('i').hide();
					window.location.href = window.location.href.replace( /[\?#].*|$/, "?page=wsko_settings_view&res=" + res.success + "&msg=" + encodeURIComponent(res.msg));
				}
			});
		}
	});
	
	$('#wsko_clear_log').click(function(event) {
		event.preventDefault();
		
		var $this = $(this);
		
		if (confirm('You are about to delete every log report. Are you sure you want to continue?'))
		{
			$this.find('i').show();
			$.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					nonce: $this.data('nonce'),
					action : 'wsko_delete_log_reports',
				},
				beforeSend: function()
				{
				},
				success: function(res)
				{
					$this.find('i').hide();
					window.location.href = window.location.href.replace( /[\?#].*|$/, "?page=wsko_reports_view&res=" + res.success + "&msg=" + encodeURIComponent(res.msg));
				}
			});
		}
	});
	
	jQuery.wsko_init = function wsko_init()
	{
		$('[data-toggle="tooltip"]').tooltip();
		
		$('.wsko_checkbox_target').each(function(){
			$(this).next().remove();
			$(this).iCheck({
				checkboxClass: 'icheckbox_line-blue wsko_icheck_toggle',
				radioClass: 'iradio_line-blue',
				insert: '<div class="icheck_line-icon"></div> Target'
			});
		});
		
		$('.wsko_checkbox_focus').each(function(){
			$(this).next().remove();
			$(this).iCheck({
				checkboxClass: 'icheckbox_line-blue wsko_icheck_toggle',
				radioClass: 'iradio_line-blue',
				insert: '<div class="icheck_line-icon"></div> Focus'
			});
		});
		
		$('.wsko_radio_main_keyword').each(function(){
			$(this).next().remove();
			$(this).iCheck({
				checkboxClass: 'icheckbox_line-blue wsko_main_keyword',
				radioClass: 'iradio_line-blue',
				insert: '<div class="icheck_line-icon"></div> Main Keyword'
			});
		});
		
		$('.wsko_tables').not('.wsko-table-custom').DataTable({
			"order": [[ 1, "desc" ]],
			"pageLength": 25
		});
		
		$('.wsko_tables_post_kw').DataTable({
			"order": [],
			"pageLength": 25
		});
	};
	var wsko_suggest_timeout = 1000,
	$wsko_suggest_kw = $('#wsko_suggest_kw'),
	wsko_suggest_kw_temp = $wsko_suggest_kw.val(),
	wsko_suggest_waiting = false,
	wsko_suggest_time_left = 0,
	$wsko_suggest_post_kw = $('#wsko_add_track_keyword_value'),
	wsko_suggest_post_kw_temp = $wsko_suggest_post_kw.val(),
	wsko_suggest_post_waiting = false,
	wsko_suggest_post_time_left = 0;
	
	jQuery.wsko_set_suggest_data = function wsko_set_suggest_data()
	{
		wsko_suggest_timeout = 1000;
		$wsko_suggest_kw = $('#wsko_suggest_kw');
		wsko_suggest_kw_temp = $wsko_suggest_kw.val();
		wsko_suggest_waiting = false;
		wsko_suggest_time_left = 0;
		$wsko_suggest_post_kw = $('#wsko_add_track_keyword_value');
		wsko_suggest_post_kw_temp = $wsko_suggest_post_kw.val();
		wsko_suggest_post_waiting = false;
		wsko_suggest_post_time_left = 0;
		
		$wsko_suggest_kw.off().on("keyup", function(event){
			if (event.keyCode == 13)
			{
				event.preventDefault();
			}
			
			if ($wsko_suggest_kw.val() == wsko_suggest_kw_temp)
				return false;
			
			wsko_suggest_time_left = wsko_suggest_timeout;
			if (!wsko_suggest_waiting)
			{
				$('#wsko_suggest_loading').fadeIn('slow');
				wsko_suggest_waiting = true;
				wsko_suggest_timeout_progress(false);
			}
		});
		
		$wsko_suggest_post_kw.off().on("keyup", function(event){
			if (event.keyCode == 13)
			{
				event.preventDefault();
			}
			
			if ($wsko_suggest_post_kw.val() == wsko_suggest_post_kw_temp)
				return false;
			
			wsko_suggest_post_time_left = wsko_suggest_timeout;
			if (!wsko_suggest_post_waiting)
			{
				$('#wsko_suggest_post_loading').fadeIn('slow');
				wsko_suggest_post_waiting = true;
				wsko_suggest_timeout_progress(true);
			}
		});
	};
	
	function wsko_suggest_timeout_progress(/*$elem,*/ post)
	{		
		var timeout = false;
		
		if (post)
		{
			//var perc = (wsko_suggest_timeout - wsko_suggest_post_time_left) / wsko_suggest_timeout * 100;
			//$elem.attr('data-progress', perc);
			
			if (wsko_suggest_post_time_left > 0)
			{
				wsko_suggest_post_time_left -= 500;
				setTimeout(function()
				{
					wsko_suggest_timeout_progress(true);
				}, 500);
			}
			else
				timeout = true;
		}
		else
		{
			//var perc = (wsko_suggest_timeout - wsko_suggest_time_left) / wsko_suggest_timeout * 100;
			//$elem.attr('data-progress', perc);
			
			if (wsko_suggest_time_left > 0)
			{
				wsko_suggest_time_left -= 500;
				setTimeout(function()
				{
					wsko_suggest_timeout_progress(false);
				}, 500);
			}
			else
				timeout = true;
		}
			
		if (timeout)
		{
			var val = '';
			if (post)
			{
				$('#wsko_suggest_post_loading').hide();
				wsko_suggest_post_waiting = false;
				val = $wsko_suggest_post_kw.val();
			}
			else
			{
				$('#wsko_suggest_loading').hide();
				wsko_suggest_waiting = false;
				val = $wsko_suggest_kw.val();
			}
			$.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					keyword: val,
					action : 'wsko_get_keyword_suggests'
				},
				beforeSend: function()
				{
				},
				success: function(res)
				{
					if (post)
						$("#" + $wsko_suggest_post_kw.attr('list')).html(res.result);
					else
						$("#" + $wsko_suggest_kw.attr('list')).html(res.result);
				}
			});
			
			if (post)
				wsko_suggest_post_kw_temp = $wsko_suggest_post_kw.val();
			else
				wsko_suggest_kw_temp = $wsko_suggest_kw.val();
		}
	};
});