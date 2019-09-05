<?php
/* Template Name: dashboard */
?>

<?php get_header(); check_logged();?>

<?php 

if(current_user_can('administrator')){
	echo '<a href="../module-submit">Create a module</a><br>';
	echo '<a href="../admin">Admindashboard</a>';
}
else if(current_user_can('contributor')){

}
else{
	$user_id = get_current_user_id();
	$sql= "SELECT applied FROM Users WHERE user_ID=?;";
	$response = getSqlData($sql, "i", array(&$user_id));

	$status = $response['data'][0]['applied'];

	if($status){
		echo 'Thank you for applying! Your application is under review.';
	}
	else{
		echo '<a href="../apply">Apply to become a contributor! </a>';
	}
}

?>

		 	
<div class="sm_container"> 
	<h4>Dashboard</h4>
	<table id="module_table">
		<tr>
			<th>Title</th>
			<th>Status</th>
			<th>Submission Date</th>
			<th>Preview</th>
			<th>Edit</th>
			<th>Delete</th>
		</tr>
	</table>

<div class="bg_modal">
	<div class= "modal-content">
		<div class="close">+</div>
		<h1 id="title"></h1>
		<p id="content"></p>
		<div id="pdf"></div>
		<div id="ppt"></div>
	</div>
</div>

<div class="bg-delete">
	<div class="delete-content">
		<div class="delete_close">+</div>
		<p>Are you sure you want to delete this module?</p>
		<input type="hidden" id="module_id">
		<input type="hidden" id="row_id">
		<button>Delete</button>
	</div>
</div>

</div> 


<?php get_footer(); ?>