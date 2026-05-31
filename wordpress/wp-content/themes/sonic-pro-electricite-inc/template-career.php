<?php
/**
* Template Name:  Career 
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
$image = get_sub_field('image');
$title = get_sub_field('title');
?>
<section class="page-banner career">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-6 order-lg-2 order-md-2 p-0 col-md-6 col-12">
				<div class="content">
					<h1 data-aos="fade-left" data-aos-delay="200"><?php echo $title;?></h1>
				</div>                
			</div>
			<div class="col-lg-6 order-lg-1 order-md-1 p-0 col-md-6 col-12">
				<img class="w-100" src="<?php echo $image;?>" alt="" />
			</div>
		</div>
	</div>
</section>
<?php endwhile; ?>
<?php endif; ?>
<?php if( have_rows('full_with_content') ): ?>
<?php while( have_rows('full_with_content') ): the_row();
$title = get_sub_field('title');
$content = get_sub_field('content');
?>
<section class="small-content-info">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-12">
				<hr/>
				<h2><?php echo $title;?></h2>
				<?php echo $content;?>
			</div>
		</div>
	</div>
</section>
<?php endwhile; ?>
<?php endif; ?>
<section class="career-form">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-12">
				<?php echo do_shortcode('[contact-form-7 id="5908e39" title="Career Form"]');?>
			</div>
		</div>
	</div>
</section>
<?php if( have_rows('contact_widget') ): ?>
<?php while( have_rows('contact_widget') ): the_row(); 
$background_image = get_sub_field('background_image');
$title = get_sub_field('title');
$button_text = get_sub_field('button_text');
$button_url = get_sub_field('button_url');
?>
<section class="small-info down" style="background: url(<?php the_sub_field('background_image'); ?>) 50% 50% no-repeat; background-size: cover;background-attachment: fixed;">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-8 text-center col-md-12 col-12">
				<p><?php the_sub_field('title'); ?></p>
				<a class="btn-default" href="<?php the_sub_field('button_url'); ?>"><?php the_sub_field('button_text'); ?></a>
			</div>
		</div>
	</div>
</section>
<?php endwhile;?>
<?php endif;?>
<?php
get_footer();