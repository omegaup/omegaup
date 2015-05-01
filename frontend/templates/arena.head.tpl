<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>{if isset($title)}{$title|escape} &ndash; {/if}omegaUp</title>
		<script type="text/javascript" src="/js/jquery-1.10.2.js"></script>
		<script type="text/javascript" src="/js/jquery.ba-hashchange.js"></script>
		<script type="text/javascript" src="/js/jquery.gritter.min.js"></script>
		<script type="text/javascript" src="/js/jquery.tableSort.js"></script>
		<script type="text/javascript" src="/js/highstock.js"></script>
		<script type="text/javascript" src="/js/sugar.js"></script>
		<script type="text/javascript" src="/js/sugar.es.js"></script>
		<script type="text/javascript" src="/js/omegaup.js?ts=22"></script>
		<script type="text/javascript" src="/js/lang.{#locale#}.js?ts=3"></script>
		<script type="text/javascript" src="/ux/libarena.js?ts=12"></script>
		<script type="text/javascript" src="{$jsfile}?ts=7"></script>
		<script type="text/javascript" src="/js/mathjax-config.js"></script>
		<script type="text/javascript" src="/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
		<link rel="stylesheet" href="/css/reset.css" />

		<!-- Bootstrap from CDN -->
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="/css/bootstrap.min.css">
		<!-- Optional theme -->
		<link rel="stylesheet" href="/css/bootstrap-theme.min.css">
		<!-- Latest compiled and minified JavaScript -->
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

		<link rel="stylesheet" href="/css/jquery.gritter.css" />
		<link rel="stylesheet" href="/css/common.css" />
		<link rel="stylesheet" href="/ux/arena.css?t=5" />
		<link rel="shortcut icon" href="/favicon.ico" />
	</head>
	<body{if isset($bodyid) and $bodyid} id="{$bodyid|escape}"{/if}>
		<!-- Generated from http://ajaxload.info/ -->
		{if !isset($bodyid) or $bodyid != 'only-problem'}
		<div id="loading" style="text-align: center; position: fixed; width: 100%; margin-top: -8px; top: 50%;"><img src="/ux/loading.gif" alt="loading" /></div>
		{/if}
		<div id="root">
{include file='common.navbar.tpl' currentSection='arena'}
{include file='status.tpl'}
