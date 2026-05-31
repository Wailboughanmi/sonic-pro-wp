(function($) {
	'use strict';

	$('.scroll-btn').on('click', function(e){
		var anchor = $(this);
		$('html, body').stop().animate({
			scrollTop: $(anchor.attr('href')).offset().top - 65
		}, 1000);
		e.preventDefault();
	});

	$(document).scroll(function(e){
		var scrollTop = $(document).scrollTop();
		if(scrollTop > 15){
			$('.site-header').addClass('fixed');
		} else {
			$('.site-header').removeClass('fixed');
		}
	});

	$('.mobile-toggle, a.menu-btn').click(function(){
		$(".side-menu").addClass('intro');
		$(".side-menu-overlay").addClass('active');
	});
	$("a.CloseBtn, .side-menu-overlay").click(function(){
		$(".side-menu").removeClass('intro');
		$(".side-menu-overlay").removeClass('active');
	});

	var amountScrolled = 200;
	$(window).scroll(function() {
		if ( $(window).scrollTop() > amountScrolled ) {
			$('.back-to-top').addClass('show');
		} else {
			$('.back-to-top').removeClass('show');
		}
	});

	$('.back-to-top').click(function() {
		$('html, body').animate({ scrollTop: 0 }, 800);
		return false;
	});

	$(".menu-item-has-children").click(function(){
		$(this).toggleClass('current');
	});

	$('.banner-carousel').owlCarousel({
		margin:0, loop:true, dots:false, nav:false, autoplay: true, items:1,
		animateIn: 'fadeIn', animateOut: 'fadeOut'
	});

	AOS.init();

})(jQuery);
