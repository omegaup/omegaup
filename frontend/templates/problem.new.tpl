{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleProblemNew#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

{include file='problem.edit.form.tpl' new='true'}
<span id="form-data" data-name="problems"></span>
<script src="/js/alias.generate.js?ver=ca96ff"></script>
{include file='footer.tpl'}
