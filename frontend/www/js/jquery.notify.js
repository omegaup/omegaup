function Notification ( obj ) {

	var self = this;

	self.counter;
	self.drawer;
	self.menu;
	self.button;
	self.tools;
	self.tag_counter;

	self.__construct = function ( obj ) {
	  self.counter = 0;

	  if ( !obj.hasOwnProperty('element') ) {
	  	return;
	  }
	
	  if ( $('.notification-drawer', obj.element).length > 0 ) {
	  	self.drawer = $('.notification-drawer', obj.element);
	  }

	  if ( $('.notification-menu', obj.element).length > 0 ) {
	  	self.menu = $('.notification-menu', obj.element);
	  }

	  if ( $('.notification-button', obj.element).length > 0 ) {
	  	self.button = $('.notification-button', obj.element);

	  	self.button.click( function () {
	  		self.counter = 0;

	  		if ( self.tag_counter ) {
	  			self.tag_counter.addClass('hide');
	  		}
	  	});
	  }

	  if ( $('.notification-tools', obj.element).length > 0 ) {
	  	self.tools = $('.notification-tools', obj.element);
	  }

	  if ( $('.notification-counter', obj.element).length > 0 ) {
	  	self.tag_counter = $('.notification-counter', obj.element);
	  	self.tag_counter.addClass('hide');
	  }

	  if ( $('.clear-notifications', obj.element).length > 0 && self.menu && self.drawer ) {
	  	$('.clear-notifications', obj.element).click( function (event) {
	  		event.preventDefault();
			
			self.menu.dropdown('toggle');

			var	arr 	= self.drawer.children(),
				idx		= 0,
				limit	= arr.length;
		
			var thread	= setInterval( function () {
				if( idx < limit ) {
					$( arr[ idx ] ).fadeOut(200, function() {
						$(this).remove();
					});
					
					++idx;
				} else {
					clearInterval(thread);
				}
			}, 200);
	  	});
	  }
	
	}

	self.__construct( obj );

}

Notification.prototype.add = function ( obj ) {

	var self = this;

	if ( !self.drawer ||
		!obj.hasOwnProperty('id') ) {
		return;
	}

	if ( $('#'+obj.id).length > 0 ) {
	  return;
	}

  	var $notify 	= $('<a href="#"></a>'),
	  	$li     	= $('<li id="' + obj.id + '" class="alert-dismissible" role="alert"></li>'),
	  	$btn_close	= $('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>');

  	$notify.click( function () { self.menu.dropdown('toggle'); });  
  	$btn_close.click( function () { self.menu.dropdown('toggle'); $(this).parent().remove() }); 

  	$li.append($btn_close);

  	if ( obj.hasOwnProperty('title') ) {
		$notify.append('<h4>'+obj.title+'</h4>');
  	}

  	if ( obj.hasOwnProperty('text') ) {
		var tmp = $('<p></p>');
		tmp.append(obj.text);
		$notify.append(tmp);
  	} 
  
  	$li.append($notify);
  	self.drawer.prepend($li);

  	++self.counter;

  	if ( self.tag_counter ) {

		if ( self.tag_counter.hasClass('hide') ) {
	  		self.tag_counter.removeClass('hide');
		}
		self.tag_counter.text( self.counter );
  	}

};

Notification.prototype.modify = function ( obj ) {

	var self = this;

	if ( !obj.hasOwnProperty('id') ) {
		return;
	}

	var $li = $('#' + obj.id);

	if ( $li.length == 1 ) {

		++self.counter;

		if ( self.tag_counter ) {
			
			if ( self.tag_counter.hasClass('hide') ) {
				self.tag_counter.removeClass('hide');
			}
			self.tag_counter.text( self.counter );
		}

		var $notify 	= $('<a href="#"></a>'),
			$btn_close 	= $('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>');

		$notify.click( function () { self.menu.dropdown('toggle'); });  
		$btn_close.click( function () { self.menu.dropdown('toggle'); $(this).parent().remove() }); 

		$li.html($btn_close); 

		if ( obj.hasOwnProperty('title') ) {
			$notify.append('<h4>' + obj.title + '</h4>');
		} 

		if ( obj.hasOwnProperty('text') ) {
			var tmp = $('<p></p>');
			tmp.html(obj.text);
			$notify.append(tmp);
		}
		  
		$li.append($notify);
	} else {

		self.add({
			id: obj.id,
			title: obj.hasOwnProperty('title') ? obj.title : "",
			text: obj.hasOwnProperty('text') ? obj.text : ""
		});

	}
};

// Declarar las notificaciones...
var $notification = null;
$(function () {
	$notification = new Notification({
		element: 	$('#my-notifications')
	});
});