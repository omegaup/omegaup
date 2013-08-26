<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>omegaUp</title>
		<script type="text/javascript" src="/js/jquery.js"></script>
		<script type="text/javascript" src="/js/jquery.ba-hashchange.js"></script>
		<script type="text/javascript" src="/js/jquery.gritter.min.js"></script>
		<script type="text/javascript" src="/js/jquery.tableSort.js"></script>
		<script type="text/javascript" src="/js/jquery.msgBox.js"></script>
		<script type="text/javascript" src="/js/highstock.js"></script>
		<script type="text/javascript" src="/js/sugar.js"></script>
		<script type="text/javascript" src="/js/sugar.es.js"></script>
		<script type="text/javascript" src="/js/omegaup.js"></script>
		<script type="text/javascript" src="{$jsfile}"></script>
{literal}
		<script type="text/javascript" src="https://c328740.ssl.cf1.rackcdn.com/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
		<script type="text/x-mathjax-config">MathJax.Hub.Config({tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}});</script>
{/literal}
		<link rel="stylesheet" href="/css/reset.css" />

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

		<link rel="stylesheet" href="/css/jquery.gritter.css" />
		<link rel="stylesheet" href="/css/common.css" />
		<link rel="stylesheet" href="/ux/arena.css" />
		<link rel="shortcut icon" href="/favicon.ico" />
	</head>
	<body{if $bodyid} id="{$bodyid}"{/if}>
		<!-- Generated from http://ajaxload.info/ -->
		<div id="loading" style="text-align: center; position: fixed; width: 100%; margin-top: -8px; top: 50%;"><img src="/ux/loading.gif" alt="loading" /></div>
		<div id="root">
{include file='common.navbar.tpl' currentSection='arena'}
