<?php
if (!defined('ABSPATH')) exit;
?>
<div class="modal fade wsko_modal" id="modal_feedback">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">Contact us!</h4>
      </div>
	  <form id="wsko_feedback_form" class="wsko-form-feedback" data-nonce="<?=wp_create_nonce('wsko-feedback')?>">
	    <div class="modal-body">
			<div class="row">
				<div class="form-group">
					<div class="col-sm-9 col-sm-offset-3">	
						<fieldset>
							<input class="wsko-icheck" data-color="green" type="radio" name="type" value="0" checked>
							<label>Feedback</label>
							<input class="wsko-icheck" data-color="red" type="radio" name="type" value="1">
							<label>Error Ticket</label>
							<input class="wsko-icheck" data-color="blue" type="radio" name="type" value="2">
							<label>Question</label>
						</fieldset>
					</div>
				</div>	
				<div class="form-group">
					<div class="col-sm-3">
						<label>Full Name</label>
					</div>
					<div class="col-sm-9">
						<input placeholder="Enter your name (optional)" class="form-control wsko-feedback-name" type="text" name="name">
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-3">
						<label>Email</label>
					</div>
					<div class="col-sm-9">
						<input placeholder="Your contact email adress" class="form-control wsko-feedback-email" type="email" name="email" required>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-3">
						<label>Subject</label>
					</div>
					<div class="col-sm-9">
						<input placeholder="A brief title" class="form-control wsko-feedback-title" type="text" name="title" required>
					</div>
				</div>
				
				<div class="form-group">				
					<div class="col-sm-3">	
						<label>Message</label>
					</div>
					<div class="col-sm-9">
						<textarea rows="10" placeholder="Having problems or a good time?" class="form-control wsko-feedback-msg" name="msg" required></textarea>
					</div>
				</div>	
			</div>	
	    </div>
	    <div class="modal-footer">
		  <button class="btn btn-primary wsko-feedback-submit" type="submit"><i class="fa fa-spin fa-spinner" style="display:none;"></i> Send</button>
		  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	    </div>
	  </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal --> 