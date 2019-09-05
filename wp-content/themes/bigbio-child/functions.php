<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_separate', trailingslashit( get_stylesheet_directory_uri() ) . 'ctc-style.css', array( 'style','mfn-base','mfn-btn','mfn-icons','mfn-grid','mfn-layout','mfn-shortcodes','mfn-variables','mfn-style-simple','mfn-animations','mfn-colorpicker','mfn-jquery-ui','mfn-jplayer','mfn-prettyPhoto','mfn-responsive-1240','mfn-responsive','mfn-custom' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 100 );

// END ENQUEUE PARENT ACTION

######################################################################
#####				  	 Custom Post Types 						 #####
######################################################################

$GLOBALS['url'] = 'http://big-bio.org/beta';

function module_post_type(){
	$labels = array(
		'name' => 'Modules',
		'singular_name' => 'Module',
		'add_new' => 'Add New Module',
		'all_items' => 'All Modules',
		'add_new_item' => 'Add New Module',
		'edit_item' => 'Edit Module',
		'new_item' => 'New Module',
		'view_item' => 'View Module',
		'search_item' => 'Search Module',
		'not_found' => 'No module found',
		'not_found_in_trash' => 'No modules in trash',
		'parent_item_colon' => 'Parent Module'
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'has_archive' => true,
		'publicly_queryable' => true,
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => 'false',
		'supports' => array(
			'title',
			'editor',
			'author',
			'excerpt',
			'thumbnail',
			'revisions',
			'custom-fields',
			'page-attributes',
			'post-formats'
		),
		'taxonomies' => array(
			'category',
			'post_tag'
		),
		'menu_position' => 1,
		'exclude_from_search' => false
	);
	register_post_type('module', $args);
}

// add_action('init','module_post_type');

function profile_page_post_type(){
	$labels = array(
		'name' => 'Profile Pages',
		'singular_name' => 'Profile Page',
		'add_new' => 'Add Profile Page',
		'all_items' => 'All Profile Pages',
		'add_new_item' => 'Add New Profile Page',
		'edit_item' => 'Edit Profile Page',
		'new_item' => 'New Profile Page',
		'view_item' => 'View Profile Page',
		'search_item' => 'Search Profile Page',
		'not_found' => 'No profile pages found',
		'not_found_in_trash' => 'No profile pages in trash'
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'has_archive' => true,
		'publicly_queryable' => true,
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => 'false',
		'supports' => array(
			'title',
			'editor',
			'author',
			'excerpt',
			'thumbnail',
			'revisions',
			'custom-fields',
			'page-attributes',
			'post-formats'
		),
		'taxonomies' => array(
			'category',
			'post_tag'
		),
		'menu_position' => 1,
		'exclude_from_search' => false
	);
	register_post_type('profile', $args);
}

add_action('init','profile_page_post_type');

######################################################################
#####				  	 User Status 	 						 #####
######################################################################

function display_user(){
	$current_user = wp_get_current_user();
	if(!$current_user->exists()){
		echo '<a href="http://big-bio.org/beta/login">Login</a>';
		return false;
	}
	$fname = get_user_meta(get_current_user_id(), 'first_name', true);
	$lname = get_user_meta(get_current_user_id(), 'last_name', true);
	if(!empty($fname) && !empty($lname)){
		echo '<div class="login_wrapper">';
		echo 'Welcome ';
		echo '<a href="' . $GLOBALS['url'] . '/profile/'. $fname . '-' . $lname .'">' . $fname . ' ' . $lname . '</a>';
		echo '<br>';
		echo '<a href='.wp_logout_url('/beta').'>Logout</a>';
		echo '</div>';
	}
	
}

function check_logged(){
	if(!is_user_logged_in()){
		echo 'Please log in.';
		get_footer();
		exit();
	}
}


######################################################################
#####				 	 	 Roles			 					 #####
######################################################################

$cap = array(
	'edit_posts' => true,
	'read' => true
);
add_role('contributor','Contributor',$cap);

$cap = array();
add_role('user','User',$cap);

function disable_admin_bar() {
   if (current_user_can('administrator')) {
     show_admin_bar(true); 
   } else {
     show_admin_bar(false);
   }
}
add_action('after_setup_theme', 'disable_admin_bar');

######################################################################
#####				  	      Menu       	 					 #####
######################################################################

function my_wp_nav_menu_args( $args = '' ) {
 
if( is_user_logged_in() ) { 
    $args['menu'] = 'logged-in';
} else { 
    $args['menu'] = 'logged-out';
} 
    return $args;
}

add_filter( 'wp_nav_menu_args', 'my_wp_nav_menu_args' );

######################################################################
#####				  	      Ajax      	 					 #####
######################################################################

function ajax_login_init(){

    wp_register_script('ajax-login-script', '/wp-content/themes/bigbio-child/js/ajax-login-script.js', array('jquery') ); 
    wp_enqueue_script('ajax-login-script');

    wp_localize_script( 'ajax-login-script', 'ajax_login_object', array( 
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'redirecturl' => $GLOBALS['url'] . '/dashboard',
    ));

    // Enable the user with no privileges to run ajax_login() in AJAX
    add_action( 'wp_ajax_nopriv_ajaxlogin', 'ajax_login' );
}

if (!is_user_logged_in()) {
    add_action('init', 'ajax_login_init');
}

function ajax_login(){
    check_ajax_referer( 'ajax-login-nonce', 'security' );

    $info = array();
    $info['user_login'] = $_POST['username'];
    $info['user_password'] = $_POST['password'];
    $info['remember'] = true;

    $error = array();

    if( !isset($info['user_login']) || empty($info['user_login'])){
		array_push($error,'Username cannot be empty.');
	}

	if( !isset($info['user_password']) || empty($info['user_password'])){
		array_push($error,'Password cannot be empty.');
	}

	if(!empty($error)){
		echo json_encode(array('loggedin' => false, 'message'=> $error));
		die();
		return;
	}

    $user_signon = wp_signon( $info, false );
    if ( is_wp_error($user_signon) ){
        echo json_encode(array('loggedin'=>false, 'message'=> array('Invalid username or password.')));
    } else {
        echo json_encode(array('loggedin'=>true, 'message'=> array() ));
    }

    die();
}


function setup_script(){
	$uri = $_SERVER['REQUEST_URI'];
	$location = basename($uri);
	$role = '';
	if(current_user_can('administrator')){
		$role = 'administrator';
	}
	else if(current_user_can('contributor')){
		$role = 'contributor';
	}
	else{
		$role = 'user';
	}
	if($location == 'module-submit' && ($role == 'administrator' || $role == 'contributor')){
		wp_enqueue_script('ajax-mod-submit','/wp-content/themes/bigbio-child/js/ajax-mod-submit.js', array('jquery'));
		wp_localize_script( 'ajax-mod-submit', 'ajax_modsubmit_object', array( 
		    'ajaxurl' => admin_url( 'admin-ajax.php' ),
		    'redirecturl' => $GLOBALS['url'] . '/dashboard',
		));
	}
	if($location == 'register'){
		wp_enqueue_script('ajax-send-email','/wp-content/themes/bigbio-child/js/ajax-send-email.js', array('jquery'));
		wp_localize_script( 'ajax-send-email', 'ajax_sendemail_object', array( 
		    'ajaxurl' => admin_url( 'admin-ajax.php' ),
		    'redirecturl' => $GLOBALS['url'] . '/dashboard',
		));
	}
	if(substr($uri,0,14) == '/beta/r/?vkey='){
		wp_enqueue_script('ajax-register','/wp-content/themes/bigbio-child/js/ajax-register.js', array('jquery'));
		wp_localize_script( 'ajax-register', 'ajax_register_object', array( 
		    'ajaxurl' => admin_url( 'admin-ajax.php' ),
		    'redirecturl' => $GLOBALS['url'] . '/welcome',
		));
		wp_enqueue_script('ajax-json','/wp-content/themes/bigbio-child/lists/lists.json', array('jquery'));
		wp_localize_script( 'ajax-json', 'ajax_json_object', array( 
		    'ajaxurl' => admin_url( 'admin-ajax.php' ),
		    'redirecturl' => $GLOBALS['url'] . '/dashboard',
		));
	}
	if(substr($uri,0,17) == '/beta/module/?id='){
		wp_enqueue_script('ajax-module','/wp-content/themes/bigbio-child/js/setup_module.js', array('jquery'));
		wp_localize_script( 'ajax-module', 'ajax_module_object', array( 
		    'ajaxurl' => admin_url( 'admin-ajax.php' ),
		    'redirecturl' => $GLOBALS['url'] . '/dashboard',
		));
	}
	if($location == 'dashboard' && $role != 'user'){
		wp_enqueue_script('ajax-module','/wp-content/themes/bigbio-child/js/ajax-dashboard.js', array('jquery'));
		wp_localize_script( 'ajax-module', 'ajax_module_object', array( 
		    'ajaxurl' => admin_url( 'admin-ajax.php' ),
		    'redirecturl' => $GLOBALS['url'] . '/dashboard',
		));
	}
	if(current_user_can('administrator') && $location == 'admin'){
		wp_enqueue_script('ajax-module','/wp-content/themes/bigbio-child/js/ajax-adminboard.js', array('jquery'));
		wp_localize_script( 'ajax-module', 'ajax_module_object', array( 
		    'ajaxurl' => admin_url( 'admin-ajax.php' ),
		    'redirecturl' => $GLOBALS['url'] . '/admin',
		));
	}
	if(substr($uri,0,22) == '/beta/module/edit/?id='){
		wp_enqueue_script('ajax-module','/wp-content/themes/bigbio-child/js/ajax-mod-edit.js', array('jquery'));
		wp_localize_script( 'ajax-module', 'ajax_module_object', array( 
		    'ajaxurl' => admin_url( 'admin-ajax.php' ),
		    'redirecturl' => $GLOBALS['url'] . '/dashboard',
		));
	}
	if($location == 'apply'){
		wp_enqueue_script('ajax-module','/wp-content/themes/bigbio-child/js/ajax-apply.js', array('jquery'));
		wp_localize_script( 'ajax-module', 'ajax_module_object', array( 
		    'ajaxurl' => admin_url( 'admin-ajax.php' ),
		    'redirecturl' => $GLOBALS['url'] . '/dashboard',
		));
	}
}
add_action('wp_ajax_ajaxmodulestatusupdate', 'update_module_status');
add_action('wp_ajax_ajaxappapprove', 'approve_app');
add_action('wp_ajax_ajaxapply', 'process_app');
add_action('wp_ajax_ajaxgetdata', 'get_preview_data');
add_action('wp_ajax_ajaxpreloadedit', 'ajax_preload_edit');
add_action('wp_ajax_ajaxmodedit', 'ajax_mod_edit');
add_action('wp_ajax_ajaxpreloadadmin', 'ajax_preload_admin');
add_action('wp_ajax_ajaxloadmore', 'ajax_load_more_modules');
add_action('wp_ajax_ajaxpreloaddash', 'ajax_preload_dash');
add_action('wp_ajax_nopriv_ajaxdisplaymodule', 'ajax_display_module_data');
add_action('wp_ajax_ajaxdisplaymodule', 'ajax_display_module_data');
add_action('wp_ajax_nopriv_ajaxjson', 'ajax_load_json');
add_action('wp_ajax_nopriv_ajaxregister1', 'ajax_register1');
add_action('wp_ajax_nopriv_ajaxregister2', 'ajax_register2');
add_action('wp_ajax_nopriv_ajaxsendemail', 'ajax_send_mail');
add_action('wp_ajax_ajaxmodsubmit', 'ajax_mod_submit');
add_action('init', 'setup_script');

function getSqlData($sql, $type, $params){
	$conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	$stmt = mysqli_stmt_init($conn);
		if(!mysqli_stmt_prepare($stmt, $sql)){
			return array('error' => 'Prepare failure', 'status' => false);
		}
		else{
			call_user_func_array("mysqli_stmt_bind_param", array_merge(array($stmt, $type), $params));
			mysqli_stmt_execute($stmt);
		}
	if($stmt->error != ''){
		return array('error' => $stmt->error, 'status' => false);
	}
	else{
		$result = mysqli_stmt_get_result($stmt);
		$data = array();
		while($row = mysqli_fetch_assoc($result)){
			$data[] = $row;
		}
		return array('error' => 'None', 'status' => true, 'data' => $data);
	}
}


function checkPOST($title, $content, $tags, $doi, $collab, $ack, $sup_notes, $pdf, $ppt){
	$status = true;
	$error = array();

	if( !isset($title) || empty($title)){
		$error[] = 'Title cannot be empty.';
		$status = false;
	}
	if( !isset($content) || empty($content)){
		$error[] = 'Module summary cannot be empty';
		$status = false;
	}

	if( !isset($tags) || empty($tags)){
		$error[] = 'Tags cannot be empty';
		$status = false;
	}


	$pptMimes= array(
		'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'ppt' => 'application/vnd.ms-powerpoint',
		'pdf' => 'application/pdf'
	);
	$pdfMimes= array(
		'pdf' => 'application/pdf',
		'doc' => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'

	);

	$maxsize = 250000000;

	$pptInfo = wp_check_filetype(basename($ppt['name']), $pptMimes);
	$pdfInfo = wp_check_filetype(basename($pdf['name']), $pdfMimes);

	if($pdf != 'ignore'){
		if($pdf['size'] == 0){
			$error[] = 'Worksheet is required';
			$status = false;
		}
		else{
			if(!$pdfInfo[ext]){
				$error[] = 'Worksheet must be .PDF, .DOC, or .DOCX';
				$status = false;
			}
			if($pdf['size'] > $maxsize){
				$error[] = 'Worksheet exceeds 25mb';
				$status = false;
			}		
		}
	}

	if($ppt != 'ignore'){
		if($ppt['size'] == 0){
			$error[] = 'Presentation slides are required';
			$status = false;
		}
		else{
			if(!$pptInfo[ext]){
				$error[] = 'Presentation slides must be .PPT, .PPTX, or .PDF';
				$status = false;
			}

			if($ppt['size'] > $maxsize){
				$error[] = 'Presentation slides exceed 25mb';
				$status = false;
			}
		}
	}

	return [$status, $error];
}

function terminate($error, $status){
	echo json_encode(array('message' => $error, 'status' => $status));
	die();
}

function ajax_mod_submit(){
	check_ajax_referer( 'ajax-module-nonce', 'security' );
	$title = $_POST['title'];
	$content = $_POST['content'];
	$keyterms = $_POST['keyterms'];
	$doi = $_POST['doi'];
	$collab = $_POST['collab'];
	$ack = $_POST['ack'];
	$sup_notes = $_POST['sup_notes'];

	$pdf = $_FILES['worksheet'];
	$ppt = $_FILES['presentation'];

	$data = array(&$title, &$content, &$keyterms, &$doi, &$collab, &$ack, &$sup_notes);

	foreach ($data as &$item){
		if(empty($item) || !isset($item)){
			$item = '';
		}
	}

	$validate_arr = checkPOST($title, $content, $keyterms, $doi, $collab, $ack, $sup_notes, $pdf, $ppt);

	$status = $validate_arr[0];
	$error = $validate_arr[1];
	
	if($status){
		$user = wp_get_current_user();
		$author_name = $user->user_firstname . ' ' . $user->user_lastname;
		$author_id = get_current_user_id();
		$upload_pdf = wp_handle_upload($pdf,array('test_form' => FALSE));
		$upload_ppt = wp_handle_upload($ppt,array('test_form' => FALSE));

		$worksheet_name = $pdf['name'];
		$worksheet_path = $upload_pdf['file'];
		$worksheet_url = $upload_pdf['url'];

		$presentation_name = $ppt['name'];
		$presentation_path = $upload_ppt['file'];
		$presentation_url = $upload_ppt['url'];


		$conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
		$sql = "INSERT INTO Modules(author_id, author_name, title, content, sup_notes, ack, collab, doi, keyterms, worksheet_path, worksheet_url, presentation_path, presentation_url, worksheet_name, presentation_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,? ,?, ?, ?);";
		$stmt = mysqli_stmt_init($conn);
		if(!mysqli_stmt_prepare($stmt, $sql)){
			$error[] = 'Database connection error';
			$status = false;
		}
		else{
			mysqli_stmt_bind_param($stmt, "issssssssssssss", $author_id, $author_name, $title, $content, $sup_notes, $ack, $collab, $doi, $keyterms, $worksheet_path, $worksheet_url, $presentation_path, $presentation_url, $worksheet_name, $presentation_name);
			mysqli_execute($stmt);
			if($stmt->error != ''){
				$status = false;
			}
		}

	}
		
	echo json_encode(array('message' => $error, 'status' => $status, 'db_error' => $stmt->error));
	die();
}

function ajax_mod_edit(){
	check_ajax_referer( 'ajax-module-nonce', 'security' );

	$title = $_POST['title'];
	$content = $_POST['content'];
	$keyterms = $_POST['keyterms'];
	$doi = $_POST['doi'];
	$collab = $_POST['collab'];
	$ack = $_POST['ack'];
	$sup_notes = $_POST['sup_notes'];
	$module_id = intval($_POST['module_id']);

	$wrk = $_FILES['worksheet'];
	$pre = $_FILES['presentation'];

	$changed_work = true;
	$changed_pres = true;
	if($_POST['wrk_haschanged'] == 'false'){
		$changed_work = false;
		$wrk = 'ignore';
	}
	if($_POST['pres_haschanged'] == 'false'){
		$changed_pres = false;
		$pre = 'ignore';
	}

	$validate_arr = checkPOST($title, $content, $keyterms, $doi, $collab, $ack, $sup_notes, $wrk, $pre);

	$status = $validate_arr[0];
	$error = $validate_arr[1];

	if($status){
		$author_id = get_current_user_id();
		$conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
		$stmt = mysqli_stmt_init($conn);

		$additional = '';

		if($changed_work){
			$additional .= ', worksheet_url= ?, worksheet_path= ?, worksheet_name= ?';
			$sql2 = "SELECT worksheet_path FROM Modules WHERE author_id= ? AND module_id= ?;";
			$path= getSqlData($sql2, "ii", array(&$author_id, &$module_id))['data'][0]['worksheet_path'];
			if(file_exists($path)){
				wp_delete_file($path);
				$response = true;

				$upload_pdf = wp_handle_upload($wrk,array('test_form' => FALSE));
				$worksheet_name = $wrk['name'];
				$worksheet_path = $upload_pdf['file'];
				$worksheet_url = $upload_pdf['url'];
			}
		}
		if($changed_pres){
			$additional .= ', presentation_url= ?, presentation_path= ?, presentation_name= ?';
			$sql2 = "SELECT presentation_path FROM Modules WHERE author_id= ? AND module_id= ?;";
			$path= getSqlData($sql2, "ii", array(&$author_id, &$module_id))['data'][0]['presentation_path'];
			if(file_exists($path)){
				wp_delete_file($path);
				$response = true;

				$upload_ppt = wp_handle_upload($pre,array('test_form' => FALSE));
				$presentation_name = $pre['name'];
				$presentation_path = $upload_ppt['file'];
				$presentation_url = $upload_ppt['url'];
			}
		}

		$sql = "UPDATE Modules SET title= ?, content=?, keyterms=?, doi=?, collab=?, ack=?, sup_notes=?, status=?, date_modified=?" . $additional . " WHERE module_id= ? AND author_id= ?";
		if(!mysqli_stmt_prepare($stmt, $sql)){
			$status = false;
		}
		else{
			$state = 'pending';
			$dt = new DateTime("NOW", new DateTimeZone('America/Los_Angeles'));
			$date = $dt->format("Y-m-d H:i:s");
			if($changed_pres && $changed_work){
				mysqli_stmt_bind_param($stmt, "sssssssssssssssii", $title, $content, $keyterms, $doi, $collab, $ack, $sup_notes, $state, $date, $worksheet_url, $worksheet_path, $worksheet_name, $presentation_url, $presentation_path, $presentation_name, $module_id, $author_id);
				mysqli_execute($stmt);
			}
			else if($changed_work){
				mysqli_stmt_bind_param($stmt, "ssssssssssssii", $title, $content, $keyterms, $doi, $collab, $ack, $sup_notes, $state, $date, $worksheet_url, $worksheet_path, $worksheet_name, $module_id, $author_id);
				mysqli_execute($stmt);
			}
			else if($changed_pres){
				mysqli_stmt_bind_param($stmt, "ssssssssssssii", $title, $content, $keyterms, $doi, $collab, $ack, $sup_notes, $state, $date, $presentation_url, $presentation_path, $presentation_name, $module_id, $author_id);
				mysqli_execute($stmt);
			}
			else{
				mysqli_stmt_bind_param($stmt, "sssssssssii", $title, $content, $keyterms, $doi, $collab, $ack, $sup_notes, $state, $date, $module_id, $author_id);
				mysqli_execute($stmt);
			}

		}

	}

	echo json_encode(array('message' => $error, 'status' => $status, 'test' => $response));
	die();
}

function ajax_send_mail(){
	check_ajax_referer( 'email_verify', 'security' );

	$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
	$error = array();
	$status = true;


	if(empty($email)){
		$error[] = 'Email cannot be empty';
		terminate($error, false);
	}

	if(!$email){
		$error[] = 'Invalid input';
		terminate($error, false);
	}

	if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
		$error[] = 'Please enter a valid email';
		terminate($error, false);
	}

	if($status){
		$mysqli = NEW MySQLi(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
		$email = $mysqli->real_escape_string($email);

		$check_exist = $mysqli->query(" SELECT * FROM `pre_accounts` WHERE email='$email'");
		$wp_check_exist = email_exists($email);

		if($check_exist->num_rows == 0 && !$wp_check_exist){
			$vkey = md5(time().$email);


			$insert = $mysqli->query("INSERT INTO pre_accounts(email,vkey) VALUES('$email', '$vkey')");

			if($insert){
				$to = $email;
				$subject = 'Big-Bio 	Email Verification';
				$message = "<a href='" . $GLOBALS['url'] . "/r?vkey=$vkey'>Verify Link</a>";
				$headers = "";
				$headers .= "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				$x = mail($to,$subject,$message,$headers);
			}
			else{
				
			}
		}
		else{
			$error[] = 'Email already exists';
			$status = false;
		}

	}

	terminate($error, $status);
}

function ajax_load_json(){
	$get_file = file_get_contents('../wp-content/themes/bigbio-child/lists/lists.json');
	echo json_encode($get_file);
	die();
}

function ajax_register1(){
	check_ajax_referer( 'verify_one', 'security' );

	$username = $_POST['username'];
	$password1 = $_POST['password1'];
	$password2 = $_POST['password2'];

	$fname = $_POST['fname'];
	$lname = $_POST['lname'];

	$error = array();
	$status1 = true;
	$status2 = true;
	$status = true;

	if(empty($fname) || empty($lname)){
		$error[] = 'First and Last Name cannot be empty';
		$status1 = false;
	}
	if(empty($username)){
		$error[] = 'Username cannot be empty';
		$status1 = false;
	}
	if(empty($password1) || empty($password2)){
		$error[] = 'Password cannot be empty';
		$status1 = false;
	}

	if($status1){
		if(username_exists($username)){
			$error[] = 'Username exists';
			$status2 = false;
		}
		if($password1 != $password2){
			$error[] = 'Passwords do not match';
			$status2 = false;
		}
	}

	if(!$status1 || !$status2){
		$status = false;
	}

	terminate($error, $status);
}

function check_valid_vkey($vkey){
	$arr = array();
	$mysqli = NEW MySQLi(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	$key = $mysqli->real_escape_string($vkey);
	$resultSet = $mysqli->query("SELECT email,verified,vkey FROM pre_accounts WHERE verified=0 AND vkey='$key' LIMIT 1");
	if($resultSet->num_rows == 1){
		return mysqli_fetch_assoc($resultSet)['email'];
	}
	else{
		return false;
	}
	
}

function checkRequiredFields($key,$required, &$error, &$status){
	if(in_array($key,$required)){
			$error[] = $key;
			$status = false;
	}
}

function ajax_register2(){
	//setup incoming data
	$keys = array('fname','lname','username','password1','password2','age','gender','country','state','degree','years_schooling','rank_bio','rank_stats','rank_cs','occupation','employer','primary_field','ethnicity','reason','vkey');
	$fields = 'user_ID, username, password, first_name, last_name, email, age, gender, ethnicity, country, state, highest_degree_earned, years_schooling, rank_of_knowledge_biology, rank_of_knowledge_stats, rank_of_knowledge_cs, reason, occupation, employer, primary_field';
	$required = array('age','gender','country','degree','ethnicity','years_schooling','rank_bio','rank_stats','rank_cs','occupation','primary_field','reason');
	$data = array();
	$error = array();
	$status = true;
	$reqStatus = true;

	foreach($keys as $key){
		$val = $_POST[$key];
		if(is_array($val)){
			$data[$key] = implode(',', $val);
		}
		else{
			if(is_null($val) || empty($val)){
				checkRequiredFields($key,$required, $error, $reqStatus);
				$data[$key] = '';
			}
			else{
				$data[$key] = $_POST[$key];
			}
		}
	}
	$data['password'] = $_POST['password1'];

	if(!$reqStatus){
		$status = false;
		echo json_encode(array('message' => array(), 'req_list' => $error, 'status' => false, 'required' => false));
		die();
	}
	
	//get email, if email is false then exit
	$data['email'] = check_valid_vkey($data['vkey']);
	if(!$data['email']){
		terminate($error, false);
	}

	//wordpress account setup
	$user_id = wp_create_user($data['username'], $data['password'], $data['email']);
	if(is_wp_error($user_id)){
		terminate(array('WP Account Creation Error'), false);
	}
	global $wpdb;
	$hashword = $wpdb->get_results("SELECT user_pass FROM wpstg1_users WHERE ID='$user_id';")[0]->user_pass;
	$data['user_id'] = $user_id;
	$data['password'] = $hashword;

	$args = array('ID' => $user_id, 'first_name' => $data['fname'], 'last_name' => $data['lname']);
	wp_update_user($args);

	//connect to database
	$conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	//prepare statements
	$sql2 = "INSERT INTO Users (" . $fields . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
	//initialize prepare statements
	$stmt = mysqli_stmt_init($conn);
	//if failed to prepare, then exit
	if(!mysqli_stmt_prepare($stmt, $sql2)){
		$status = false;
		$error[] = 'Database Error';
	}
	//else insert data into db
	else{
		mysqli_stmt_bind_param($stmt, "isssssisssssiiiissss", $data['user_id'], $data['username'], $data['password'], $data['fname'], $data['lname'], $data['email'], $data['age'], $data['gender'], $data['ethnicity'], $data['country'], $data['state'] , $data['degree'], $data['years_schooling'], $data['rank_bio'], $data['rank_stats'], $data['rank_cs'], $data['reason'], $data['occupation'] , $data['employer'], $data['primary_field']);
		$x = mysqli_stmt_execute($stmt);
		if($stmt->error == ''){
			$sql3 = "DELETE FROM pre_accounts WHERE email= ?";
			$stmt = mysqli_stmt_init($conn);
			if(!mysqli_stmt_prepare($stmt, $sql3)){
				$status = false;
				$error[] = 'Pre_account Error';
			}
			else{
				mysqli_stmt_bind_param($stmt, "s", $data['email']);
				mysqli_stmt_execute($stmt);
			}
		}
	}

	//delte this when DONE*********************************************************************************
	// wp_delete_user($user_id);

	echo json_encode(array('message' => $error, 'status' => $status, 'required'=>$reqStatus, 'db_error' => $data));
	die();
}

function ajax_display_module_data(){
	$module_id = intval($_POST['module_id']);
	if($module_id == 0){
		terminate(array('Invalid ID: 1'), false);
	}
	$error = array();
	$status = true;
	$data = array();

	$conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	$sql = "SELECT * FROM Modules WHERE module_id= ? LIMIT 1;";
	$stmt = mysqli_stmt_init($conn);
	if(!mysqli_stmt_prepare($stmt, $sql)){
		terminate(array('Invalid ID: 2'), false);
	}
	else{
		mysqli_stmt_bind_param($stmt, "i", $module_id);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);

		if($result->num_rows != 1){
			terminate(array('Invalid ID: 3'), false);
		}

		while($row = mysqli_fetch_assoc($result)){
			$data['author_name'] = $row['author_name'];
			$data['title'] = $row['title'];
			$data['content'] = $row['content'];
			$data['sup_notes'] = $row['sup_notes'];
			$data['ack'] = $row['ack'];
			$data['collab'] = $row['collab'];
			$data['doi'] = $row['doi'];
			$data['worksheet_url'] = $row['worksheet_url'];
			$data['presentation_url'] = $row['presentation_url'];
		}
	}

	echo json_encode(array('message' => $error, 'status' => $status, 'info'=> $data));
	die();
}



function ajax_preload_dash(){
	$user_id = get_current_user_id();
	$sql = "SELECT title, status, date_modified, module_id FROM Modules WHERE author_id= ? ORDER BY date_modified DESC LIMIT 1;";
	$response = getSqlData($sql, "i", array(&$user_id));

	$sql2 = "SELECT MIN(date_modified) AS date_modified FROM Modules WHERE author_id= ?;";
	$response2 = getSqlData($sql2, "s", array(&$user_id));
	
	echo json_encode(array('message' => $response['error'], 'status' =>  $response['status'], 'rows'=> $response['data'], 'last_date' => $response2['data'][0]['date_modified']));
	die();
}

function ajax_load_more_modules(){
	$user_id = get_current_user_id();
	$last_date = $_POST['last_date'];
	$sql = "SELECT title, status, date_modified, module_id FROM Modules WHERE author_id= ? AND date_modified < ? ORDER BY date_created DESC LIMIT 1;";
	$response = getSqlData($sql, "is", array(&$user_id, &$last_date));

	
	echo json_encode(array('message' => $response['error'], 'status' =>  $response['status'], 'rows'=> $response['data']));
	die();
}

function ajax_preload_admin(){
	$sql = "SELECT author_name, title, status, date_modified, module_id FROM Modules WHERE status=? ORDER BY date_modified DESC;";
	$state = 'pending';
	$response = getSqlData($sql, "s", array(&$state));

	$sql2 = "SELECT user_ID, first_name, last_name, cv_url, highest_degree_earned FROM Users WHERE applied=? AND status=?;";
	$applied = 1;
	$state = 'user';
	$response2 = getSqlData($sql2, "is", array(&$applied, &$state));

	$x = array($response, $response2);

	terminate($x, false);
}

function ajax_load_more_admin(){

}

function ajax_preload_edit(){
	$current_user = get_current_user_id();
	$module_id = intval($_POST['module_id']);

	$sql = "SELECT title, content, sup_notes, ack, collab, doi, keyterms, worksheet_name, presentation_name FROM Modules WHERE author_id= ? AND module_id = ?;";
	$response = getSqlData($sql, "ii", array(&$current_user, &$module_id));

	if(count($response['data']) != 0){
		terminate($response, true);
	}
	else{
		terminate(array('Cannot edit this module'), false);
	}
}

function get_preview_data(){
	$module_id = $_POST['module_id'];
	if(current_user_can('administrator')){
		$sql = "SELECT title, keyterms, doi, collab, content, ack, worksheet_url AS worksheet, presentation_url AS presentation, author_name AS author FROM Modules WHERE module_id= ? LIMIT 1;";
		$response = getSqlData($sql, "i", array(&$module_id));
	}
	else{
		$current_user = get_current_user_id();
		$sql = "SELECT title, keyterms, doi, collab, content, ack, worksheet_url AS worksheet, presentation_url AS presentation, author_name AS author FROM Modules WHERE author_id= ? AND module_id= ? LIMIT 1;";
		$response = getSqlData($sql, "ii", array(&$current_user, &$module_id));
	}

	terminate($response, true);
}

function process_app(){
	$cv = $_FILES['cv'];
	$degree = $_POST['degree'];
	$phone = $_POST['phone'];
	$status = true;
	$error = array();

	$pdfMimes= array(
		'pdf' => 'application/pdf',
		'doc' => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'

	);

	$maxsize = 400000;

	$pdfInfo = wp_check_filetype(basename($cv['name']), $pdfMimes);
	if($cv['size'] == 0){
		$error[] = 'CV is required';
		$status = false;
	}
	else{
		if(!$pdfInfo['ext']){
			$error[] = 'CV must be .PDF, .DOC, or .DOCX';
			$status = false;
		}
		if($cv['size'] > $maxsize){
			$error[] = 'Worksheet exceeds 4mb';
			$status = false;
		}		
	}

	if(empty($degree) || $degree == 'null'){
		$error[] = 'Please select your highest degree';
		$status = false;
	}

	if(empty($phone) || !isset($phone)){
		$error[] = 'Phone number cannot be empty';
		$status = false;
	}
	// else if(!preg_match('/^[0-9]{3}-[0-9]{4}-[0-9]{4}$/', $phone)) {
	//   	$error[] = 'Invalid phone number format';
	//   	$error[] = $phone;
	//   	$status = false;
	// }

	if($status){
		$phone = str_replace('-','',$phone );
		$user_id = get_current_user_id();

		$upload = wp_handle_upload($cv,array('test_form' => FALSE));
		$name = $cv['name'];
		$path = $upload['file'];
		$url = $upload['url'];

		$conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
		$sql = "UPDATE Users SET applied=?, phone=?, cv_name=?, cv_url=?, cv_path=? WHERE user_ID=?; ";
		$stmt = mysqli_stmt_init($conn);
		if(!mysqli_stmt_prepare($stmt, $sql)){
			terminate(array('DB Error'), false);
		}
		else{
			$t = 1;
			mysqli_stmt_bind_param($stmt, "issssi", $t, $phone, $name, $url, $path, $user_id);
			mysqli_stmt_execute($stmt);
		}
	}

	terminate($error, $status);
}

function update_module_status(){
	$error = array();
	if(!current_user_can('administrator')){
		$error[] = 'User is not Admin';
		terminate($error, false);
	}
	$module_id = $_POST['module_id'];
	$state = $_POST['status'];

	$conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	$sql = "UPDATE Modules SET status=? WHERE module_id= ?";
	$stmt = mysqli_stmt_init($conn);
	if(!mysqli_stmt_prepare($stmt, $sql)){
		terminate(array('DB Error'), false);
	}
	else{
		mysqli_stmt_bind_param($stmt, "si", $state, $module_id);
		mysqli_stmt_execute($stmt);
	}

	if($stmt->error == ''){
		terminate($stmt->error, true);
	}
	else{
		terminate($stmt->error, false);
	}
}

function approve_app(){
	$error = array();
	if(!current_user_can('administrator')){
		$error[] = 'User is not Admin';
		terminate($error, false);
	}
	$app_id = $_POST['app_id'];
	$conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	$sql = "UPDATE Users SET status=? WHERE user_ID=?; ";
	$stmt = mysqli_stmt_init($conn);
	if(!mysqli_stmt_prepare($stmt, $sql)){
		terminate(array('DB Error'), false);
	}
	else{
		$state = 'contributor';
		mysqli_stmt_bind_param($stmt, "si", $state, $app_id);
		mysqli_stmt_execute($stmt);
	}

	if($stmt->error == ''){
		$u = new WP_User($app_id);
		$u->add_role('contributor');
		terminate($stmt->error, true);
	}
	else{
		terminate($stmt->error, false);
	}

}