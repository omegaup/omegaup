<?php

	   /*
		* LEVEL_NEEDED defines the users who can see this page.
		* Anyone without permission to see this page, will	
		* be redirected to a page saying so.
		* This variable *must* be set in order to bootstrap
		* to continue. This is by design, in order to prevent
		* leaving open holes for new pages.
		* 
		* */
	define( "LEVEL_NEEDED", false );

	require_once( "../server/inc/bootstrap.php" );

	require_once(SERVER_PATH . '/libs/Markdown/markdown.php');

        $page = new OmegaupComponentPage();

	$form = new FormComponent();

	if (!$_POST['source']) {
		$_POST['source'] = "# Descripción\n\nEsta es la descripción del problema. Inventa una historia creativa.\n\n# Entrada\n\nAquí va la descripción de la entrada del problema.\n\n# Salida\n\nEsta es la descripción de la salida esperada.\n\n# Ejemplo\n\nTabla|Tabla\n--------------\nTabla|Tabla\n\n# Límites\n\n* Aquí\n* Van\n* Los\n* Límites";
	}

	$form->addField('markdownSource', '', 'textarea', $_POST['source'], 'source');
	$form->addSubmit('Previsualzación', 'redaccion.php', 'POST');
	
	$page->addComponent(new FreeHTMLComponent('<script type="text/javascript" src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>'));
	$page->addComponent(new FreeHTMLComponent('<script type="text/x-mathjax-config">MathJax.Hub.Config({tex2jax: {inlineMath: [[\'$\',\'$\'], [\'\\\\(\',\'\\\\)\']]}});</script>'));
	$page->addComponent(new FreeHTMLComponent('<div>Explicación :P</div>'));
	$page->addComponent(new FreeHTMLComponent('<div id="markdownPreview" class="problem-statement">' . markdown($_POST['source']) . '</div>'));
	$page->addComponent($form);
	$page->addComponent(new FreeHTMLComponent('<div style="clear: right"></div>'));

        $page->render();

