
<div id="first">
	<div class="form-group">
		<label for="username">Email</label>
		<input type="text" name="email" class="form-control" placeholder="Username">
	</div>
</div>

<div id="second">
	<div class="form-group">

	</div>
</div>

<div id="third">
	<div class="form-group">

	</div>
</div>


<?php 

if(is_user_logged_in()){
	echo '<div class="sm_container"> Already logged in';
	get_footer();
	exit();
}

if($_POST){
	$username = $wpdb->escape($_POST['uid']);
	$email = $wpdb->escape($_POST['mail']);
	$password = $wpdb->escape($_POST['pwd']);
	$conpassword= $wpdb->escape($_POST['con-pwd']);
	$firstname = $wpdb->escape($_POST['fn']);
	$lastname = $wpdb->escape($_POST['ln']);

	#error checks
	$error = array();

	if(strpos($username, ' ') != false){
		$error['username_space'] = "Username contains Space";
	}

	if(empty($username)){
		$error['username_empty'] = "Enter a Username";
	}

	if(username_exists($username)){
		$error['username_exists'] = "Username is taken";
	}

	if(!is_email($email)){
		$error['email_invalid'] = "Enter a valid Email";
	}

	if(email_exists($email)){
		$error['email_taken'] = "Email already exists";
	}

	if(strcmp($password,$conpassword)){
		$error['password'] = "Password does not match";
	}

	print_r($error);

	if(count($error) == 0){
		$user_id = wp_create_user($username, $password, $email);
		update_user_meta($user_id, 'first_name', $firstname);
		update_user_meta($user_id, 'last_name', $lastname);
		$new_post = array(
			'post_title' => $firstname . ' ' . $lastname,
			'post_status' => 'publish',
			'post_author' => $user_id,
			'post_type' => 'profile'
		);
		$post_id = wp_insert_post($new_post);
		header('Location: ' . $GLOBALS['url'] . '/login');
		exit();
	}

}
?>




<body>
<div class="mm_container"> 
	<form id='signup-form' method = "post">
			<input type="text" name = "fn" placeholder= "Firstname">
			<input type="text" name = "ln" placeholder= "Lastname">
			<input type="text" name = "uid" placeholder= "Username">
			<input type="text" name = "mail" placeholder= "E-mail">
			<input type="password" name = "pwd" placeholder= "Password">
			<input type="password" name = "con-pwd" placeholder= "Confirm Password">
			<button type="submit" name="signup-submit">Signup</button>
	</form>
</div>
</body>