<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Sonic-Pro_Electricite_inc.
 */

?>

<section class="footer">
	<div class="container">
		<div class="row">
			<div class="col-lg-3 text-center col-md-3 col-12">
				<a class="logo" href="<?php echo home_url();?>"><img src="<?php the_field('logo','option');?>"></a><br/>
				<?php
				wp_nav_menu( array(
					'container'=> false,
					'menu_class'=> false, 
					'theme_location' => 'footer-menu',
					'menu_id'        => 'footer-menu',
					'menu_class'        => 'footer-menu',
					'link_class'   => 'link '
				) );
				?>
			</div>
			<div class="col-lg-4 col-md-3 col-12">
				<h3><?php the_field('details_title','option');?></h3>
				<p class="lg">
					<?php if( have_rows('phone', 'option') ): ?>
					<?php while( have_rows('phone', 'option') ): the_row(); ?>
					<a href="tel:<?php the_sub_field('value'); ?>"><?php the_sub_field('value'); ?></a>
					<?php endwhile; endif;?>
					<?php if( have_rows('email', 'option') ): ?>
					<?php while( have_rows('email', 'option') ): the_row(); ?>
					<a href="mailto:<?php the_sub_field('value'); ?>"><?php the_sub_field('value'); ?></a>
					<?php endwhile; endif;?>
				</p>
			</div>
			<?php if( have_rows('opening_hours', 'option') ): ?>
			<?php while( have_rows('opening_hours', 'option') ): the_row(); ?>
			<div class="col-lg-3 col-md-3 col-12">
				<h3><?php the_sub_field('title'); ?></h3>
				<p><?php the_sub_field('value'); ?></p>
				<p><?php the_sub_field('content'); ?></p>
			</div>
			<?php endwhile; endif;?>
			<div class="col-lg-2 col-md-3 col-12">
				<?php if( have_rows('address', 'option') ): ?>
				<?php while( have_rows('address', 'option') ): the_row(); ?>
				<h3><?php the_sub_field('title'); ?></h3>
				<p><?php the_sub_field('value'); ?></p>
				<?php endwhile; endif;?>
				<?php if( have_rows('social_media', 'option') ): ?>
				<?php while( have_rows('social_media', 'option') ): the_row(); ?>
				<a class="icon" href="<?php the_sub_field('url'); ?>" target="_blank"><i class="<?php the_sub_field('icon'); ?>"></i></a>
				<?php endwhile; endif;?>
			</div>
		</div>
	</div>
</section>
<section class="copyright">
	<div class="container">
		<div class="row">
			<div class="col-12 text-center">
				<p>&copy; <?php echo date('Y'); ?> Sonic-Pro Électricité inc. Tous droits réservés.</p>
				<p class="footer-credits">Site conçu par <a href="https://tmt.ai-throttle-mtl.ca/" target="_blank" rel="noopener"><img src="https://tmt.ai-throttle-mtl.ca/logo.png" alt="Throttle Mind Technologies Montreal" class="footer-logo" /></a></p>
				<p class="footer-credits">Hébergé par <a href="https://www.hostinger.com/" target="_blank" rel="noopener"><img src="https://www.hostinger.com/favicon.ico" alt="Hostinger" class="footer-logo footer-logo-sm" /></a></p>
			</div>
		</div>
	</div>    
</section> 
<script src="<?php echo get_template_directory_uri();?>/assets/js/jquery.min.js"></script>
<script src="<?php echo get_template_directory_uri();?>/assets/js/bootstrap.min.js"></script>
<script src="<?php echo get_template_directory_uri();?>/assets/js/owl.carousel.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="<?php echo get_template_directory_uri();?>/assets/js/wow.min.js"></script>
<script src="<?php echo get_template_directory_uri();?>/assets/js/custom.js"></script>

<script src="https://marketingwebsites.ca/privacy/js/privacy_policy_fr.js"></script>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery("body").on("click", "a[href^='tel']", function () {
            gtag('event', 'conversion', {'send_to': 'AW-16923388945/eKoqCP-s66kaEJHY2YU_'});
        });

        jQuery("form").submit(function (event) {
            gtag('event', 'conversion', {'send_to': 'AW-16923388945/GJzmCN-a56kaEJHY2YU_'});
        });
    });

</script>


<?php wp_footer(); ?>
</body>
</html>