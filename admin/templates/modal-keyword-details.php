<?php
if (!defined('ABSPATH')) exit;
?>
<div class="modal fade wsko_modal" id="modal_keyword_details">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">Keyword Details</h4>
      </div>
	  <div class="modal-body-template" style="display:none;">
			<div style="text-align:center;">
			<i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>
			<span class="sr-only">Loading...</span>
			</div>
	  </div>
	  <div class="modal-body wsko_modal_table">
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	  </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal --> 