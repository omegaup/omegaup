<?php
/*
	Plugin Name: Markdown Editor
	Plugin URI: https://github.com/svivian/q2a-markdown-editor
	Plugin Description: Markdown editor plugin for simple text-based markup
	Plugin Version: 2.5.1
	Plugin Date: 2016-06-21
	Plugin Author: Scott Vivian
	Plugin Author URI: http://codelair.com/
	Plugin Contributors: NoahY
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.6
	Plugin Minimum PHP Version: 5.4
	Plugin Update Check URI: https://raw.githubusercontent.com/svivian/q2a-markdown-editor/master/qa-plugin.php

	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

	More about this license: http://www.gnu.org/licenses/gpl.html
*/

if (!defined('QA_VERSION')) exit;


qa_register_plugin_module('editor', 'qa-markdown-editor.php', 'qa_markdown_editor', 'Markdown Editor');
qa_register_plugin_module('viewer', 'qa-markdown-viewer.php', 'qa_markdown_viewer', 'Markdown Viewer');
qa_register_plugin_module('event', 'qa-md-events.php', 'qa_markdown_events', 'Markdown events');
qa_register_plugin_layer('qa-md-layer.php', 'Markdown Editor layer');
qa_register_plugin_phrases('qa-md-lang-*.php', 'markdown');


// autoloader for cebe\markdown traits
spl_autoload_register(function($class) {
	$baseNS = 'cebe\markdown';
	if (strpos($class, $baseNS) === 0) {
		$pathNS = substr($class, strlen($baseNS));
		$file = __DIR__ . '/cebe-markdown' . str_replace('\\', '/', $pathNS) . '.php';
		require_once $file;
	}
});
