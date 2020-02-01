{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='problems' headerPayload=$headerPayload htmlTitle="{#omegaupTitleProblemNew#}" inline}
{include file='problem.edit.form.tpl' new='true' tags=$SELECTED_TAGS|json_encode inline}
<span id="form-data" data-name="problems"></span>
<script src="{version_hash src="/js/alias.generate.js"}"></script>
{include file='footer.tpl' inline}
