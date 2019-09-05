<?php
/* Template Name: dashboard-admin */
?>

<?php get_header(); ?>


<?php

if(!current_user_can('administrator')){
	echo 'Invalid entry.';
	get_footer();
	exit();
}
?>

<div class="mm_container">
	<h1>Admin Dashboard</h1>
	<div class="table_wrapper">
		<label>Pending Modules</label>
		<table id="module_table">
			<tr>
				<th>Author</th>
				<th>Title</th>
				<th>Status</th>
				<th>Date Modified</th>
				<th>Preview</th>
				<th>Revise</th>
				<th>Approve</th>
			</tr>
		</table>
		<!-- <button id="load_more">Load More</button> -->
	</div>
	<div class="app_wrapper">
		<label>Contributor Applications</label>
		<table id="app_table">
			<tr>
				<th>Name</th>
				<th>Degree</th>
				<th>CV</th>
				<th>Approve</th>
			</tr>
		</table>
		<!-- <button id="load_more">Load More</button> -->
	</div>
</div>

<div class="bg_modal">
	<div class= "modal-content">
		<div class="close_but">+</div>
		Title: <div id="title"></div>
		Author: <div id="author"></div>
		Key Terms: <div id="keyterms"></div>
		DOIs: <div id="doi"></div>
		Collaborators: <div id="collab"></div>
		Module Summary: <div id="content"></div>
		Supplemental Notes and Resources: <div id="sup_notes"></div>
		Acknowledgements: <div id="ack"></div>
		<div id="worksheet">Worksheet: </div>
		<div id="presentation">Presentation: </div>
	</div>
</div>

<div class="bg-delete">
	<div class="delete-content">
		<div class="delete_close">+</div>
		<p>Are you sure you want to delete this module?</p>
		<button>Delete</button>
	</div>
</div>


<?php get_footer(); ?>

