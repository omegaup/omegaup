<?php 

	session_start(); 
	include_once("../config.php");
	include_once("../includes/db_con.php");
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="../css/teddy_style.css" />
    		<title>Admin - Usuarios</title>
			<script src="../js/jquery.min.js"></script>
			<script src="../js/jquery-ui.custom.min.js"></script>
	</head>
<body>

<div class="wrapper">
	<div class="header">
		<h1>teddy online judge</h1>
		<h2>teddy es un oso de peluche</h2>
	</div>

   	<?php include_once("../includes/admin.menu.php"); ?>

	<div class="post_blanco">
	<?php


	$consulta = "SELECT * FROM `Usuario` ";
	$resultado = mysql_query($consulta);
	?>

	<div align="center" >
	<table border='0' style="font-size: 14px;" > 
	<thead> <tr >
		<th width='12%'>userID</th> 
		<th width='12%'>Nombre</th> 
		<th width='12%'>Mail</th> 
		<th width='12%'>Twitter</th>
		<th width='12%'>Ubicacion</th> 
		<th width='12%'>Escuela</th> 
		<th width='12%'>Cuenta</th> 
		<th width='12%'>Solved</th>
		<th width='12%'>Tried</th> 
		<th width='12%'></th> 
		</tr> 
	</thead> 
	<tbody>
	<?php
    	$flag = true;

    	while($row = mysql_fetch_array($resultado)){
		
    		if($flag){
    			echo "<TR style='background:#e7e7e7;'>";
    			$flag = false;
    		}else{
    			echo "<TR style='background:white;'>";
    			$flag = true;
    		}
            ?>
                        
                		<TD align='center' ><?php echo $row['userID']?></TD>
                		<TD align='center' ><?php echo utf8_decode($row['nombre']) ?></TD>
                		<TD align='center' ><?php echo $row['mail']?></TD>
                		<TD align='center' ><?php echo $row['twitter']?></TD>
                		<TD align='center' ><?php echo $row['ubicacion']?></TD>
                		<TD align='center' ><?php echo utf8_decode($row['escuela']) ?></TD>
                		<TD align='center' ><?php echo $row['cuenta']?></TD>
                		<TD align='center' ><?php echo $row['solved']?></TD>
                		<TD align='center' ><?php echo $row['tried']?></TD>
                		<TD align='center' >x</TD>
                        </tr>
            <?php

	}
	?>		
	</tbody>
	</table>
	</div>
	</div>





	<?php include_once("../includes/footer.php"); ?>

</div>
<?php include("../includes/ga.php"); ?>
</body>
</html>

<?php
	if( isset($resultado))
		 mysql_free_result($resultado);

	if( isset($enlace))
		mysql_close($enlace);
?>
