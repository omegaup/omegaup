<?php


	session_start();

	include_once("../config.php");
	include_once("../includes/db_con.php");		

	if( !(isset($_SESSION['userID']) && isset($_SESSION['userMode'])) || ( $_SESSION['userMode'] == "USER" )){
		die (" <h1>You dont belong here :P</h1> Teddy guardara tu IP y monitoreara tus actividades de ahora en adelante. ");
	}
	
	date_default_timezone_set('America/Mexico_City');

	$q = "UPDATE  `teddy`.`Mensaje` SET  `unread` =  '0' WHERE  para = '".$_SESSION['userID']."' ;";

	$resultado = mysql_query($q) or die('Donte be evil with teddy :P ');
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="../css/teddy_style.css" />
    		<title>Admin - Inbox</title>
			<script src="../js/jquery.min.js"></script>
			<script src="../js/jquery-ui.custom.min.js"></script>
		<style>

			.form-big {
				width:400px;
				margin:auto;
				margin-top:30px;
				padding:30px;
				border:1px solid #bbb;
				-moz-border-radius:11px;
			}
			form label{
				display:block;
				color:#777777;
				font-size:13px;
			}
			form p{
				color:#777777;
				font-size:14px;
				text-align:justify;
				margin-bottom:20px;
			}
			form input.text{
				background:#FBFBFB none repeat scroll 0 0;
				border:1px solid #E5E5E5;
				font-size:24px;
				margin-bottom:16px;
				margin-right:6px;
				margin-top:2px;
				padding:3px;
				width:97%;
			}
			form select{
				background:#FBFBFB none repeat scroll 0 0;
				border:1px solid #E5E5E5;
				font-size: 12px;
				margin-bottom:16px;
				margin-right:6px;
				margin-top:2px;
				padding:3px;
				width:80%;
			}
			form input.button {
				-moz-border-radius-bottomleft:6px;
				-moz-border-radius-bottomright:6px;
				-moz-border-radius-topleft:6px;
				-moz-border-radius-topright:6px;
				border:1px solid #AAAAAA;
				font-size:16px;
				padding:3px;
			}
			.right{
				text-align:right;
			}
		</style>
	</head>
<body>

<div class="wrapper">
	<div class="header">
		<h1>teddy online judge</h1>
		<h2>teddy es un oso de peluche</h2>
	</div>

	<?php include_once("../includes/admin.menu.php"); ?>
	<?php //include_once("../includes/session_mananger.php"); ?>	
	
	
	<?php

		if(isset($_REQUEST['enviado']) && $_REQUEST['enviado'] = "si"){
			echo '<div class="post_blanco"  align=center>';

			$msg = addslashes($_REQUEST['msg']);
			$para = addslashes($_REQUEST['para']);
			$q = "INSERT INTO Mensaje (de , para , mensaje, fecha ) VALUES (   '{$_SESSION['userID']}',  '{$para}',  '{$msg}', '" .date("Y-m-d H:i:s", time()).  "');";
			$resultado = mysql_query($q) or die('Donte be evil with teddy :P ' );
			echo "Mensaje enviado !";
			echo '</div>';
		}

	?>
	
	<div class="post_blanco"  align=center>
		
		<?php
			
			if(!isset($_SESSION['userID'])){
				echo "Inicia session";
			}else{
				?>
				
				
				<table border=0>
					<form class="form-big" method=POST >
						<tr><td>Enviar Mensaje</td></tr>
						<tr><td>Para<input name="para" type=text id="msg_to"></td></tr>					
						<tr><td><textarea name="msg" cols=44 rows=5></textarea></td></tr>
						<tr><td><input type=submit value="Enviar Mensaje"></td></tr>					
						<input type=hidden name="enviado" value="si" >
					</form>
				</table>
				<?php
				$q = "SELECT * FROM Mensaje WHERE de = '{$_SESSION['userID']}' OR para = '{$_SESSION['userID']}' ORDER BY fecha DESC";

				$resultado = mysql_query($q) or die('Donte be evil with teddy :P ');
			
				echo "<table border=0>";
				$total = 0;

				while($row = mysql_fetch_array($resultado)){

					?>
						
						<tr style="background-color: #white;">
							<td>De <b><?php echo $row['de']; ?></b>&nbsp;&nbsp;</td> 
							<td>Para <b><?php echo $row['para']; ?></b>
								&nbsp;&nbsp;<span style="cursor: pointer" onclick="document.getElementById('msg_to').value = '<?php echo $row['para']; ?>';">[reply]</span>&nbsp;&nbsp;
								<?php
									if($row['unread'] != 0){
										?>&nbsp;&nbsp;<span>[UNREAD]</span>&nbsp;&nbsp; <?php
									}

								?>
							</td> 
							<td>Fecha <b><?php echo $row['fecha']; ?></b>&nbsp;&nbsp;</td>
						</tr>
						<tr><td colspan=3><hr></td><tr>
						<tr style="background-color: #white;">
							<td colspan=3><?php 
							//echo $row['mensaje']."<br>"; 
							// Order of replacement
							$str     = $row['mensaje'];
							$order   = array("\r\n", "\n", "\r");
							$replace = '<br />';

							// Processes \r\n's first so they aren't converted twice.
							$newstr = str_replace($order, $replace, $str);

							echo $newstr;
						?></td>
						</tr>
						<tr>
							<td colspan=3>&nbsp;</td>
						</tr>
					<?php
				}
				echo "</table>";			
				
			}
			
		?>

	
	</div>




	<?php include_once("../includes/footer.php"); ?>

</div>

<!-- GOOGLE Analitics -->
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-11327997-2");
pageTracker._trackPageview();
} catch(err) {}</script>
<!-- GOOGLE Analitics -->


</body>
</html>

<?php
	if( isset($resultado))
		mysql_free_result($resultado);
		
	if( isset($enlace))
		mysql_close($enlace);
?>

