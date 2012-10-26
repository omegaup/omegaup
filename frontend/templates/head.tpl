<!--header-->


<!DOCTYPE html>

<!-- @see this later for localization http://www.smarty.net/docs/en/language.function.config.load.tpl -->

<html xmlns="http://www.w3.org/1999/xhtml " xmlns:fb="http://www.facebook.com/2008/fbml ">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
<title>OmegaUp | Elevando el nivel de programacion</title>
<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
<div id="wrapper">

    {if $LOGGED_IN eq '1'} 
    <div class="login_bar" style="display: block">
        WAZAAAAP <b><a href='logout.php'>Cerrar sesion</a> !</b>
    </div>
    {else}
    <div class="login_bar" style="display: block">
         we a OmegaUp ! <b><a href='login.php'>Inicia sesion</a> !</b>
    </div>
    {/if}
        



    <div id="title">
        <a href="index.php">
        <div style="margin-left: 40%;">
            <img src="media/omegaup_curves.png">
        </div>
        </a>
    </div>
    <div id="content">
        <div class="post footer">
            <ul>
                <li><a href='contests.php'><b>Concursos</b></a></li>
                <!-- <li><a href='probs.php'>Problemas</a></li> -->
                <li><a href='rank.php'>Ranking</a></li>
                <li><a href='recent.php'>Actividad reciente</a></li>
                <li><a href='faq.php'>FAQ</a></li>
                <!-- <li><a href='schools.php'>Escuelas</a></li> -->
                <li><a href='help.php'>Colabora</a></li>
                <!-- <li><input type='text' placeholder='Buscar'></li> -->
            </ul>
        </div>

        {if $ERROR_TO_USER eq 'USER_OR_PASSWORD_WRONG'} 
        <div class="post footer">
            mensaje 
        </div>
        {/if} 
        