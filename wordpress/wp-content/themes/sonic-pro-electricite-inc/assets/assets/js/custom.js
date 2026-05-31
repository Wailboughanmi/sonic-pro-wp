(function($) {
	'use strict';
	// Navbar Menu JS
	$('.scroll-btn').on('click', function(e){
		var anchor = $(this);
		$('html, body').stop().animate({
			scrollTop: $(anchor.attr('href')).offset().top - 65
		}, 1000);
		e.preventDefault();
	});
	
	//MENU FIXATION
	$(document).scroll(function(e){
		var scrollTop = $(document).scrollTop();
		if(scrollTop > 15){
			console.log(scrollTop);
			$('.header').removeClass('').addClass('fixed');
		} else {
			$('.header').removeClass('fixed').addClass('');
		}
	});
	
	//Sidemenu 
	$("a.menu-btn").click(function(){
		$(".side-menu").addClass('intro');
	});
	$("a.CloseBtn").click(function(){
		$(".side-menu").removeClass('intro');
	});

	$(".menu-item-has-children").click(function(){
		$(this).toggleClass('current');
	});
	
	// Banner carousel
	$('.banner-carousel').owlCarousel({
		margin:0,
		loop:true,
		dots:false,
		nav:false,
        autoplay: true,
		items:1,
		animateIn: 'fadeIn',
		animateOut: 'fadeOut'
	});
	

	// AOS Animation
	AOS.init();

})(jQuery);
