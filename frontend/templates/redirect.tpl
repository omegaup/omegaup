{if $LOGGED_IN eq '0'} 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>omegaUp</title>
	<meta http-equiv="REFRESH" content="0;url=/login/?redirect={$smarty.server.REQUEST_URI|escape:"url"}"></HEAD>
		<BODY>
		</BODY>
	</HTML>
{/if}
