{include file='head.tpl' htmlTitle="{#omegaupTitleSchoolsOfTheMonth#}" inline}
<script type="text/json" id="school-of-the-month-payload">{$schoolOfTheMonthPayload|json_encode}</script>
<div id="school-of-the-month"></div>
{js_include entrypoint="school_of_the_month"}
{include file='footer.tpl' inline}