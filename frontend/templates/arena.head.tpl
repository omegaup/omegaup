<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>OmegaUp</title>
		<script type="text/javascript" src="/js/jquery.js"></script>
		<script type="text/javascript" src="/js/jquery.ba-hashchange.js"></script>
		<script type="text/javascript" src="/js/jquery.gritter.min.js"></script>
		<script type="text/javascript" src="/js/jquery.tableSort.js"></script>
		<script type="text/javascript" src="/js/jquery.msgBox.js"></script>
		<script type="text/javascript" src="/js/highstock.js"></script>
		<script type="text/javascript" src="/js/sugar.js"></script>
		<script type="text/javascript" src="/js/omegaup.js"></script>
		<script type="text/javascript" src="{$jsfile}"></script>
{literal}
		<script type="text/javascript" src="/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
		<script type="text/x-mathjax-config">MathJax.Hub.Config({tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}});</script>
{/literal}
		<link rel="stylesheet" href="/css/reset.css" />
		<link rel="stylesheet" href="/css/jquery.gritter.css" />
		<link rel="stylesheet" href="/ux/arena.css" />
		<link rel="shortcut icon" href="/favicon.ico" />
	</head>
	<body{if $bodyid} id="{$bodyid}"{/if}>
		<!-- Generated from http://ajaxload.info/ -->
		<div id="loading" style="text-align: center; position: fixed; width: 100%; margin-top: -8px; top: 50%;"><img src="/ux/loading.gif" alt="loading" /></div>
		<div id="root">
{include file='common.login_bar.tpl'}
