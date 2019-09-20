<script type="text/json" id="headerPayload">{[
  'omegaUpLockDown' => $smarty.const.OMEGAUP_LOCKDOWN,
  'inContest' => isset($inContest) && $inContest,
  'isLoggedIn' => $LOGGED_IN eq '1',
  'isReviewer' => ($LOGGED_IN eq '1') ? ($CURRENT_USER_IS_REVIEWER eq '1') : false,
  'gravatarURL51' => ($LOGGED_IN eq '1') ? $CURRENT_USER_GRAVATAR_URL_51 : '',
  'currentUsername' => ($LOGGED_IN eq '1') ? $CURRENT_USER_USERNAME : '',
  'requestURI' => $smarty.server.REQUEST_URI|escape:'url',
  'isAdmin' => ($LOGGED_IN eq '1') ? $CURRENT_USER_IS_ADMIN eq '1' : false,
  'lockDownImage' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAA6UlEQVQ4jd2TMYoCMRiFv5HBwnJBsFqEiGxtISps6RGmFD2CZRr7aQSPIFjmCGsnrFYeQJjGytJKRERsfp2QmahY+iDk5c97L/wJCchBFCclYAD8SmkBTI1WB1cb5Ji/gT+g7mxtgK7RausNiOIEYAm0pHSWOZR5BbSNVndPwTmlaZnnQFnGXGot0XgDfiw+NlrtjVZ7YOzRZAJCix893NZkAi4eYejRpJcYxckQ6AENKf0DO+EVoCN8DcyMVhM3eQR8WesO+WgAVWDituC28wiFDHkXHxBgv0IfKL7oO+UF1Ei/7zMsbuQKTFoqpb8KS2AAAAAASUVORK5CYII='
]|json_encode}</script>
<div id="common-navbar"></div>

<script type="text/javascript" src="{version_hash src="/js/dist/common_navbar.js"}"></script>
{if $CURRENT_USER_IS_ADMIN eq '1'}
  <script type="text/javascript" src="{version_hash src="/js/common.navbar.grader_status.js"}"></script>
{/if}