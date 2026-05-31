<?php
/**
 * The header for our theme
 *
 * @package Sonic-Pro_Electricite_inc.
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>

	<!-- Google Tag Manager -->
	<script>
	(function(w,d,s,l,i){
		w[l]=w[l]||[];
		w[l].push({
			'gtm.start': new Date().getTime(),
			event:'gtm.js'
		});
		var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),
			dl=l!='dataLayer' ? '&l='+l : '';

		j.async=true;
		j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
		f.parentNode.insertBefore(j,f);

	})(window,document,'script','dataLayer','GTM-MMLZ6PPQ');
	</script>
	<!-- End Google Tag Manager -->

	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="profile" href="https://gmpg.org/xfn/11">

	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/animate.min.css">
	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/owl.carousel.min.css">
	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/owl.theme.default.min.css">
	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/font-awesome.css">

	<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">

	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/custom-v2.css?v=<?php echo time(); ?>">

	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/responsive-v2.css?v=<?php echo time(); ?>">
<meta name="google-site-verification" content="69K_bxQOpr8VwQZniyfBvTZ9y547jswn7x-kDVpCMJE" />
	<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<!-- Google Tag Manager (noscript) -->
<noscript>
	<iframe 
		src="https://www.googletagmanager.com/ns.html?id=GTM-MMLZ6PPQ"
		height="0"
		width="0"
		style="display:none;visibility:hidden">
	</iframe>
</noscript>
<!-- End Google Tag Manager (noscript) -->

<a class="dmBackToTop back-to-top" aria-label="Scroll to top" href=".header"></a>

<header class="site-header">
	<div class="header-main">
		<div class="container">
			<div class="header-inner">
				<a class="header-logo" href="<?php echo home_url(); ?>">
					<img src="<?php the_field('logo', 'option'); ?>" alt="<?php bloginfo('name'); ?>" />
				</a>

				<nav class="header-nav">
					<?php
					wp_nav_menu(array(
						'container'      => false,
						'theme_location' => 'menu-1',
						'menu_id'        => 'primary-menu',
						'menu_class'     => 'nav-menu',
						'depth'          => 2,
					));
					?>
				</nav>

				<div class="header-actions">
					<?php
					if (have_rows('phone', 'option')) :
						while (have_rows('phone', 'option')) : the_row();
							$phone_value = get_sub_field('value');
					?>
						<a class="header-phone-btn" href="tel:<?php echo esc_attr($phone_value); ?>">
							<i class="fa fa-phone"></i>
							<span><?php echo esc_html($phone_value); ?></span>
						</a>
					<?php
						endwhile;
					endif;
					?>
					<button class="mobile-toggle" aria-label="Menu">
						<span></span>
						<span></span>
						<span></span>
					</button>
				</div>
			</div>
		</div>
	</div>
</header>

<div class="side-menu-overlay"></div>

<div class="side-menu">

	<a class="CloseBtn"></a>

	<?php
	wp_nav_menu(array(
		'container'      => false,
		'theme_location' => 'menu-1',
		'menu_id'        => 'primary-menu',
		'menu_class'     => 'main-menu navbar-nav',
	));
	?>

	<div class="cinfo">

		<?php if(have_rows('address', 'option')): ?>
			<?php while(have_rows('address', 'option')): the_row(); ?>
				<h3><?php the_sub_field('title'); ?></h3>
				<p><?php the_sub_field('value'); ?></p>
			<?php endwhile; ?>
		<?php endif; ?>

		<?php if(have_rows('opening_hours', 'option')): ?>
			<?php while(have_rows('opening_hours', 'option')): the_row(); ?>
				<h3><?php the_sub_field('title'); ?></h3>
				<p><?php the_sub_field('value'); ?></p>
				<p><?php the_sub_field('content'); ?></p>
			<?php endwhile; ?>
		<?php endif; ?>

		<?php if(have_rows('phone', 'option')): ?>
			<?php while(have_rows('phone', 'option')): the_row(); ?>
				<h3><?php the_sub_field('title'); ?></h3>
				<p class="lg">
					<a href="tel:<?php the_sub_field('value'); ?>">
						<?php the_sub_field('value'); ?>
					</a>
				</p>
			<?php endwhile; ?>
		<?php endif; ?>

		<?php if(have_rows('email', 'option')): ?>
			<?php while(have_rows('email', 'option')): the_row(); ?>
				<h3><?php the_sub_field('title'); ?></h3>
				<p>
					<a href="mailto:<?php the_sub_field('value'); ?>">
						<?php the_sub_field('value'); ?>
					</a>
				</p>
			<?php endwhile; ?>
		<?php endif; ?>

		<?php if(have_rows('social_media', 'option')): ?>
			<?php while(have_rows('social_media', 'option')): the_row(); ?>
				<a class="icon" href="<?php the_sub_field('url'); ?>" target="_blank">
					<i class="<?php the_sub_field('icon'); ?>"></i>
				</a>
			<?php endwhile; ?>
		<?php endif; ?>

	</div>

</div>