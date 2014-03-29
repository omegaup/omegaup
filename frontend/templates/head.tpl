<!DOCTYPE html>
<!-- @see this later for localization http://www.smarty.net/docs/en/language.function.config.load.tpl -->
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>{$htmlTitle} - OmegaUp</title>

		<script type="text/javascript" src="/js/jquery.js"></script>
		<!--<script type="text/javascript" src="/js/jquery-ui.min.js"></script>-->
		<!--<script type="text/javascript" src="/js/jquery-ui-timepicker-addon.js"></script>-->
		<script type="text/javascript" src="/js/omegaup.js?ts=17"></script>
		<script type="text/javascript" src="/js/sugar.js"></script>
		<script type="text/javascript" src="/js/sugar.es.js"></script>
		<script type="text/javascript" src="/js/highstock.js"></script>
		<script type="text/javascript" src="/js/omegaup-graph.js"></script>
		<script type="text/javascript" src="/js/langtools.js"></script>
		
		<!-- Bootstrap from CDN -->
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
		<!-- Optional theme -->
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css">
		<!-- Latest compiled and minified JavaScript -->
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
		<!-- Bootstrap select plugin from https://github.com/silviomoreto/bootstrap-select -->
		<link rel="stylesheet" href="/css/bootstrap-select.min.css">
		<script type="text/javascript" src="/js/bootstrap-select.min.js"></script>
		<!-- Bootstrap datepicker plugin from http://www.eyecon.ro/bootstrap-datepicker/ -->
		<link rel="stylesheet" href="/css/bootstrap-datepicker.css">
		<script type="text/javascript" src="/js/bootstrap-datepicker.js"></script>
		<!-- Bootstrap typeahead plugin from https://github.com/tcrosen/twitter-bootstrap-typeahead -->
		<script type="text/javascript" src="/js/bootstrap-typeahead.js"></script>
		<!-- Bootstrap timepicker plugin from https://github.com/jdewit/bootstrap-timepicker
		<link rel="stylesheet" href="/css/bootstrap-timepicker.min.css">
		<script type="text/javascript" src="/js/bootstrap-timepicker.min.js"></script> -->
		<!-- Bootstrap datetimepicker plugin from http://www.malot.fr/bootstrap-datetimepicker/demo.php -->
		<link rel="stylesheet" href="/css/bootstrap-datetimepicker.css">
		<script type="text/javascript" src="/js/bootstrap-datetimepicker.min.js"></script>

		<!-- from arena -->
		<link rel="shortcut icon" href="/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="/css/style.css">
		<link rel="stylesheet" type="text/css" href="/css/common.css">
		<!--
		<link rel="stylesheet" type="text/css" href="/css/jquery-ui-1.8.16.custom.css">
		<link rel="stylesheet" type="text/css" href="/css/jquery-ui-timepicker-addon.css">
		-->
		
{if isset($LOAD_MATHJAX) && $LOAD_MATHJAX}
{literal}
	<script type="text/javascript" src="/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
	<script type="text/x-mathjax-config">MathJax.Hub.Config({tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}});</script>
{/literal}
{/if}
{if isset($LOAD_PAGEDOWN) && $LOAD_PAGEDOWN}
	<script type="text/javascript" src="/js/pagedown/Markdown.Converter.js"></script>
	<script type="text/javascript" src="/js/pagedown/Markdown.Sanitizer.js"></script>
	<script type="text/javascript" src="/js/pagedown/Markdown.Editor.js"></script>
	<link rel="stylesheet" type="text/css" href="/js/pagedown/demo/browser/demo.css" />
{/if}
		<script type="text/javascript"> /* Set sugarjs date locale globally */ Date.setLocale("{#locale#}");</script>
	</head>
	<body>
		
		<!-- Facebook like stuff -->
		<div id="fb-root"></div>
		<script>
			(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=197705690257857";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
		</script>
		<div id="wrapper">

{include file='common.navbar.tpl'}
