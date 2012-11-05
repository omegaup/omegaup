<!--header-->


<!DOCTYPE html>

<!-- @see this later for localization http://www.smarty.net/docs/en/language.function.config.load.tpl -->

<html xmlns="http://www.w3.org/1999/xhtml " xmlns:fb="http://www.facebook.com/2008/fbml ">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
<title>OmegaUp | Elevando el nivel de programacion</title>
<link rel="stylesheet" type="text/css" href="/css/style.css">
</head>
<body>
<div id="wrapper">

    
    <div class="login_bar" style="display: block">
        {if $LOGGED_IN eq '1'} 
            Hola user <b><a href='/logout.php'>Cerrar sesion</a> !</b>
        {else}
             OmegaUp ! <b><a href='/login.php'>Inicia sesion</a> !</b>
        {/if}
    </div>



    <div id="title">
        <a href="index.php">
        <div style="margin-left: 40%;">
            <img src="/media/omegaup_curves.png">
        </div>
        </a>
    </div>
    <div id="content">
        <div class="post footer">
            <ul>
                <li><a href='/contests.php'>Concursos</a></li>
                <li><a href='/probs.php'>Problemas</a></li>
                <li><a href='/rank.php'>Ranking</a></li>
                <li><a href='/recent.php'>Actividad reciente</a></li>
                <li><a href='/api/explorer/'>API</a></li>
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
        