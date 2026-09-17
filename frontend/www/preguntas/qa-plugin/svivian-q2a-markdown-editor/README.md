Markdown Editor plugin for Question2Answer
=================================================

This is an editor plugin for popular open source Q&A platform, [Question2Answer](http://www.question2answer.org). It uses Markdown to format posts, which is a simple text-friendly markup language using for example \*\*bold\*\* for **bold text** or \> for quoting sources.

The plugin uses modified versions of the PageDown scripts (released by Stack Overflow) for the editor and live preview respectively.


Installation
-------------------------------------------------

1. Download and extract the `markdown-editor` folder to the `qa-plugins` folder in your Q2A installation.
2. If your site is a different language from English, copy `qa-md-lang-default.php` to the required language code (e.g. `qa-tt-lang-de.php` for German) and edit the phrases for your language.
3. Log in to your Q2A site as a Super Administrator and head to Admin > Posting.
4. Set the default editor for questions and answers to 'Markdown Editor'. The editor does also work for comments, but keeping to plain text is recommended.

In Admin > Plugins, you can set three options:

- "Don't add CSS inline" - this will not output the CSS onto the page, to allow putting it in a stylesheet instead which is more efficient. Copy the CSS from `pagedown/wmd.css` to the bottom of your theme's current stylesheet.
- "Plaintext comments" - Sets a post as plaintext when converting answers to comments.
- "Use syntax highlighting" - Integrates [highlight.js](http://softwaremaniacs.org/soft/highlight/en/) for code blocks (including while writing posts). All common programming languages are supported, but you can add more using the [customized download here](http://softwaremaniacs.org/soft/highlight/en/download/). Save the file and overwrite `pagedown/highlight.min.js`. If you ticked the box for CSS above, copy the CSS from `pagedown/highlightjs.css` to the bottom of your theme's current stylesheet.


Extra bits
-------------------------------------------------

**Converting old posts:** If you have been running your Q2A site for a little while, you may wish to convert old content to Markdown. This does not work reliably for HTML content (created via the WYSIWYG editor); it is pretty safe for plain text content, but check your posts afterwards as some formatting may go awry. You can convert text posts automatically using this SQL query:

    UPDATE qa_posts SET format='markdown' WHERE format='' AND type IN ('Q', 'A', 'Q_HIDDEN', 'A_HIDDEN')

(Make sure to change `qa_` above to your installation's table prefix if it is different.)


Pay What You Like
-------------------------------------------------

Most of my code is released under the open source GPLv3 license, and provided with a 'Pay What You Like' approach. Feel free to download and modify the plugins/themes to suit your needs, and I hope you value them enough to donate a few dollars.

### [Donate here](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4R5SHBNM3UDLU)
