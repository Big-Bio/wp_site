<?php get_header(); ?>

<?php 
	
	$post_author_id = get_post_field('post_author');
	$user_info = get_userdata($post_author_id);


	$current_user = get_current_user_id();
	if($current_user == $post_author_id){ 

	$onclick = "location.href='" . $GLOBALS['url'] . "/edit-profile';";
	echo '<input type="button" onclick="' . $onclick . '" value="Edit Profile"/>';
	
	} 

?>


<?php if (have_posts()) : while (have_posts()) : the_post(); 

	the_content(); 

endwhile; 
endif; ?>


	

<?php

if (empty( get_the_content() ) ){
	echo get_post_field('post_title'); 
	echo get_avatar($post_author_id); 

	$args = array(
    'author' => $post_author_id,
    'orderby' =>  'post_date',
    'post_type' => 'module',
    'numberposts' => 5,
	);
	
	$posts = get_posts($args);

	foreach($posts as $post):?>

      <div class = "vlink">
  		<a href="<?php the_permalink(); ?>"><?php the_title();?></a>
      <br>
      </div>

 <?php endforeach; } ?>
	


<?php get_footer(); ?>