<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Sonic-Pro_Electricite_inc.
 */

get_header();
?>
<?php if( have_rows('404_banner','option') ): ?>
<?php while( have_rows('404_banner','option') ): the_row(); 
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
<?php endwhile;?>
<?php endif;?>
<section class="small-content-info mt-5">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 text-center col-md-12 col-12">
				<?php $pcontent = get_field('404_page_content','option');?>
				<?php echo $pcontent;?>
				<?php if( have_rows('404_button', 'option') ): ?>
				<?php while( have_rows('404_button', 'option') ): the_row(); ?>
				<a href="<?php the_sub_field('button_url'); ?>" class="btn-default btn-black mt-4"><?php the_sub_field('button_text'); ?></a>
				<?php endwhile;?>
				<?php endif;?>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
