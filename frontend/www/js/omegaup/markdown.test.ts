import * as markdown from './markdown';

describe('markdown', () => {
  describe('Converter', () => {
    const converter = new markdown.Converter({ preview: true });

    it('Should handle trivial inputs', () => {
      expect(converter.makeHtml('Foo')).toEqual('<p>Foo</p>');
    });

    it('Should handle templates', () => {
      expect(converter.makeHtml('{{libinteractive:download}}')).toEqual(
        '<p><code class="libinteractive-download"><i class="glyphicon glyphicon-download-alt"></i></code></p>',
      );
      expect(converter.makeHtml('{{output-only:download}}')).toEqual(
        '<p><code class="output-only-download"><i class="glyphicon glyphicon-download-alt"></i></code></p>',
      );
    });

    it('Should handle path-style links', () => {
      expect(converter.makeHtml('[foo](/foo)')).toEqual(
        '<p><a href="/foo" target="_blank" rel="noopener noreferrer">foo</a></p>',
      );
    });

    it('Should handle path-style links with titles', () => {
      expect(converter.makeHtml('[foo](/foo "foo")')).toEqual(
        '<p><a href="/foo" target="_blank" rel="noopener noreferrer" title="foo">foo</a></p>',
      );
    });

    it('Should handle OmegaUp domain links', () => {
      const url = window.location.origin;
      expect(converter.makeHtml(`[OmegaUp](${url})`)).toEqual(
        `<p><a href="${url}">OmegaUp</a></p>`,
      );
    });

    it('Should handle mailto links', () => {
      expect(
        converter.makeHtml('[foo](mailto:foo@foo.com?subject=foo)'),
      ).toEqual(
        '<p><a href="mailto:foo@foo.com?subject=foo" target="_blank" rel="noopener noreferrer">foo</a></p>',
      );
    });

    it('Should handle mailto links with titles', () => {
      expect(
        converter.makeHtml('[foo](mailto:foo@foo.com?subject=foo "foo")'),
      ).toEqual(
        '<p><a href="mailto:foo@foo.com?subject=foo" target="_blank" rel="noopener noreferrer" title="foo">foo</a></p>',
      );
    });

    it('Should handle invalid iframe tag', () => {
      expect(
        converter.makeHtml(
          '<iframe src="https://www.facebook.com/embed/enMumwvLAug" frameborder="0" allowfullscreen="true"></iframe>',
        ),
      ).toEqual('');
    });

    it('Should handle valid iframe tag form facebook', () => {
      expect(
        converter.makeHtml(
          '<iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Fomegaup%2Fvideos%2F291451792022031%2F&show_text=0&width=560" width="560" height="315" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>',
        ),
      ).toEqual(
        '<iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Fomegaup%2Fvideos%2F291451792022031%2F&show_text=0&width=560" width="560" height="315" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>',
      );
    });

    it('Should handle valid iframe tag from youtube', () => {
      expect(
        converter.makeHtml(`<figure class="video_container">
           <iframe src="https://www.youtube.com/embed/enMumwvLAug" frameborder="0" allowfullscreen="true"> </iframe>
         </figure>`),
      ).toEqual(`<p><figure class="video_container">
           <iframe src="https://www.youtube.com/embed/enMumwvLAug" frameborder="0" allowfullscreen="true"> </iframe>
         </figure></p>`);
    });

    it('Should handle valid iframe tag with extra attributes', () => {
      expect(
        converter.makeHtml(`<figure class="video_container">
           <iframe width="560" height="315" src="https://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen> </iframe>
        </figure>`),
      ).toEqual(`<p><figure class="video_container">
           <iframe width="560" height="315" src="https://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen> </iframe>
        </figure></p>`);
    });

    it('Should handle details/summary tags', () => {
      expect(
        converter.makeHtml('<details><summary>SPOILER</summary>Hi</details>'),
      ).toEqual('<p><details><summary>SPOILER</summary>Hi</details></p>');
    });

    it('Should handle sample I/O tables', () => {
      expect(
        converter.makeHtml(`# Ejemplo

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
||end`),
      ).toEqual(`<h1>Ejemplo</h1>

<table class="sample_io">
<thead><tr><th>Entrada</th><th>Salida</th><th>Descripción</th></tr></thead>
<tbody><tr><td><pre>1
2</pre></td><td><pre>Case #1: 3</pre></td><td><p>Explicación</p></td></tr>
<tr><td><pre>5
10</pre></td><td><pre>Case #2: 15</pre></td><td></td></tr>
</tbody>
</table>`);
    });

    it('Should handle sample I/O tables with markdown', () => {
      expect(
        converter.makeHtml(`# Ejemplo

||input
5 5 2
#####
#A#B#
#...#
#b#a#
#####

headers
=======

- lists

****

----

____

\`\`\`
github flavored markdown
\`\`\`

> hi <
> hello <

    other kind of blockquote

Other escapes: $~~T~D~E32E

Tags <b>hello</b>

<pre>hi</pre>
||output
0
||end`),
      ).toEqual(`<h1>Ejemplo</h1>

<table class="sample_io">
<thead><tr><th>Entrada</th><th>Salida</th></tr></thead>
<tbody><tr><td><pre>5 5 2
#####
#A#B#
#...#
#b#a#
#####

headers
=======

- lists

****

----

____

\`\`\`
github flavored markdown
\`\`\`

&gt; hi &lt;
&gt; hello &lt;

    other kind of blockquote

Other escapes: $~~T~D~E32E

Tags &lt;b&gt;hello&lt;/b&gt;

&lt;pre&gt;hi&lt;/pre&gt;</pre></td><td><pre>0</pre></td></tr>
</tbody>
</table>`);
    });

    it('Should handle sample I/O tables from files', () => {
      expect(
        converter.makeHtmlWithImages(
          `# Ejemplo

||examplefile
one
||description
yes
||examplefile
two
||end`,
          {},
          {},
          {
            cases: {
              one: {
                in: 'hello',
                out: 'world',
              },
              two: {
                in: `5 5 2
#####
#A#B#
#...#
#b#a#
#####

headers
=======

- lists

****

----

____

\`\`\`
github flavored markdown
\`\`\`

> hi <
> hello <

    other kind of blockquote

Other escapes: $~~T~D~E32E

Tags <b>hello</b>

<pre>hi</pre>`,
                out: '0',
              },
            },
            limits: {
              ExtraWallTime: '0s',
              MemoryLimit: 0,
              OutputLimit: 0,
              OverallWallTimeLimit: '0s',
              TimeLimit: '0s',
            },
            validator: {
              name: 'token',
            },
          },
        ),
      ).toEqual(`<h1>Ejemplo</h1>

<table class="sample_io">
<thead><tr><th>Entrada</th><th>Salida</th><th>Descripción</th></tr></thead>
<tbody><tr><td><pre>hello</pre></td><td><pre>world</pre></td><td><p>yes</p></td></tr>
<tr><td><pre>5 5 2
#####
#A#B#
#...#
#b#a#
#####

headers
=======

- lists

****

----

____

\`\`\`
github flavored markdown
\`\`\`

&gt; hi &lt;
&gt; hello &lt;

    other kind of blockquote

Other escapes: $~~T~D~E32E

Tags &lt;b&gt;hello&lt;/b&gt;

&lt;pre&gt;hi&lt;/pre&gt;</pre></td><td><pre>0</pre></td><td></td></tr>
</tbody>
</table>`);
    });

    it('Should handle GitHub-flavored Markdown tables', () => {
      expect(
        converter.makeHtml(`| foo | bar |
| --- | --- |
| baz | bim |`),
      ).toEqual(`<table>
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

      expect(
        converter.makeHtml(`| abc | defghi |
| :-: | -----------: |
| bar | baz |`),
      ).toEqual(`<table>
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

      expect(
        converter.makeHtml(
          '| f\\|oo  |\n' +
            '| ------ |\n' +
            '| b ` \\| ` az |\n' +
            '| b **\\|** im |',
        ),
      ).toEqual(`<table>
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

      expect(
        converter.makeHtml(`| abc | def |
| --- | --- |
| bar | baz |
> bar`),
      ).toEqual(`<table>
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

      expect(
        converter.makeHtml(`| abc | def |
| --- |
| bar |`),
      ).toEqual(`<p>| abc | def |
| --- |
| bar |</p>`);

      expect(
        converter.makeHtml(`| abc | def |
| --- | --- |
| bar |
| bar | baz | boo |`),
      ).toEqual(`<table>
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

      expect(
        converter.makeHtml(`| abc | def |
| --- | --- |`),
      ).toEqual(`<table>
<thead>
<tr>
<th>abc</th>
<th>def</th>
</tr>
</thead>
</table>`);
    });

    it('Should handle GitHub-flavored fenced code blocks', () => {
      // Simple example with backticks.
      expect(converter.makeHtml('```\n<\n >\n```')).toEqual(
        '<pre><code>&lt;\n &gt;\n</code></pre>',
      );
      // Simple example with tildes.
      expect(converter.makeHtml('~~~\n<\n >\n~~~')).toEqual(
        '<pre><code>&lt;\n &gt;\n</code></pre>',
      );
      // Fewer than three backticks is not enough.
      expect(converter.makeHtml('``\nfoo\n``')).toEqual(
        '<p><code>\nfoo\n</code></p>',
      );
      // The closing code fence must use the same character as the opening
      // fence.
      expect(converter.makeHtml('```\naaa\n~~~\n```')).toEqual(
        '<pre><code>aaa\n~~~\n</code></pre>',
      );
      expect(converter.makeHtml('~~~\naaa\n```\n~~~')).toEqual(
        '<pre><code>aaa\n```\n</code></pre>',
      );
      // The closing code fence must be at least as long as the opening fence.
      expect(converter.makeHtml('````\naaa\n```\n``````')).toEqual(
        '<pre><code>aaa\n```\n</code></pre>',
      );
      expect(converter.makeHtml('~~~~\naaa\n~~~\n~~~~~~')).toEqual(
        '<pre><code>aaa\n~~~\n</code></pre>',
      );
      // A code block can have all empty lines as its content.
      expect(converter.makeHtml('```\n\n  \n```')).toEqual(
        '<pre><code>\n\n</code></pre>',
      );
      // A code block can be empty.
      expect(converter.makeHtml('```\n```')).toEqual(
        '<pre><code></code></pre>',
      );
      // Fences can be indented. If the opening fence is indented,
      // content lines will have equivalent opening indentation
      // removed, if present.
      expect(converter.makeHtml(' ```\n aaa\naaa\n```')).toEqual(
        '<pre><code>aaa\naaa\n</code></pre>',
      );
      expect(converter.makeHtml('  ```\naaa\n  aaa\naaa\n  ```')).toEqual(
        '<pre><code>aaa\naaa\naaa\n</code></pre>',
      );
      expect(
        converter.makeHtml('   ```\n   aaa\n    aaa\n  aaa\n   ```'),
      ).toEqual('<pre><code>aaa\n aaa\naaa\n</code></pre>');
      // Four spaces indentation produces an indented code block.
      expect(converter.makeHtml('    ```\n    aaa\n    ```')).toEqual(
        '<pre><code>```\naaa\n```\n</code></pre>',
      );
      // Closing fences may be indented by 0-3 spaces, and their
      // indentation need not match that of the opening fence.
      expect(converter.makeHtml('```\naaa\n  ```')).toEqual(
        '<pre><code>aaa\n</code></pre>',
      );
      expect(converter.makeHtml('   ```\naaa\n  ```')).toEqual(
        '<pre><code>aaa\n</code></pre>',
      );
      // This is not a closing fence, because it is indented 4 spaces.
      expect(converter.makeHtml('```\naaa\n    ```')).not.toEqual(
        '<pre><code>aaa\n</code></pre>',
      );
      // Code fences (opening and closing) cannot contain internal spaces.
      expect(converter.makeHtml('``` ```\naaa')).toEqual(
        '<p><code></code>\naaa</p>',
      );
      expect(converter.makeHtml('~~~~~~\naaa\n~~~ ~~')).toEqual(
        '<p>~~~~~~\naaa\n~~~ ~~</p>',
      );
      // Fenced code blocks can interrupt paragraphs, and can be
      // followed directly by paragraphs, without a blank line
      // between.
      expect(converter.makeHtml('foo\n```\nbar\n```\nbaz')).toEqual(
        '<p>foo</p>\n\n<pre><code>bar\n</code></pre>\n\n<p>baz</p>',
      );
      // Other blocks can also occur before and after fenced code
      // blocks without an intervening blank line.
      expect(converter.makeHtml('foo\n---\n~~~\nbar\n~~~\n# baz')).toEqual(
        '<h2>foo</h2>\n\n<pre><code>bar\n</code></pre>\n\n<h1>baz</h1>',
      );
      // An info string can be provided after the opening code fence.
      // Although this spec doesn’t mandate any particular treatment
      // of the info string, the first word is typically used to
      // specify the language of the code block. In HTML output, the
      // language is normally indicated by adding a class to the code
      // element consisting of language- followed by the language
      // name.
      expect(
        converter.makeHtml('```ruby\ndef foo(x)\n  return 3\nend\n```'),
      ).toEqual(
        '<pre><code class="language-ruby"><span class="token keyword">def</span> <span class="token method-definition"><span class="token function">foo</span></span><span class="token punctuation">(</span>x<span class="token punctuation">)</span>\n  <span class="token keyword">return</span> <span class="token number">3</span>\n<span class="token keyword">end</span>\n</code></pre>',
      );
      expect(
        converter.makeHtml(
          '~~~~    ruby startline=3 $%@#$\ndef foo(x)\n  return 3\nend\n~~~~~~~',
        ),
      ).toEqual(
        '<pre><code class="language-ruby"><span class="token keyword">def</span> <span class="token method-definition"><span class="token function">foo</span></span><span class="token punctuation">(</span>x<span class="token punctuation">)</span>\n  <span class="token keyword">return</span> <span class="token number">3</span>\n<span class="token keyword">end</span>\n</code></pre>',
      );
      // Info strings for backtick code blocks cannot contain backticks.
      expect(converter.makeHtml('``` aa ```\nfoo')).toEqual(
        '<p><code>aa</code>\nfoo</p>',
      );
      // Info strings for tilde code blocks can contain backticks and tildes.
      expect(converter.makeHtml('~~~ aa ``` ~~~\nfoo\n~~~')).toEqual(
        '<pre><code class="language-aa">foo\n</code></pre>',
      );
      // Closing code fences cannot have info strings.
      expect(converter.makeHtml('```\n``` aaa\n```')).toEqual(
        '<pre><code>``` aaa\n</code></pre>',
      );
      // All Markdown special characters should be escaped.
      expect(
        converter.makeHtml('```\n<>&\n*foo* _bar_\n[img](img)\n\\\n```'),
      ).toEqual(
        '<pre><code>&lt;&gt;&amp;\n*foo* _bar_\n[img](img)\n\\\n</code></pre>',
      );
      expect(
        converter.makeHtml('```python\ndef foo(x):\n  return 3\n```'),
      ).toEqual(
        '<pre><code class="language-python"><span class="token keyword">def</span> <span class="token function">foo</span><span class="token punctuation">(</span>x<span class="token punctuation">)</span><span class="token punctuation">:</span>\n  <span class="token keyword">return</span> <span class="token number">3</span>\n</code></pre>',
      );
      expect(
        converter.makeHtml(
          '```\n#include <iostream>\n#include <map>\n###\n```',
        ),
      ).toEqual(
        '<pre><code>#include &lt;iostream&gt;\n#include &lt;map&gt;\n###\n</code></pre>',
      );
      expect(
        converter.makeHtml(
          '```cpp\n#include <iostream>\n#include <map>\n###\n```',
        ),
      ).toEqual(
        '<pre><code class="language-cpp"><span class="token macro property"><span class="token directive-hash">#</span><span class="token directive keyword">include</span> <span class="token string">&lt;iostream></span></span>\n<span class="token macro property"><span class="token directive-hash">#</span><span class="token directive keyword">include</span> <span class="token string">&lt;map></span></span>\n###\n</code></pre>',
      );
    });

    it('Should handle file transclusions', () => {
      expect(
        converter.makeHtmlWithImages(
          '{{sample.py}}',
          {},
          { 'sample.py': 'def foo(x):\n  return 3\n' },
        ),
      ).toEqual(
        '<p><pre><code class="language-python"><span class="token keyword">def</span> <span class="token function">foo</span><span class="token punctuation">(</span>x<span class="token punctuation">)</span><span class="token punctuation">:</span>\n  <span class="token keyword">return</span> <span class="token number">3</span>\n</code></pre></p>',
      );
      // All Markdown special characters should be escaped.
      expect(
        converter.makeHtmlWithImages(
          '{{Sample.in}}',
          {},
          { 'Sample.in': '<>&\n*foo* _bar_\n[img](img)\n\\\n' },
        ),
      ).toEqual(
        '<p><pre><code class="language-in">&lt;&gt;&amp;\n*foo* _bar_\n[img](img)\n\\\n</code></pre></p>',
      );
    });
  });
});
