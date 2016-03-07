$.fn.addNotify = function( obj ) {
	return this.each ( function() {
		if( $('#'+obj.id).length > 0 ) {
			return;
		}

		var 	$this 	= $(this),
			$notify	= $('<a href="#"></a>'),
			$li 	= $('<li id="'+ obj.id +'" class="alert-dismissible" role="alert"></li>');

		$notify.click( function () { $('#notification-menu').dropdown('toggle');  });
		$li.append('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>');

		if( obj.hasOwnProperty('title') ) {
			$notify.append('<h4>'+obj.title+'</h4>');
		}
		if( obj.hasOwnProperty('text') ) {
			var tmp = $('<p></p>');
			tmp.append(obj.text);
			$notify.append(tmp);
		} 
		
		$li.append($notify);
		$this.prepend($li);
		
		if( obj.hasOwnProperty('counter') ) {

			if( localStorage ) {
				var notifications = localStorage['notifications'];
	
				localStorage['notifications'] =  ( notifications == null ) ? 0 : parseInt( notifications ) + 1; 
				
				if( obj.counter.hasClass('hide') ) {
					obj.counter.removeClass('hide');
				}
	
				obj.counter.text( localStorage['notifications'] );
			}
		} 
	});
};

$.fn.modifyNotification = function ( obj ) {
	var	$this	= $(this),
		$notify = $('<a href="#"></a>');

	$notify.click( function () { $('#notification-menu').dropdown('toggle');  });
	$this.html('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>');	

	if( obj.hasOwnProperty('title') ) {
		$notify.append('<h4>' + obj.title + '</h4>');
	} 

	if( obj.hasOwnProperty('text') ) {
		var tmp = $('<p></p>');
		tmp.html(obj.text);
		$notify.append(tmp);
	}
	$this.append($notify);
};

$.fn.clearNotifications = function( element ) {
	var	$element = $(element);

	return $(this).each( function () {
		var	$this = $(this);
		$this.click( function(event) {
			event.preventDefault();
			$('#notification-menu').dropdown('toggle');
			var	arr = $element.children(),
				tmp = {};

			
			tmp.idx		= 0;
			tmp.limit	= arr.length;
		
			var thread	= setInterval( function () {
				if( tmp.idx < tmp.limit ) {
					$( arr[ tmp.idx ] ).fadeOut(200, function() {
						$(this).remove();
					});
					
					++tmp.idx;
				} else {
					clearInterval(thread);
				}
			}, 200);
		});
	});
}; 

$(document).on('ready', function(){ 
	$('#clear-notifications').clearNotifications('#notification-drawer');
	if( localStorage ) {
		localStorage['notifications'] = 0;
		$('#notification-counter').addClass('hide');
	}
	$('#notification-button').click( function () {
		localStorage['notifications'] = 0;
		$('#notification-counter').addClass('hide');
	});
});
