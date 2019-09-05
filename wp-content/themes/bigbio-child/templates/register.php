<?php 
/*Template Name: Register-Page*/
?>

<?php get_header();?>

<script src= "../wp-content/themes/bigbio-child/js/ajax-register-script.js"> </script>

<div class="sm_container" id="email-form">
	<div class="form-group">
	<h2>Create an Account</h2>
	<section> 
		<p id="status" class="error"></p>
		<label for="email"> <h5>Email Address</h5> </label>
	</section>
	<section> 
		<p class="input_prompt">Enter your email address to receive an invitation to register.</p>
		<input type="text" id="email" name="email" class="form-control" placeholder="Enter Email">
	</section>
	</div>
	<?php wp_nonce_field('email_verify', 'security'); ?>
	<button id="submit" class="submit_button" onclick="ajax_email_js()" >Submit</button>
</div>


<div class="sm_container" id="verify">
	<p>
		Please check your email to register and verify your account. 
	</p>
</div>




<?php get_footer(); ?>
