{if $LOGGED_IN eq '0'} 
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>Omegaup</title>
	<meta http-equiv="REFRESH" content="0;url=/login.php?redirect={$smarty.server.REQUEST_URI}"></HEAD>
		<BODY>
			Redirectioning you.
		</BODY>
	</HTML>
{/if}