
$(function() {
	$('.wmd-input').keypress(function() {
		window.clearTimeout(hljs.Timeout);
		hljs.Timeout = window.setTimeout(function() {
			hljs.initHighlighting.called = false;
			hljs.initHighlighting();
		}, 500);
	});
	window.setTimeout(function() {
		hljs.initHighlighting.called = false;
		hljs.initHighlighting();
	}, 500);
});
