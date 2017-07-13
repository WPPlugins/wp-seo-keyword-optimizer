jQuery(document).ready(function($){	
	if ($('#wsko_lazy_beacon').length != 0 && $('#wsko_lazy_beacon').data('post') != undefined) //Is on Post Page
	{
		$.ajax({
			url: ajaxurl,
			type: 'post',
			data: {
				nonce: $('#wsko_lazy_beacon').data('nonce'),
				post: $('#wsko_lazy_beacon').data('post'),
				action : 'wsko_get_post_metabox',
			},
			beforeSend: function()
			{
			},
			success: function(res)
			{
				if (res.success)
				{
					$('#wsko_lazy_beacon').replaceWith(res.result);
					$('#wsko_lazy_beacon_footer').replaceWith(res.result_f);
					jQuery.wsko_set_suggest_data();
					jQuery.wsko_init_post();
					jQuery.wsko_init();
				}
				else
					$('#wsko_lazy_beacon').replaceWith('AJAX Error. Please contact support.');
			}
		});
	}
	else //Is on Plugin Page
	{
		if ($('#wsko_lazy_admin_overview_beacon').length != 0) //Is valid for loading
		{
			$.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					nonce: $('#wsko_lazy_admin_overview_beacon').data('nonce'),
					start_time: $('.wsko-start-time').val(),
					end_time: $('.wsko-end-time').val(),
					action : 'wsko_lazy_overview',
				},
				beforeSend: function()
				{
				},
				success: function(res)
				{
					if (res.success)
					{
						$('#wsko_overview').html(res.overview);
						$('#wsko_keyword_overview').html(res.keywords_view);
						$('#wsko_page_overview').html(res.pages_view);
						$('#wsko_lazy_notices').html(res.notices);
						jQuery.wsko_init();
						jQuery.wsko_init_admin();
						wsko_set_datatables();
					}
					else
						alert('AJAX Error (' + res.msg + '). Please contact support.');
				}
			});
		}
		
		function wsko_load_page_data()
		{
			var pages_data = [];
			$('#wsko_tables_page tr .wsko-unloaded').each(function(index){
				var $this = $(this);
				pages_data.push({"id": $this.data('id'), "url": $this.data('url')});
			});
			$.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					nonce: $('#wsko_tables_page').data('nonce'),
					pages: pages_data,
					action : 'wsko_get_pages'
				},
				beforeSend: function()
				{
				},
				success: function(res)
				{
					if (res.success)
					{
						for (var i in res.result)
						{
							var $row = $('#wsko_tables_page tr .wsko-unloaded[data-id="' + res.result[i].id + '"]').removeClass('wsko-unloaded').parents('tr');
							$row.find('.wsko-post-title').html(res.result[i].res.title);
							if (res.result[i].res.type == 'post')
								$row.find('.wsko-post-button').show().attr('href', $("<textarea/>").html(res.result[i].res.link).text());
						}
					}
				}
			});
		}
		
		function wsko_set_datatables()
		{
			if ($('#wsko_lazy_admin_beacon').length > 0)
			{
				$('#wsko_table_keywords').on('draw.dt', function(){
					$(this).find('[data-toggle="tooltip"]').tooltip(); 
				}).DataTable({
					"processing": true,
					"serverSide": true,
					"ajax": {
						"url": ajaxurl + "?action=wsko_get_tables",
						"data": function ( d ) { 
							d.table = 'keywords';
							d.start_time = $('.wsko-start-time').val();
							d.end_time = $('.wsko-end-time').val();
							d.ref = $('#wsko_lazy_admin_beacon').data('ref');
							d.nonce = $('#wsko_lazy_admin_beacon').data('nonce');
						}
					},
					"order": [[ 1, "desc" ]],
					"pageLength": 25
				});
				
				$('#wsko_tables_page').on('draw.dt', function(){
					wsko_load_page_data();
					$(this).find('[data-toggle="tooltip"]').tooltip(); 
					$('.wsko-show-keywords').each(function(index){
						$(this).click(function(event){
							event.preventDefault();
							;
							
							var $this = $(this),
							$modal = $('#modal_keyword_details').modal('show'),
							$body = $modal.find('.modal-body');
							
							if ($this.hasClass('unloaded'))
							{
								$body.html($modal.find('.modal-body-template').html());
								$.ajax({
									url: ajaxurl,
									type: 'post',
									data: {
										start_time: $('.wsko-start-time').val(),
										end_time: $('.wsko-end-time').val(),
										nonce: $this.data('nonce'),
										url: $this.data('url'),
										action : 'wsko_get_keyword',
									},
									beforeSend: function()
									{
									},
									success: function(res)
									{
										$this.removeClass('unloaded');
										$this.parents('tr').find('.wsko-kd-cache').html(res.result);
										$body.html(res.result);
										$modal.find('.wsko_tables').each(function(index){$(this).DataTable({
												"order": [[ 1, "desc" ]],
												"pageLength": 25
											});
										});
									}
								});
							}
							else
							{
								$body.html($this.parents('tr').find('.wsko-kd-cache').html());
								$modal.find('.wsko_tables').each(function(index){$(this).DataTable({
										"order": [[ 1, "desc" ]],
										"pageLength": 25
									});
								});
							}
						});
					});
				}).DataTable({
					"processing": true,
					"serverSide": true,
					"ajax": {
						"url": ajaxurl + "?action=wsko_get_tables",
						"data": function ( d ) {
							d.table = 'pages';
							d.start_time = $('.wsko-start-time').val();
							d.end_time = $('.wsko-end-time').val();
							d.ref = $('#wsko_lazy_admin_beacon').data('ref');
							d.nonce = $('#wsko_lazy_admin_beacon').data('nonce');
						}
					},
					"order": [[ 1, "desc" ]],
					"pageLength": 25
				});
			}
		}
		wsko_set_datatables();
	}
});