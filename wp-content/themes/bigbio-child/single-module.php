<?php get_header(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>


 <?php 

 	$post_id = get_the_ID();
 	$author = get_the_author();
 	$yt_url = get_post_meta($post_id,'yt');
 	$quiz_url = get_post_meta($post_id,'quiz');
 	$pdf_url = get_post_meta($post_id, 'pdf_url');
 	$ppt_url = get_post_meta($post_id, 'ppt_url');
 	$collab = get_post_meta($post_id, 'collab')[0];
 	$ack = get_post_meta($post_id, 'ack')[0];
 	$doi = get_post_meta($post_id, 'doi')[0];
 	$sup_notes = get_post_meta($post_id, 'sup_notes')[0];
 	$yt = get_post_meta($post_id, 'yt')[0];
 	$quiz = get_post_meta($post_id,'quiz')[0];

 	$collab = str_replace(',',', ', $collab);
 	// $doi = str_replace(',',' | ', $doi);
 	$doi = explode(',', $doi);

 	echo '<h1>' . get_the_title() . '</h1>';
 	echo 'Author: ' . $author . '<br>';
 	echo 'Collaborators: ' . $collab . '<br>'; 
	echo 'Module Summary: ' . get_the_content() . '<br>';
	echo 'DOIs: ';
	foreach($doi as $item){
		echo '<a href="' . $item . '">' . $item . '</a> ';
	}
	echo '<br>';
	echo 'Acknowledgement: ' . $ack . '<br>';
	echo 'Supplemental Notes and Resources: ' . $sup_notes . '<br>';	

	echo 'Worksheet: <br><iframe src="https://docs.google.com/viewer?url=' . $pdf_url[0] . '&embedded=true" style="width:600px; height:500px;" frameborder="0"></iframe><br>';

	echo 'Presentation: <br><iframe src="https://docs.google.com/viewer?url=' . $ppt_url[0] . '&embedded=true" style="width:600px; height:500px;" frameborder="0"></iframe><br>';

	echo 'Youtube: <br><object width="900" height="550" data="http://www.youtube.com/v/' . $yt . '" type="application/x-shockwave-flash"><param name="src" value="http://www.youtube.com/v/' . $yt . '" /></object><br>';

	echo 'Quiz: <br><iframe src="' . $quiz . '&embedded=true" style="width:600px; height:500px;" frameborder="0"></iframe><br>';
	
?>
	
	

<?php endwhile; endif; ?>
	

<?php get_footer(); ?>