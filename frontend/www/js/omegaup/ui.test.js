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

  describe('markdownConverter',
           function() {
             let converter = omegaup.UI.markdownConverter();

             it('Should handle trivial inputs', function() {
               expect(converter.makeHtml('Foo')).toEqual('<p>Foo</p>');
             });

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
<thead><tr><th>Entrada</th><th>Salida</th><th>Descripci&oacute;n</th></tr></thead><tbody><tr><td><pre>1
2</pre></td><td><pre>Case #1: 3</pre></td><td><p>Explicación</p></td></tr><tr><td><pre>5
10</pre></td><td><pre>Case #2: 15</pre></td><td></td></tr></tbody>
</table>`);
             });

             it('Should handle GitHub-flavored Markdown tables', function() {
               expect(converter.makeHtml(`| foo | bar |
| --- | --- |
| baz | bim |`)).toEqual(`<table>
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
| bar | baz |`)).toEqual(`<table>
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
| bar |`)).toEqual(`<p>| abc | def |
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
| --- | --- |`)).toEqual(`<table>
<thead>
<tr>
<th>abc</th>
<th>def</th>
</tr>
</thead>
</table>`);
             });
           });
});
