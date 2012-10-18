<?php
/*
 * LEVEL_NEEDED defines the users who can see this page.
 * Anyone without permission to see this page, will	
 * be redirected to a page saying so.
 * This variable *must* be set in order to bootstrap
 * to continue. This is by design, in order to prevent
 * leaving open holes for new pages.
 * */
define("LEVEL_NEEDED", false);

require_once("../server/inc/bootstrap.php");

/**
 * Logic for registering a new user
 **/
if (isset($_POST["request"]) && ($_POST["request"] == "register")){
	// test params
	if(
		isset($_POST["email"])
		&& isset($_POST["pass"])
		&& isset($_POST["name"]) 
	){
		try {
			UsersController::registerNewUser($_POST["name"], $_POST["email"], $_POST["pass"]);  
		} catch (Exception $e) {
			die($e);
		}

		$_POST["request"] = "login";
		$_POST["user"]    = $_POST["email"];
		$_POST["pass"]    = $_POST["pass"];

		define("FL", 1);
	} else {
		die;
	}
}

/**
 *
 * If user is logged in, and somehow
 * reached this page, send him to home
 **/
//if (LoginController::isLoggedIn())
//	die(header("Location: index.php"));

//start creating the page,
//this pages handles login in
$page = new OmegaupComponentPage();

if (LoginController::isLoggedIn()) {
	if (isset($_REQUEST['redirect'])) {
		header("Location: {$_REQUEST['redirect']}");
	} else {
		header("Location: profile.php?");
	}
	exit;
}

/**
 * Login
 **/
$page->addComponent(new TitleComponent("&iquest; Ya tienes cuenta ?"));
$login_form = new FormComponent(new Users());
$login_form->addField("user", "Email o usuario"		, "input"	, ""		, "user");
$login_form->addField("pass", "Contrase&ntilde;a"	, "password", ""		, "pass");
$login_form->addSubmit("Iniciar sesion",  "nativeLogin.php" . (isset($_REQUEST['redirect']) ? "?redirect=" . urlencode($_REQUEST['redirect']) : ""), "POST");
$login_form->addField(""	, ""					, "hidden"	, "login"	, "request");
$page->addComponent($login_form);

/**
 * Third Party Login
 *
 **/
$page->addComponent(new TitleComponent("Unete a Omegaup !"));
$page->addComponent(new TitleComponent("&iquest; Tienes alguna cuenta en uno de estos sitios ?", 3));

$html = '
	<a href="googleLoginReturn.php"  title="log in with Google" style="background: #fff url(http://cdn.sstatic.net/Img/openid/openid-logos.png?v=8); background-position: -1px -1px" class="google openid_large_btn"></a>
	&nbsp;&nbsp;&nbsp;
<a href="' . LoginController::getFacebookLoginUrl() . '" title="log in with Facebook"  style="background: #fff url(http://cdn.sstatic.net/Img/openid/openid-logos.png?v=8); background-position: -1px -456px" class="facebook openid_large_btn"></a><a style="float:right"></a><br>
	';

$page->addComponent(new FreeHtmlComponent($html));

/**
 * Native registration
 **/
$page->addComponent(new FreeHtmlComponent("<h3 style='margin-top:65px'>&iquest; No es asi ? Registrate, es facil y rapido !</h3>"));
$reg_form = new FormComponent();

$reg_form->addField("name", "Nombre", "input", "", "name");
$reg_form->addField("email", "Email", "input", "", "email");

$reg_form->addField("pass", "Contrase&ntilde;a", "password", "", "pass");
$reg_form->addField("pass2", "Repetir contrase&ntilde;a", "password", "", "pass2");

$reg_form->addField("", "", "hidden", "register", "request");
$reg_form->addSubmit("Registrar", "nativeLogin.php" . (isset($_REQUEST['redirect']) ? "?redirect=" . urlencode($_REQUEST['redirect']) : ""), "POST");

$page->addComponent($reg_form);

$page->render();
