<?php
/*Template Name: edit-module-admin*/
?>

<?php get_header(); check_logged(); ?>

<script src= "../beta/wp-content/themes/bigbio-child/js/ajax-edit-module-script.js"> </script>

<script src= "../beta/wp-content/themes/bigbio-child/js/citation.js"> </script>

<?php
	check_admin_referer( 'edit-nonce', 'security' );
	$post_id = $_POST['post_id'];
	$post = get_post($post_id);


	if(!current_user_can('administrator')){
		echo 'Invalid Entry';
		get_footer();
	}

	$title = $post->post_title;
	$content = $post->post_content;
	$tags = get_the_tags($post_id);
	$sup_notes = get_post_meta($post_id,'sup_notes')[0];
	$ack = get_post_meta($post_id, 'ack')[0];
	$yt = get_post_meta($post_id,'yt')[0];
	$quiz = get_post_meta($post_id,'quiz')[0];
?>



<?php echo "<a id='back-button' href=" . $GLOBALS['url'] . "/admin" . ">Back to Admin Dashboard</a>"; ?>

<div class="creator_content">
	<input id="post_id" type="hidden" value="<?php echo $post_id?>">
	<h1>Admin: Edit Module</h1>
  	<u1 id="status"></u1>
  	<div class="row">
  		 
  		<div class="column2">
  			<label for="title">Title</label>
  			<input id= "title" type="text" name="title" value="<?php echo $title ?>">

  			<label for="collab">Collaborators</label>
  			<p>Press enter after each collaborators.</p>
  			<div id="collab-cont" class="tag-cont"></div>
  			
  			<label for="content">Module Summary</label>
		    <textarea id= "content" name="content"><?php echo $content; ?></textarea>

		    <label for="sup-notes">Supplemental Notes and Resources</label>
		    <textarea id= "sup-notes" name= "sup-notes"><?php echo $sup_notes; ?></textarea>

		    <label for="acknowledge">Acknowledgements</label>
		    <textarea id="acknowledge" name="acknowledge"><?php echo $ack; ?></textarea>
		</div>

  		<div class="column1">
  			<div id="file_cont"></div>
  			<div id="ppt_cont">
	  			<label for="ppt">Upload Presentation (PDF or PPT)</label>
				<input id= "ppt" type="file" name="ppt">
  			</div>
  			

			<div id="pdf_cont">
  				<label for="pdf">Upload Worksheet (PDF)</label>
  				<input id= "pdf" type="file" name="pdf">
  			</div>

			<label for="tags">Key Terms</label>
			<p>Press enter after each term.</p>
			<div id="key-cont" class="tag-cont"></div>
			
			<label for="doi">DOIs</label>
			<p>Press enter after each DOI.</p>
			<div id="doi-cont" class="tag-cont"></div>

			<label for="yt">Video URL</label>
			<input id= "yt" type="text" name="yt" value="https://www.youtube.com/watch?v=<?php echo $yt; ?>">

			<label for="quiz">Quiz URL</label>
			<input id= "quiz" type="text" name="quiz" value="<?php echo $quiz; ?>">
		</div>
		
	</div>



	<?php wp_nonce_field( 'ajax-module-nonce', 'security' ); ?>
	<button id="submit" onclick="ajax_edit_module_js()" >Submit</button>
</div>

<div class="bg_modal">
	<div class= "modal-content">
		<div class="close">+</div>
		<div id="filepreview"></div>
	</div>
</div>

<script src= "../beta/wp-content/themes/bigbio-child/js/tags.js"></script>
<script>get_info();</script>

<script>
	document.querySelector('.close').addEventListener('click', function(){
	    document.querySelector('.bg_modal').style.display = 'none';
	});
</script>


<?php get_footer() ?>


