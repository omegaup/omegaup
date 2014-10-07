<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
	<head data-locale="{#locale#}">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>{$htmlTitle} &ndash; omegaUp</title>

		<script type="text/javascript" src="/js/jquery.js"></script>
		<!--<script type="text/javascript" src="/js/jquery-ui.min.js"></script>-->
		<!--<script type="text/javascript" src="/js/jquery-ui-timepicker-addon.js"></script>-->
		<script type="text/javascript" src="/js/omegaup.js?ts=22"></script>
		<script type="text/javascript" src="/js/lang.{#locale#}.js?ts=2"></script>
		<script type="text/javascript" src="/js/sugar.js"></script>
		<script type="text/javascript" src="/js/sugar.es.js"></script>
		<script type="text/javascript" src="/js/highstock.js"></script>
		<script type="text/javascript" src="/js/omegaup-graph.js"></script>
		<script type="text/javascript" src="/js/langtools.js"></script>
		
		<!-- Bootstrap from CDN -->
		<!-- Latest compiled and minified CSS -->
		
		<!--<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">-->
		<link rel="stylesheet" href="/css/bootstrap.min.css">
		<!-- Optional theme -->
                
		<!--<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css">-->
		<link rel="stylesheet" href="/css/bootstrap-theme.min.css">
		<!-- Latest compiled and minified JavaScript -->
		
		<!--<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>-->
		<script src="/js/bootstrap.min.js"></script>
		<!-- Bootstrap select plugin from https://github.com/silviomoreto/bootstrap-select -->
		<link rel="stylesheet" href="/css/bootstrap-select.min.css">
		<script type="text/javascript" src="/js/bootstrap-select.min.js"></script>
		<!-- Bootstrap datepicker plugin from http://www.eyecon.ro/bootstrap-datepicker/ -->
		<link rel="stylesheet" href="/css/bootstrap-datepicker.css">
		<script type="text/javascript" src="/js/bootstrap-datepicker.js"></script>
		<!-- typeahead plugin from https://github.com/twitter/typeahead.js -->
		<script type="text/javascript" src="/js/typeahead.jquery.js"></script>
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
	<script type="text/javascript" src="/js/mathjax-config.js"></script>
	<script type="text/javascript" src="/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
{/if}
{if isset($LOAD_PAGEDOWN) && $LOAD_PAGEDOWN}
	<script type="text/javascript" src="/js/pagedown/Markdown.Converter.js?ts=1"></script>
	<script type="text/javascript" src="/js/pagedown/Markdown.Sanitizer.js?ts=1"></script>
	<script type="text/javascript" src="/js/pagedown/Markdown.Editor.js?ts=1"></script>
	<link rel="stylesheet" type="text/css" href="/js/pagedown/demo/browser/demo.css" />
{/if}
		<script type="text/javascript" src="/js/head.sugar_locale.js"></script>
	</head>
	<body>
		
		<div id="wrapper">

{include file='common.navbar.tpl'}
