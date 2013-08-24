<!DOCTYPE html>
<!-- @see this later for localization http://www.smarty.net/docs/en/language.function.config.load.tpl -->
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>OmegaUp | {#pageTitle#}</title>

		<script type="text/javascript" src="/js/jquery.js"></script>
		<!--<script type="text/javascript" src="/js/jquery-ui.min.js"></script>-->
		<script type="text/javascript" src="/js/jquery.msgBox.js"></script>
		<!--<script type="text/javascript" src="/js/jquery-ui-timepicker-addon.js"></script>-->
		<script type="text/javascript" src="/js/omegaup.js?ts=2"></script>
		<script type="text/javascript" src="/js/sugar.js"></script>
		<script type="text/javascript" src="/js/highstock.js"></script>
		<script type="text/javascript" src="/js/omegaup-graph.js"></script>
		
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

		<!-- from arena -->
		<link rel="shortcut icon" href="/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="/css/style.css">
		<link rel="stylesheet" type="text/css" href="/css/msgBoxLight.css">
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
	</head>
	<body>
		<div id="wrapper">

{include file='common.login_bar.tpl'}