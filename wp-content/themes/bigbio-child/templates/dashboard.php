<?php
/* Template Name: dashboard */
?>

<?php get_header(); check_logged(); ?>

<div class="sm_container"> 
<h1>Dashboard</h1>
<?php
	if(current_user_can('administrator')){
		echo '<a href="' . $GLOBALS['url'] . '/admin">Admin Dashboard</a><br>';
		echo '<a href="' . $GLOBALS['url'] . '/module-submit">Create a new Module</a><br>';
	}
	else if(current_user_can('contributor')){
		echo '<a href="' . $GLOBALS['url'] . '/module-submit">Create a new Module</a><br>';
	}
	else if(!current_user_can('administrator') && !current_user_can('contributor')){
		$user_id = get_current_user_id();
		$sql = "SELECT applied FROM Users WHERE user_ID= ?";
		$response = getSqlData($sql, "i", array(&$user_id));
		$has_applied = $response['data'][0]['applied'];

		if($has_applied){
			echo 'Thank you for applying. Your application is being reviewed.';
		}
		else{
			echo '<a href="../apply">Apply to be a Contributor!</a>';
		}
		
	}
?>

<div class="table_wrapper">
	<table id="module_table">
		<tr>
			<th>Title</th>
			<th>Status</th>
			<th>Date Modified</th>
			<th>Edit</th>
			<th>Delete</th>
		</tr>
	</table>
	<button id="load_more">Load More</button>
</div>

<?php if(!current_user_can('administrator') && !current_user_can('contributor')){ ?>
		<script type="text/javascript"> document.getElementsByClassName('table_wrapper')[0].style.display = 'none';</script>
		<?php
	} ?>

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
		<button onclick="ajax_delete_js()">Delete</button>
	</div>
</div>
</div> 

<?php get_footer(); ?>