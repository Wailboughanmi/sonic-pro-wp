<?php
/**
* Template Name:  Home 
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
$sub_title = get_sub_field('sub_title');
$content = get_sub_field('content');
$icon = get_sub_field('icon');
?>
<section class="banner">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-6 order-lg-2 order-md-2 p-0 col-md-6 col-12">
				<div class="content">
					<img class="logo" src="<?php echo $icon;?>" alt=""  data-aos="fade-left"/>
					<h1 data-aos="fade-left" ><?php echo $title;?></h1>
					<p class="lg" data-aos="fade-left"><?php echo $sub_title;?></p>
					<?php echo $content;?>
				</div>                
			</div>
			<div class="col-lg-6 order-lg-1 order-md-1 p-0 col-md-6 col-12">
				<div class="img" style="background: url(<?php echo $image;?>) 50% 50% no-repeat; background-size: cover;"></div>
			</div>
		</div>
	</div>
</section>
<?php endwhile;?>
<?php endif;?>
<section class="emergency-service">
	<div class="container">
		<div class="emergency-inner">
			<div class="emergency-icon">
				<i class="fa fa-bolt"></i>
			</div>
			<h2>Service d'urgence 24/7</h2>
			<p>Une panne électrique, un problème urgent ? Nos électriciens interviennent rapidement à <strong>Repentigny</strong> ,<strong>Rivière-des-Prairies</strong> ,<strong>Laval</strong>, <strong>Terrebonne</strong> et partout sur la Rive-Nord de Montréal, jour et nuit, 7 jours sur 7.</p>
			<div class="emergency-actions">
				<?php
				if (have_rows('phone', 'option')) :
					while (have_rows('phone', 'option')) : the_row();
						$phone_value = get_sub_field('value');
				?>
					<a class="emergency-phone" href="tel:<?php echo esc_attr($phone_value); ?>">
						<i class="fa fa-phone"></i>
						<span>Urgence : <?php echo esc_html($phone_value); ?></span>
					</a>
				<?php
					endwhile;
				endif;
				?>

			</div>
		</div>
	</div>
</section>
<?php if( have_rows('cta') ): ?>
<?php while( have_rows('cta') ): the_row(); 
$title = get_sub_field('title');
$button_text = get_sub_field('button_text');
$button_url = get_sub_field('button_url');
?>
<section class="small-info">
	<style>
		.small-info .wpcf7-form label {
			color: #000000;
		}
		.small-info .row.align-items-center {
			padding-bottom: 30px;
		}
		.small-info .row.align-items-center p {
			font-size: 32px;
			font-weight: 600;
		}
		.small-info .wpcf7-form .btn-default {
			display: block;
			margin: 0 auto;
		}
	</style>
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-12 text-center col-md-12 col-12">
				<p><?php echo $title;?></p>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-12">
				<?php echo do_shortcode('[contact-form-7 id="4a5c422" title="Contact form"]');?>
			</div>
		</div>
	</div>
</section>
<?php endwhile;?>
<?php endif;?>
<?php if( have_rows('about') ): ?>
<?php while( have_rows('about') ): the_row(); 
$image = get_sub_field('image');
$title = get_sub_field('title');
$content = get_sub_field('content');
?>
<section class="about">
	<div class="container-fluid bg">
		<div class="row ms-auto me-auto">
			<div class="col-lg-6 left col-md-6 col-12">
				<div class="section-title">
					<h2><?php echo $title;?></h2>
					<hr/>
					<?php echo $content;?>
				</div>
			</div>
			<div class="col-lg-6 right col-md-6 col-12" data-aos="fade-left" data-aos-delay="200">
				<img class="w-100" src="<?php echo $image;?>" alt="" />
			</div>
		</div>
	</div>
</section>
<?php endwhile;?>
<?php endif;?>
<?php if( have_rows('details') ): ?>
<?php while( have_rows('details') ): the_row(); 
$logo = get_sub_field('logo');
?>
<section class="contact-info">
	<div class="container">
		<div class="row g-0">
			<?php if( have_rows('left_details') ): ?>
			<?php while( have_rows('left_details') ): the_row(); 
			$number = get_sub_field('number');
			$title = get_sub_field('title');
			$sub_title = get_sub_field('sub_title');
			$tag = get_sub_field('tag');
			$serial_number = get_sub_field('serial_number');
			?>
			<div class="col-lg-4 p-0 col-md-4 col-12">
				<div class="contact-box">
					<div>
						<h3><?php echo $title;?></h3>
						<p><span><?php echo $sub_title;?></span><br/><a href="tel:<?php echo $number;?>"><?php echo $number;?></a></p>
						<p><span class="red"><?php echo $tag;?></span> <a href="#"><?php echo $serial_number;?></a></p>
					</div>
				</div>
			</div>
			<?php endwhile;?>
			<?php endif;?>
			<div class="col-lg-4 p-0 col-md-4 col-12">
				<div class="contact-box blue">
					<div>
						<img src="<?php echo $logo;?>" alt="" />
					</div>
				</div>
			</div>
			<?php if( have_rows('right_details') ): ?>
			<?php while( have_rows('right_details') ): the_row(); 
			$number = get_sub_field('number');
			$title = get_sub_field('title');
			$sub_title = get_sub_field('sub_title');
			$tag = get_sub_field('tag');
			$serial_number = get_sub_field('serial_number');
			?>
			<div class="col-lg-4 p-0 col-md-4 col-12">
				<div class="contact-box">
					<div>
						<h3><?php echo $title;?></h3>
						<p><span><?php echo $sub_title;?></span><br/><a href="tel:<?php echo $number;?>"><?php echo $number;?></a></p>
						<p><span class="red"><?php echo $tag;?></span> <a href="#"><?php echo $serial_number;?></a></p>
					</div>
				</div>
			</div>
			<?php endwhile;?>
			<?php endif;?>
		</div>
	</div>
</section>
<?php endwhile;?>
<?php endif;?>

<?php if (have_rows('testimonials')) : while (have_rows('testimonials')) : the_row();
	$title = get_sub_field('title');
	$testimonials = get_sub_field('testimonials');
?>
	<section class="google-testimonials-section">
		<div class="container">
			<div class="row">
				<div class="col-12 text-center mb-5">
					<div class="section-title">
						<h2><?= $title; ?></h2>
						<hr>
					</div>
					<div class="col-12">
						<?= $testimonials; ?>
					</div>
				</div>
			</div>
		</div>
	</section>
<?php endwhile; endif; ?>

<section class="main-info">
	<?php if( have_rows('residential') ): ?>
	<?php while( have_rows('residential') ): the_row(); 
	$title = get_sub_field('title');
	?>
	<div class="bg-title">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-12">
					<div class="section-title">
						<h2><?php echo $title;?></h2>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="container icon-boxes">
		<div class="row">
			<?php if( have_rows('points') ): ?>
			<?php while( have_rows('points') ): the_row(); 
			$title = get_sub_field('title');
			$url = get_sub_field('url');
			$icon = get_sub_field('icon');
			?>
			<div class="col-lg-4 col-md-6 col-12">
				<a href="<?php echo $url;?>" class="icon-box">
					<img src="<?php echo $icon;?>" alt="" /> <?php echo $title;?>
				</a>
			</div>
			<?php endwhile;?>
			<?php endif;?>
		</div>
	</div>
	<?php endwhile;?>
	<?php endif;?>
	<?php if( have_rows('Commercial') ): ?>
	<?php while( have_rows('Commercial') ): the_row(); 
	$title = get_sub_field('title');
	?>
	<div class="bg-title">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-12">
					<div class="section-title">
						<h2><?php echo $title;?></h2>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="container icon-boxes">
		<div class="row">
			<?php if( have_rows('points') ): ?>
			<?php while( have_rows('points') ): the_row(); 
			$title = get_sub_field('title');
			$url = get_sub_field('url');
			$icon = get_sub_field('icon');
			?>
			<div class="col-lg-4 col-md-6 col-12">
				<a href="<?php echo $url;?>" class="icon-box">
					<img src="<?php echo $icon;?>" alt="" /> <?php echo $title;?>
				</a>
			</div>
			<?php endwhile;?>
			<?php endif;?>
		</div>
	</div>
	<?php endwhile;?>
	<?php endif;?>
	<?php if( have_rows('industrial') ): ?>
	<?php while( have_rows('industrial') ): the_row(); 
	$title = get_sub_field('title');
	?>
	<div class="bg-title">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-12">
					<div class="section-title">
						<h2><?php echo $title;?></h2>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="container icon-boxes">
		<div class="row">
			<?php if( have_rows('points') ): ?>
			<?php while( have_rows('points') ): the_row(); 
			$title = get_sub_field('title');
			$url = get_sub_field('url');
			$icon = get_sub_field('icon');
			?>
			<div class="col-lg-4 col-md-6 col-12">
				<a href="<?php echo $url;?>" class="icon-box">
					<img src="<?php echo $icon;?>" alt="" /> <?php echo $title;?>
				</a>
			</div>
			<?php endwhile;?>
			<?php endif;?>
		</div>
	</div>
	<?php endwhile;?>
	<?php endif;?>
	<?php if( have_rows('electrical_engineering') ): ?>
	<?php while( have_rows('electrical_engineering') ): the_row(); 
	$title = get_sub_field('title');
	?>
	<div class="bg-title">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-12">
					<div class="section-title">
						<h2><?php echo $title;?></h2>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="container icon-boxes">
		<div class="row">
			<?php if( have_rows('points') ): ?>
			<?php while( have_rows('points') ): the_row(); 
			$title = get_sub_field('title');
			$url = get_sub_field('url');
			$icon = get_sub_field('icon');
			?>
			<div class="col-lg-4 col-md-6 col-12">
				<a href="<?php echo $url;?>" class="icon-box">
					<img src="<?php echo $icon;?>" alt="" /> <?php echo $title;?>
				</a>
			</div>
			<?php endwhile;?>
			<?php endif;?>
		</div>
	</div>
	<?php endwhile;?>
	<?php endif;?>
</section>
<?php if( have_rows('widget') ): ?>
<?php while( have_rows('widget') ): the_row(); 
$title = get_sub_field('title');
$button_text = get_sub_field('button_text');
$button_url = get_sub_field('button_url');
?>
<section class="info-bar">
	<div class="container">
		<div class="row text-center align-items-center">
			<div class="col-lg-6 col-md-6 col-12">
				<h3><?php echo $title;?></h3>
			</div>
			<div class="col-lg-6 col-md-6 col-12">
				<a class="btn-default" href="<?php echo $button_url;?>"><?php echo $button_text;?></a>
			</div>
		</div>
	</div>
</section>
<?php endwhile;?>
<?php endif;?>
<?php if( have_rows('areas') ): ?>
<?php while( have_rows('areas') ): the_row(); 
$background_image = get_sub_field('background_image');
$title = get_sub_field('title');
?>
<section class="zones-servies" style="background:url(<?php echo $background_image;?>) 50% 50% fixed no-repeat; background-size:cover;">
	<!--<div class="parallax-window" data-parallax="scroll" data-image-src="<?php echo $background_image;?>"></div>-->
	<div class="container">
		<div class="row">
			<div class="col-lg-12 text-center col-md-12 col-12">
				<h2><?php echo $title;?></h2>
				<p class="zone-subtitle">Basés à <strong>Repentigny</strong>, nous desservons également <strong>Rivière-des-Prairies</strong>, Laval, Montréal et toute la Rive-Nord.</p>
			</div>
		</div>
		<div class="row">
			<?php if( have_rows('area') ): ?>
			<?php while( have_rows('area') ): the_row(); 
			$title = get_sub_field('title');
			?>
			<div class="col-lg-4 text-center col-md-4 col-12">
				<h5><?php echo $title;?></h5>
				<hr/>
			</div>
			<?php endwhile;?>
			<?php endif;?>
		</div>
	</div>
</section>
<?php endwhile;?>
<?php endif;?>
<?php if( have_rows('advantages') ): ?>
<?php while( have_rows('advantages') ): the_row(); 
$image = get_sub_field('image');
$title = get_sub_field('title');
?>
<section class="faq">
	<div class="container">
		<div class="row">
			<div class="col-lg-6 col-md-6 col-12">
				<div class="imgstyle" data-aos="fade-right" data-aos-delay="200">
					<img src="<?php echo $image;?>" alt="" />
				</div>
			</div>
			<div class="col-lg-6 right col-md-6 col-12">
				<h2><?php echo $title;?></h2>
				<div class="accordion" id="accordionExample">
					<?php if( have_rows('accordian_points') ): ?>
					<?php $acounter = 1;?>
					<?php while( have_rows('accordian_points') ): the_row(); 
					$content = get_sub_field('content');
					$title = get_sub_field('title');
					?>
					<div class="accordion-item">
						<h2 class="accordion-header" id="heading<?php echo $acounter; ?>">
							<button class="accordion-button <?php echo ($acounter == 1) ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $acounter; ?>" aria-expanded="<?php echo ($acounter == 1) ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $acounter; ?>">
								<?php echo $title; ?>
							</button>
						</h2>
						<div id="collapse<?php echo $acounter; ?>" class="accordion-collapse collapse <?php echo ($acounter == 1) ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $acounter; ?>" data-bs-parent="#accordionExample">
							<div class="accordion-body">
								<?php echo $content; ?>
							</div>
						</div>
					</div>
					<?php $acounter++; ?>
					<?php endwhile; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endwhile;?>
<?php endif;?>
<?php if( have_rows('full_with_content') ): ?>
<?php while( have_rows('full_with_content') ): the_row(); 
$content = get_sub_field('content');
$title = get_sub_field('title');
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
<?php endwhile;?>
<?php endif;?>

<?php if( have_rows('logos') ): ?>
<?php while( have_rows('logos') ): the_row(); 
$background_image = get_sub_field('background_image');
?>
<section class="logos-info" style="background:url(<?php echo $background_image;?>) 50% 50% fixed no-repeat; background-size:cover;">
	<!--<div class="parallax-window" data-parallax="scroll" data-image-src="<?php echo $background_image;?>"></div>-->
	<div class="container">
		<div class="row align-items-center">
			<?php if( have_rows('images') ): ?>
			<?php while( have_rows('images') ): the_row(); 
			$logo = get_sub_field('logo');
			?>
			<div class="col-lg-4 text-center col-md-4 col-6 wow animated bounceInUp">
				<a href="#"><img src="<?php echo $logo;?>" alt="" /></a>
			</div>
			<?php endwhile;?>
			<?php endif;?>
		</div>
	</div>
</section>
<?php endwhile;?>
<?php endif;?>
<?php if( have_rows('contact_widget', 'option') ): ?>
<?php while( have_rows('contact_widget', 'option') ): the_row(); ?>
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