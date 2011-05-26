<?php


    session_start();

	include_once("../config.php");
	include_once("../includes/db_con.php");	

?>

<html>
	<head>
		<link rel="stylesheet" type="text/css" href="../css/teddy_style.css" />
    		<title>Admin - Problemas</title>
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

        <div class="post_blanco"  align=center>
        <h1>testing teddy</h1>






    <?php

        $consulta = "select * from Problema ";
        $resultado = mysql_query($consulta) or die('Algo anda mal: ' . mysql_error());
    ?>

    <table border='0'> 
	<thead> <tr >
        <th >ID</th> 
		<th >Titulo</th> 
		<th >Publico</th>
		<th >data.in</th> 
		<th >data.out</th>  
		<th >Vistas</th> 
		<th >Aceptados</th> 
		<th >Intentos</th> 
		<th >Radio</th>
		<th ></th>
		</tr> 
	</thead> 
	<tbody>
	<?php

	$flag = true;

    while($row = mysql_fetch_array($resultado)){

		if( $row['intentos'] != 0)
			$ratio = ($row['aceptados'] / $row['intentos'])*100;
		else
			$ratio = "0.0";

		if($flag){
	        	echo "<TR style=\"background:#e7e7e7;\">";
			$flag = false;
		}else{
	        	echo "<TR style=\"background:white;\">";
			$flag = true;
		}

        if(file_exists( "../../casos/" . $row['probID'] . ".in" )){
            $datain =  filesize ( "../../casos/" . $row['probID'] . ".in"  ) . " bytes";
        }else{
            $datain =  "<div style='color:red'>x</div>" ;
        }

        if(file_exists( "../../casos/" . $row['probID'] . ".out" )){
            $dataout =  filesize ( "../../casos/" . $row['probID'] . ".out"  ). " bytes" ;
        }else{
            $dataout =  "<div style='color:red'>x</div>" ;
        }


        ?>
            <TD align='center'><?php echo $row['probID'] ?> </TD>
            <TD align='center'><?php echo $row['titulo'] ?> </TD>
            <TD align='center'><?php echo $row['publico'] ?> </TD>
            <TD align='center'><?php echo $datain ?> </TD>
            <TD align='center'><?php echo $dataout ?> </TD>
            <TD align='center'><?php echo $row['vistas'] ?> </TD>
            <TD align='center'><?php echo $row['aceptados'] ?> </TD>
            <TD align='center'><?php echo $row['intentos'] ?> </TD>
            <TD align='center'><?php printf( "%2.2f%%", $ratio ) ?> </TD>
            <TD align='center'>editar</TD>
        </tr>
        <?php
       
	}

	?>		
	</tbody>
	</table>



    </div>


    <?php include_once("../includes/footer.php"); ?>
</div>
<?php include("../includes/ga.php"); ?>
</body>
</html>
