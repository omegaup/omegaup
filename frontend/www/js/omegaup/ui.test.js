'use strict';

require('../dist/commons.js');
var omegaup = require('../dist/omegaup.js');

describe('omegaup.ui', function() {
  describe('formatString', function() {
    it('Should handle strings without replacements', function() {
      expect(omegaup.UI.formatString('hello', {})).toEqual('hello');
    });

    it('Should handle strings with replacements', function() {
      expect(omegaup.UI.formatString('%(greeting), %(target)!',
                                     {greeting: 'hello', target: 'world'}))
          .toEqual('hello, world!');
    });

    it('Should handle numbers', function() {
      expect(omegaup.UI.formatString('%(x)', {x: 42})).toEqual('42');
    });

    it('Should handle strings with multiple replacements', function() {
      expect(omegaup.UI.formatString('%(x) %(x)', {x: 'foo'}))
          .toEqual('foo foo');
    });
  });

  describe('parseDuration', function() {
    it('Should handle valid inputs', function() {
      expect(omegaup.UI.parseDuration('0')).toEqual(0);
      expect(omegaup.UI.parseDuration('1')).toEqual(1000.0);
      expect(omegaup.UI.parseDuration('1s')).toEqual(1000.0);
      expect(omegaup.UI.parseDuration('1ms')).toEqual(1.0);
      expect(omegaup.UI.parseDuration('1.0ms')).toEqual(1.0);
      expect(omegaup.UI.parseDuration('0.001s')).toEqual(1.0);
      expect(omegaup.UI.parseDuration('1m30s')).toEqual(90000.0);
    });

    it('Should reject invalid inputs', function() {
      expect(omegaup.UI.parseDuration('-1s')).toBe(null);
      expect(omegaup.UI.parseDuration('.s')).toBe(null);
    });
  });

  describe('markdownConverter', function() {
    let converter = omegaup.UI.markdownConverter();

    it('Should handle trivial inputs',
       function() { expect(converter.makeHtml('Foo')).toEqual('<p>Foo</p>'); });

    it('Should handle sample I/O tables', function() {
      expect(converter.makeHtml(`# Ejemplo

||input
1
2
||output
Case #1: 3
||description
Explicación
||input
5
10
||output
Case #2: 15
||end`)).toEqual(`<h1>Ejemplo</h1>

<table class="sample_io">
<thead><tr><th>Entrada</th><th>Salida</th><th>Descripción</th></tr></thead><tbody><tr><td><pre>1
2</pre></td><td><pre>Case #1: 3</pre></td><td><p>Explicación</p></td></tr><tr><td><pre>5
10</pre></td><td><pre>Case #2: 15</pre></td><td></td></tr></tbody>
</table>`);
    });

    it('Should handle GitHub-flavored Markdown tables', function() {
      expect(converter.makeHtml(`| foo | bar |
| --- | --- |
| baz | bim |`))
          .toEqual(`<table>
<thead>
<tr>
<th>foo</th>
<th>bar</th>
</tr>
</thead>
<tbody>
<tr>
<td>baz</td>
<td>bim</td>
</tr>
</tbody>
</table>`);

      expect(converter.makeHtml(`| abc | defghi |
| :-: | -----------: |
| bar | baz |`))
          .toEqual(`<table>
<thead>
<tr>
<th align="center">abc</th>
<th align="right">defghi</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center">bar</td>
<td align="right">baz</td>
</tr>
</tbody>
</table>`);

      expect(converter.makeHtml('| f\\|oo  |\n' +
                                '| ------ |\n' +
                                '| b ` \\| ` az |\n' +
                                '| b **\\|** im |'))
          .toEqual(`<table>
<thead>
<tr>
<th>f|oo</th>
</tr>
</thead>
<tbody>
<tr>
<td>b <code>|</code> az</td>
</tr>
<tr>
<td>b <strong>|</strong> im</td>
</tr>
</tbody>
</table>`);

      expect(converter.makeHtml(`| abc | def |
| --- | --- |
| bar | baz |
> bar`)).toEqual(`<table>
<thead>
<tr>
<th>abc</th>
<th>def</th>
</tr>
</thead>
<tbody>
<tr>
<td>bar</td>
<td>baz</td>
</tr>
</tbody>
</table>

<blockquote>
  <p>bar</p>
</blockquote>`);

      expect(converter.makeHtml(`| abc | def |
| --- |
| bar |`))
          .toEqual(`<p>| abc | def |
| --- |
| bar |</p>`);

      expect(converter.makeHtml(`| abc | def |
| --- | --- |
| bar |
| bar | baz | boo |`))
          .toEqual(`<table>
<thead>
<tr>
<th>abc</th>
<th>def</th>
</tr>
</thead>
<tbody>
<tr>
<td>bar</td>
<td></td>
</tr>
<tr>
<td>bar</td>
<td>baz</td>
</tr>
</tbody>
</table>`);

      expect(converter.makeHtml(`| abc | def |
| --- | --- |`))
          .toEqual(`<table>
<thead>
<tr>
<th>abc</th>
<th>def</th>
</tr>
</thead>
</table>`);
    });

    it('Should handle GitHub-flavored fenced code blocks', function() {
      // Simple example with backticks.
      expect(converter.makeHtml('```\n<\n >\n```'))
          .toEqual('<pre><code>&lt;\n &gt;\n</code></pre>');
      // Simple example with tildes.
      expect(converter.makeHtml('~~~\n<\n >\n~~~'))
          .toEqual('<pre><code>&lt;\n &gt;\n</code></pre>');
      // Fewer than three backticks is not enough.
      expect(converter.makeHtml('``\nfoo\n``'))
          .toEqual('<p><code>\nfoo\n</code></p>');
      // The closing code fence must use the same character as the opening
      // fence.
      expect(converter.makeHtml('```\naaa\n~~~\n```'))
          .toEqual('<pre><code>aaa\n~~~\n</code></pre>');
      expect(converter.makeHtml('~~~\naaa\n```\n~~~'))
          .toEqual('<pre><code>aaa\n```\n</code></pre>');
      // The closing code fence must be at least as long as the opening fence.
      expect(converter.makeHtml('````\naaa\n```\n``````'))
          .toEqual('<pre><code>aaa\n```\n</code></pre>');
      expect(converter.makeHtml('~~~~\naaa\n~~~\n~~~~~~'))
          .toEqual('<pre><code>aaa\n~~~\n</code></pre>');
      // A code block can have all empty lines as its content.
      expect(converter.makeHtml('```\n\n  \n```'))
          .toEqual('<pre><code>\n\n</code></pre>');
      // A code block can be empty.
      expect(converter.makeHtml('```\n```'))
          .toEqual('<pre><code></code></pre>');
      // Fences can be indented. If the opening fence is indented,
      // content lines will have equivalent opening indentation
      // removed, if present.
      expect(converter.makeHtml(' ```\n aaa\naaa\n```'))
          .toEqual('<pre><code>aaa\naaa\n</code></pre>');
      expect(converter.makeHtml('  ```\naaa\n  aaa\naaa\n  ```'))
          .toEqual('<pre><code>aaa\naaa\naaa\n</code></pre>');
      expect(converter.makeHtml('   ```\n   aaa\n    aaa\n  aaa\n   ```'))
          .toEqual('<pre><code>aaa\n aaa\naaa\n</code></pre>');
      // Four spaces indentation produces an indented code block.
      expect(converter.makeHtml('    ```\n    aaa\n    ```'))
          .toEqual('<pre><code>```\naaa\n```\n</code></pre>');
      // Closing fences may be indented by 0-3 spaces, and their
      // indentation need not match that of the opening fence.
      expect(converter.makeHtml('```\naaa\n  ```'))
          .toEqual('<pre><code>aaa\n</code></pre>');
      expect(converter.makeHtml('   ```\naaa\n  ```'))
          .toEqual('<pre><code>aaa\n</code></pre>');
      // This is not a closing fence, because it is indented 4 spaces.
      expect(converter.makeHtml('```\naaa\n    ```'))
          .not.toEqual('<pre><code>aaa\n</code></pre>');
      // Code fences (opening and closing) cannot contain internal spaces.
      expect(converter.makeHtml('``` ```\naaa'))
          .toEqual('<p><code></code>\naaa</p>');
      expect(converter.makeHtml('~~~~~~\naaa\n~~~ ~~'))
          .toEqual('<p>~~~~~~\naaa\n~~~ ~~</p>');
      // Fenced code blocks can interrupt paragraphs, and can be
      // followed directly by paragraphs, without a blank line
      // between.
      expect(converter.makeHtml('foo\n```\nbar\n```\nbaz'))
          .toEqual('<p>foo</p>\n\n<pre><code>bar\n</code></pre>\n\n<p>baz</p>');
      // Other blocks can also occur before and after fenced code
      // blocks without an intervening blank line.
      expect(converter.makeHtml('foo\n---\n~~~\nbar\n~~~\n# baz'))
          .toEqual(
              '<h2>foo</h2>\n\n<pre><code>bar\n</code></pre>\n\n<h1>baz</h1>');
      // An info string can be provided after the opening code fence.
      // Although this spec doesn’t mandate any particular treatment
      // of the info string, the first word is typically used to
      // specify the language of the code block. In HTML output, the
      // language is normally indicated by adding a class to the code
      // element consisting of language- followed by the language
      // name.
      expect(converter.makeHtml('```ruby\ndef foo(x)\n  return 3\nend\n```'))
          .toEqual(
              '<pre><code class="language-ruby">def foo(x)\n  return 3\nend\n</code></pre>');
      expect(
          converter.makeHtml(
              '~~~~    ruby startline=3 $%@#$\ndef foo(x)\n  return 3\nend\n~~~~~~~'))
          .toEqual(
              '<pre><code class="language-ruby">def foo(x)\n  return 3\nend\n</code></pre>');
      // Info strings for backtick code blocks cannot contain backticks.
      expect(converter.makeHtml('``` aa ```\nfoo'))
          .toEqual('<p><code>aa</code>\nfoo</p>');
      // Info strings for tilde code blocks can contain backticks and tildes.
      expect(converter.makeHtml('~~~ aa ``` ~~~\nfoo\n~~~'))
          .toEqual('<pre><code class="language-aa">foo\n</code></pre>');
      // Closing code fences cannot have info strings.
      expect(converter.makeHtml('```\n``` aaa\n```'))
          .toEqual('<pre><code>``` aaa\n</code></pre>');
      // All Markdown special characters should be escaped.
      expect(converter.makeHtml('```\n<>&\n*foo* _bar_\n[img](img)\n\\\n```'))
          .toEqual(
              '<pre><code>&lt;&gt;&amp;\n*foo* _bar_\n[img](img)\n\\\n</code></pre>');
    });
  });
});
