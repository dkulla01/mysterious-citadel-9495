(function(){ 
	var $container,
		$radar,
		$title,
		$tooltip,
		tooltip_top,
		timer,
		new_id;
	
	/* document.ready */
	$(function () {
		
		$container = $('section'),
		$radar = $('.radar'),
		$title = $('.title'),
		$tooltip = $('.tooltip'),
		new_id = 0;
		sample_text = [
			'<p>Suspendisse pulvinar, lacus sed aliquet vehicula, erat enim condimentum mi, sed interdum tortor odio eu mi. Aliquam nibh neque, fermentum ac interdum nec, cursus non enim. In vitae orci purus, ullamcorper rutrum erat. </p><p>Pellentesque interdum turpis ut purus porta laoreet. Cras sit amet erat erat, vel convallis ligula. Donec lectus metus, semper id venenatis a, dignissim vel urna. Curabitur tempus semper porttitor. Ut congue, velit id rutrum ullamcorper, libero urna interdum diam, sodales tempus est diam at orci. In eget tempor eros.</p>',
			'<p>Etiam tempus quam nec ipsum porta et mattis est aliquam. Cras porta diam vel urna vestibulum at venenatis augue hendrerit. Nunc eu justo fermentum odio condimentum posuere. Curabitur nisl erat, convallis id vulputate eget, condimentum ut risus. Donec ullamcorper, quam vel imperdiet auctor, eros neque suscipit justo, at lobortis dui dui ut mauris. </p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed id libero vitae dui ornare tincidunt. Fusce vitae orci turpis, vel aliquam velit.</p>',
			'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla ut magna neque, eu rhoncus turpis. Nulla pellentesque sagittis pulvinar. Nulla facilisi. Nullam aliquet, nisl suscipit luctus interdum, metus lorem lacinia justo, ac congue nisl dui a lectus. Donec vel massa orci, sed suscipit justo. Nunc euismod posuere commodo. Nulla ut sapien orci, et gravida arcu. Phasellus sit amet sodales odio. Sed ut ante neque.</p>',
			'<p>Ut volutpat, enim ac consequat euismod, urna velit mattis augue, sed viverra quam odio sed sem. Pellentesque ullamcorper congue nunc eu feugiat. Vestibulum id cursus massa. Phasellus nunc nisl, cursus id congue quis, hendrerit ut erat. Praesent eget hendrerit arcu. Etiam commodo vulputate pellentesque. Donec aliquam lobortis erat. Mauris odio nunc, condimentum a lobortis sit amet, pretium in massa. Praesent quis odio ultrices tellus hendrerit facilisis eget bibendum diam.</p>',
			'<p>Nunc cursus dignissim urna a aliquam. Suspendisse eleifend nisi in erat tristique in sollicitudin tellus suscipit. Vivamus consectetur orci libero. Donec ut lorem eu libero facilisis euismod. Donec tristique, turpis nec congue vulputate, urna libero hendrerit risus, sit amet mollis sem diam id est. Aliquam faucibus, turpis ut rutrum placerat, lorem nibh consequat urna, et imperdiet nibh nunc sed tellus. </p> <p>Sed et tellus a arcu condimentum pulvinar eu vitae orci. Nunc ornare sagittis libero, eu ultricies nulla venenatis et. Ut consectetur porttitor accumsan. In arcu mauris, interdum et sodales vitae, feugiat et diam. Vestibulum eget accumsan mi. Duis volutpat, odio quis accumsan tempor, orci mauris adipiscing turpis, eget ultricies quam lectus in ligula.</p>',
		];
		
		$container.imagesLoaded( function() {
			$container.isotope({
				itemSelector : 'article',
				masonry: {
					columnWidth: 50
				}
			});			
		});

		/*$('.circle').hover(function() {
			
			var selector = $(this).attr('id');
			if(selector == 'big') $tooltip.text('Acquaintances');
			else if(selector == 'med') $tooltip.text('Regular Friends');
			else $tooltip.text('Close Friends');			
			tooltip_top = $(this).position().top + $(this).height() - 5;
			if(timer) {
				clearTimeout(timer);
				timer = null;
			}
			timer = setTimeout(function () {
				$tooltip
					.css({
						'top' : tooltip_top + 'px'
					})
					.stop(true)
					.fadeIn(500);
			}, 1000);			
		}, function() {

			clearTimeout(timer);
			timer = null;		
			$tooltip.hide();
			
		});*/
		
		$('.circle').click(function(){
			var selector = $(this).attr('id');
			$('.circle').removeClass('select');
			$('aside h2').removeClass('select');
			$(this).addClass('select');
			$container.isotope({ filter: '.'+selector });
			if(selector == 'big') $title.text('Acquaintances');
			else if(selector == 'med') $title.text('Regular Friends');
			else $title.text('Close Friends');
			scrollToTop();
			if(noArticles()) $('.error').show(); 
			else $('.error').hide();
			return false;
		});
		
		$('.dot').click(function(){
			$('.circle').removeClass('select');
			$('aside h2').removeClass('select');
			$(this).addClass('select');
			$container.isotope({ filter: '.you' });
			$title.text('Your Links');
			scrollToTop();
			if(noArticles()) { $('.error').show(); }
			else $('.error').hide();			
			return false;
		});

		$('aside h2').click(function(){
			var selector = $(this).attr('id');
			$('.circle').removeClass('select');
			$('aside h2').removeClass('select');
			$(this).addClass('select');
			if (selector == 'all') {
				selector = '*';
				$('.circle').removeClass('select');	
				$title.text('All Links');
			}			
			else { 
				if(selector == 'home') $title.text('Home Friends');
				else if(selector == 'school') $title.text('School Friends');
				else if(selector == 'top') $title.text('Top Stories');
				else if(selector == 'video') $title.text('Videos');
				else if(selector == 'image') $title.text('Images');
				else if(selector == 'article') $title.text('Articles');			
				selector = '.'+selector;
			}
			scrollToTop();		
			$container.isotope({ filter: selector });
			if(noArticles()) $('.error').show(); 
			else $('.error').hide();	
			return false;
		});
		
		$('header h2').click(function () {
			
			$new_article = $('<article></article>');
			$new_article.addClass('col' + Math.floor(Math.random()*3));
			$new_article.addClass('you home article top');
			contents = '<h2>New Article ' + new_id++ + '</h2>';
			contents += sample_text[new_id%5];
			contents += '<em>shared by: you</em>';
			
			$new_article.append(contents);

			scrollToTop();			
			$container.prepend( $new_article)
					  .isotope( 'reloadItems' )
					  .isotope({ sortBy: 'original-order' })
					  .isotope( 'reLayout' );		
					  
			$new_article
				.css({
					'border' : '3px solid #4660a2'
				});
				/*.delay(500)
				.animate({
					borderTopColor: '#fff', 
					borderLeftColor: '#fff', 
					borderRightColor: '#fff', 
					borderBottomColor: '#fff'
				}, 1000);*/
		});
		
		$('article').click(function() { 
			url = $(this).data('url');
			window.open(url, '_newtab');		
		});
		
	}); /* end of document.ready */
	
	/* functions */
	function scrollToTop() {
		
		$('html, body')
			.stop(true)
	        .animate({
	        	scrollTop : 0,
	        }, {
	            duration: 750,                                             
	            easing: 'swing'
	        });
	}
	
	function noArticles() {
		
		return $('article').length == $('.isotope-hidden').length;
	}
	
})();