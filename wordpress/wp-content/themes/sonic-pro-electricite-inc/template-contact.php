<?php
/**
* Template Name:  Contact 
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
<section class="main-contactinfo">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-6 order-lg-2 order-md-2 p-0 col-md-6 col-12">
				<?php if( have_rows('phone', 'option') ): ?>
				<?php while( have_rows('phone', 'option') ): the_row(); ?>
				<div class="contact-icon">
					<div class="img">
						<img src="<?php the_sub_field('icon'); ?>" alt="" />
					</div>
					<div>
						<p><strong><?php the_sub_field('title'); ?></strong><br/><a href="tel:<?php the_sub_field('value'); ?>"><?php the_sub_field('value'); ?></a></p>
					</div>
				</div>
				<?php endwhile;?>
				<?php endif;?>
				<?php if( have_rows('email', 'option') ): ?>
				<?php while( have_rows('email', 'option') ): the_row(); ?>
				<div class="contact-icon">
					<div class="img">
						<img class="email" src="<?php the_sub_field('icon'); ?>" alt="" />
					</div>
					<div>
						<p><strong><?php the_sub_field('title'); ?></strong><br/><a href="mailto:<?php the_sub_field('value'); ?>"><?php the_sub_field('value'); ?></a></p>
					</div>
				</div>
				<?php endwhile;?>
				<?php endif;?>
				<?php if( have_rows('address', 'option') ): ?>
				<?php while( have_rows('address', 'option') ): the_row(); ?>
				<div class="contact-icon">
					<div class="img">
						<img src="<?php the_sub_field('icon'); ?>" alt="" />
					</div>
					<div>
						<p><strong><?php the_sub_field('title'); ?></strong><br/><?php the_sub_field('value'); ?></p>
					</div>
				</div>
				<?php endwhile;?>
				<?php endif;?>
				<?php if( have_rows('opening_hours', 'option') ): ?>
				<?php while( have_rows('opening_hours', 'option') ): the_row(); ?>
				<div class="contact-icon">
					<div class="img">
						<img src="<?php the_sub_field('icon'); ?>" alt="" />
					</div>
					<div>
						<p><strong><?php the_sub_field('title'); ?></strong><br/><?php the_sub_field('value'); ?></p>
					</div>
				</div>
				<div class="contact-icon">
					<div class="img">
						<!-- <img src="assets/img/icon-clock.svg" alt="" /> -->
					</div>
					<div>
						<p><?php the_sub_field('content'); ?></p>
					</div>
				</div>
				<?php endwhile;?>
				<?php endif;?>
			</div>
			<?php if( have_rows('map') ): ?>
			<?php while( have_rows('map') ): the_row();
			$title = get_sub_field('title');
			$code = get_sub_field('code');
			?>
			<div class="col-lg-6 mapdiv order-lg-1 order-md-1 text-center p-0 col-md-6 col-12">
				<h3><?php echo $title;?></h3>
				<?php echo $code;?>
			</div>
			<?php endwhile; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
<section class="contact-form-section">
	<div class="container">
		<div class="contact-form-wrapper">
			<div class="contact-form-header">
				<h2>Envoyez-nous un message</h2>
				<p>Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais.</p>
			</div>
			<div class="contact-form-body">
				<?php echo do_shortcode('[contact-form-7 id="4a5c422" title="Contact form"]');?>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();