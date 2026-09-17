<?php
/*
	Question2Answer Markdown editor plugin
	License: http://www.gnu.org/licenses/gpl.html
*/

class qa_html_theme_layer extends qa_html_theme_base
{
	private $cssopt = 'markdown_editor_css';
	private $hljsopt = 'markdown_highlightjs';

	public function head_custom()
	{
		parent::head_custom();

		$tmpl = array('ask', 'question');
		if (!in_array($this->template, $tmpl))
			return;

		$hidecss = qa_opt($this->cssopt) === '1';
		$usehljs = qa_opt($this->hljsopt) === '1';
		$wmd_buttons = QA_HTML_THEME_LAYER_URLTOROOT.'pagedown/wmd-buttons.png';

		$this->output_raw(
			"<style>\n" .
			".wmd-button > span { background-image: url('$wmd_buttons') }\n"
		);

		// display CSS for Markdown Editor
		if (!$hidecss) {
			$cssWMD = file_get_contents(QA_HTML_THEME_LAYER_DIRECTORY.'pagedown/wmd.css');
			$this->output_raw($cssWMD);

			// display CSS for HighlightJS
			if ($usehljs)
			{
				$cssHJS = file_get_contents(QA_HTML_THEME_LAYER_DIRECTORY.'pagedown/highlightjs.css');
				$this->output_raw($cssHJS);
			}
		}

		$this->output_raw("</style>\n\n");

		// set up HighlightJS
		if ($usehljs) {
			$js = file_get_contents(QA_HTML_THEME_LAYER_DIRECTORY.'pagedown/highlightjs-run.js');

			$this->output_raw(
				'<script src="'.QA_HTML_THEME_LAYER_URLTOROOT.'pagedown/highlight.min.js"></script>' .
				'<script>'.$js.'</script>'
			);
		}
	}
}
