{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='schools' headerPayload=$headerPayload htmlTitle="{#wordsActivityReport#}" inline}

<div id="course-activity"></div>
{js_include entrypoint="activity_feed"}

{include file='footer.tpl' inline}
