<?php
	require_once('../server/bootstrap.php');
	require_once('../server/libs/Markdown/markdown.php');

	if (!$_POST['source']) {
		$_POST['source'] = "# Descripción\n\nEsta es la descripción del problema. Inventa una historia creativa. Puedes utilizar matemáticas inline para hacer \$x_i, y_i\$, o \$z_i\$ o incluso \$\$x=\\frac{b\\pm \\sqrt{b^2 -4ac}}{2a}\$\$.\n\n# Entrada\n\nAquí va la descripción de la entrada del problema.\n\n# Salida\n\nEsta es la descripción de la salida esperada.\n\n# Ejemplo\n\n||input\n1\n2\n||output\nCase #1: 3\n||description\nExplicación\n||input\n5\n10\n||output\nCase #2: 15\n||end\n\n# Límites\n\n* Aquí\n* Van\n* Los\n* Límites";
	}

	$smarty->assign('LOAD_MATHJAX', true);
	$smarty->assign('source', $_POST['source']);
	$smarty->assign('markdown', markdown($_POST['source']));

	$smarty->display( '../templates/redaccion.tpl' );
