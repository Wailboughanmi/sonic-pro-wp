<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Sonic-Pro_Electricite_inc.
 */

get_header();
?>
<section class="page-banner career">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-6 order-lg-2 order-md-2 p-0 col-md-6 col-12">
				<div class="content">
					<h1 data-aos="fade-left" data-aos-delay="200"><?php the_title();?></h1>
				</div>                
			</div>
			<div class="col-lg-6 order-lg-1 order-md-1 p-0 col-md-6 col-12">
				<?php $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );?>
				<?php if ( has_post_thumbnail() ) {?> 
				<img class="w-100" src="<?php echo $thumb['0'];?>" alt="" />
				<?php } else {?>
				<img class="w-100" src="https://wecreatedesign.co/dev/WP/sonic/wp-content/uploads/2025/02/shutterstock_1906589005-2880w.webp" alt="" />
				<?php } ?>
			</div>
		</div>
	</div>
</section>
<section class="small-content-info">
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
</section>

<?php
get_footer();
