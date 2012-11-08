<!--header-->


<!DOCTYPE html>

<!-- @see this later for localization http://www.smarty.net/docs/en/language.function.config.load.tpl -->

<html xmlns="http://www.w3.org/1999/xhtml " xmlns:fb="http://www.facebook.com/2008/fbml ">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <title>OmegaUp | {#pageTitle#}</title>

        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
        <script type="text/javascript" src="/js/omegaup.js"></script>
        <script type="text/javascript" src="/js/jquery.msgBox.js"></script>
        
        

        <link rel="stylesheet" type="text/css" href="/css/style.css">
        <link rel="stylesheet" type="text/css" href="/css/msgBoxLight.css">
    </head>
<body>

<div id="wrapper">

    <div class="login_bar" style="display: block">
        {if $LOGGED_IN eq '1'}
            {$CURRENT_USER_GRAVATAR_URL_16}
            <a href="/profile.php">{$CURRENT_USER_USERNAME}</a> <b><a href='/logout.php'>{#logOut#}</a></b>
        {else}
             {#pageTitle#} <b><a href='/login.php'>{#logIn#}</a> !</b>
        {/if}
    </div>



    <div id="title">
        <a href="index.php">
        <div style="margin-left: 15%;">
            <img src="/media/omegaup_curves.png">
        </div>
        </a>
    </div>
    <div id="content">
        <div class="post footer">
            <ul>
                {if $CURRENT_USER_IS_ADMIN eq '1'}
                    <li><a href='/admin/'><b>Admin</b></a></li>
                {/if}
                <li><a href='/contests.php'>{#frontPageContests#}</a></li>
                <li><a href='/probs.php'>{#frontPageProblems#}</a></li>
                <li><a href='/rank.php'>{#frontPageRanking#}</a></li>
                <li><a href='/recent.php'>{#frontPageRecent#}</a></li>
                <li><a href='/api/explorer/'>{#frontPageDevelopers#}</a></li>
                <li><a href='help.php'>{#frontPageHelp#}</a></li>

                <li><a href='http://blog.omegaup.com/'>{#frontPageBlog#}</a></li>
                <li><a href='/preguntas/'>{#frontPageQuestions#}</a></li>
            </ul>
        </div>

        {if $ERROR_TO_USER eq 'USER_OR_PASSWORD_WRONG'} 
        <div class="post footer">
            mensaje 
        </div>
        {/if} 
        