<?php
/**
* Template Name:  Gallery 
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
<section class="page-banner">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-6 order-lg-2 order-md-2 p-0 col-md-6 col-12">
				<div class="content">
					<h1 data-aos="fade-left" data-aos-delay="200"><?php echo $title;?></h1>
				</div>                
			</div>
			<div class="col-lg-6 order-lg-1 order-md-1 p-0 col-md-6 col-12">
				<div class="img d-none d-lg-block d-md-block" style="background: url(<?php echo $image;?>) 50% 50% no-repeat; background-size: cover;"></div>
				<img class="w-100 d-lg-none d-md-none" src="<?php echo $image;?>" alt="" />
			</div>
		</div>
	</div>
</section>
<?php endwhile; ?>
<?php endif; ?>
<?php if( have_rows('page_content') ): ?>
<?php while( have_rows('page_content') ): the_row();
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
<?php if( have_rows('images') ): ?>
<section class="gallery-info">
	<div class="container">
		<div class="row row-cols-lg-4 row-cols-md-2 row-cols-2">
			<?php while( have_rows('images') ): the_row();
			$image = get_sub_field('image');
			?>
			<div class="cols">
				<a class="gallery-link" href="<?php echo $image;?>" data-fancybox="gallery"><img src="<?php echo $image;?>" alt="" /></a>
			</div>
			<?php endwhile; ?>
		</div>
	</div>
</section>
<?php endif; ?>
<?php endwhile; ?>
<?php endif; ?>
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
<script src='https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js'></script>
<script>
	$('[data-fancybox="gallery"]').fancybox({
		buttons: [
			"slideShow",
			"thumbs",
			"zoom",
			"fullScreen",
			"share",
			"close"],

		loop: false,
		protect: true });
</script>
<?php
get_footer();