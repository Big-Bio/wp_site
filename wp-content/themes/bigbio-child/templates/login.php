<?php 
/* Template Name: Login Page */
?>

<?php get_header(); ?>

<?php 

if(is_user_logged_in()){
    echo '<div class="sm_container"><h5> Already logged in. </h5>';
    echo '<a href="/beta"> Go to home </a></div>';
	get_footer();
	exit();
}


?>

<div class="sm_container"> 
    <form id="login" action="login" method="post">
        <h1>Login</h1>
        <p class="status_error" ></p>
        <label for="username"><h5>Username</h5></label>
        <input id="username" type="text" name="username">
        <label for="password"><h5> Password </h5></label>
        <input id="password" type="password" name="password">
        <input class="submit_button" type="submit" value="Login" name="submit">
        <?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>
    </form>
</div> 

<?php get_footer(); ?>