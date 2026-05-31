<?php
/**
* Template Name:  Privacy 
*
* This is the template that displays all pages by default.
* Please note that this is the WordPress construct of pages
* and that other 'pages' on your WordPress site will use a
* different template.
*
* @package Sonic-Pro_Electricite_inc.
*/
get_header(); ?>
<?php if( have_rows('banner') ): ?>
<?php while( have_rows('banner') ): the_row(); 
$background_image = get_sub_field('background_image');
$title = get_sub_field('title');
$sub_title = get_sub_field('sub_title');
?>
<section class="small-info down privacy-page" style="background: url(<?php echo $background_image;?>) 50% 50% no-repeat; background-size: cover;background-attachment: fixed;">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-8 text-center col-md-12 col-12">
				<h3><?php echo $title;?></h3>
				<h4><?php echo $sub_title;?></h4>
			</div>
		</div>
	</div>
</section>
<?php endwhile;?>
<?php endif;?>
<!--<section class="small-content-info">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-12">
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
				the_content();
				endwhile; else: ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>-->
<?php
get_footer();