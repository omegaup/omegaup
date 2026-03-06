import { mount } from '@vue/test-utils';
import Vue from 'vue';

import * as ui from '../../ui';
import * as markdown from '../../markdown';
import T from '../../lang';

import ProblemMarkdown from './ProblemMarkdown.vue';

describe('problem/ProblemMarkdown.vue', () => {
  afterEach(() => {
    // Clean up MathJax globals between tests to prevent interference
    document.getElementById('MathJax-script')?.remove();
    delete (window as any).MathJax;
    jest.restoreAllMocks();
  });

  // =========================================================================
  // Existing tests (preserved)
  // =========================================================================

  it('renders simple markdown with MathJax and template', async () => {
    const wrapper = mount(ProblemMarkdown, {
      propsData: {
        markdown: '$a^2 + b^2 = c^2$\n\n{{output-only:download}}',
      },
    });

    await wrapper.vm.$nextTick();

    // Confirm MathJax content appears
    expect(wrapper.text()).toContain('$a^2 + b^2 = c^2$');

    // Confirm template was mounted
    expect(wrapper.find('.output-only-download').exists()).toBe(true);
  });

  it('renders libinteractive template properly', async () => {
    const wrapper = mount(ProblemMarkdown, {
      propsData: {
        markdown: '{{libinteractive:download}}',
      },
    });

    await wrapper.vm.$nextTick();

    expect(wrapper.find('.libinteractive-download').exists()).toBe(true);
  });

  it('cleans up templates on markdown change', async () => {
    const wrapper = mount(ProblemMarkdown, {
      propsData: {
        markdown: '{{output-only:download}}',
      },
    });

    await wrapper.vm.$nextTick();
    expect(wrapper.find('.output-only-download').exists()).toBe(true);

    await wrapper.setProps({ markdown: 'Plain content' });
    await wrapper.vm.$nextTick();

    expect(wrapper.find('.output-only-download').exists()).toBe(false);
  });

  // =========================================================================
  // Group 1: Rendering & Computed Property `html`
  // =========================================================================

  describe('Computed property: html', () => {
    it('uses makeHtml when no imageMapping or problemSettings', () => {
      const makeHtmlSpy = jest.spyOn(
        markdown.Converter.prototype,
        'makeHtml',
      );
      const makeHtmlWithImagesSpy = jest.spyOn(
        markdown.Converter.prototype,
        'makeHtmlWithImages',
      );

      mount(ProblemMarkdown, {
        propsData: {
          markdown: '# Hello World',
        },
      });

      expect(makeHtmlSpy).toHaveBeenCalled();
      expect(makeHtmlWithImagesSpy).not.toHaveBeenCalled();
    });

    it('uses makeHtmlWithImages when imageMapping is provided', () => {
      const makeHtmlWithImagesSpy = jest.spyOn(
        markdown.Converter.prototype,
        'makeHtmlWithImages',
      );

      mount(ProblemMarkdown, {
        propsData: {
          markdown: '# Hello',
          imageMapping: { 'fig.png': 'data:image/png;base64,abc' },
        },
      });

      expect(makeHtmlWithImagesSpy).toHaveBeenCalledWith(
        '# Hello',
        { 'fig.png': 'data:image/png;base64,abc' },
        {},
        undefined,
      );
    });

    it('uses makeHtmlWithImages when problemSettings is provided', () => {
      const makeHtmlWithImagesSpy = jest.spyOn(
        markdown.Converter.prototype,
        'makeHtmlWithImages',
      );
      const settings = {
        cases: { easy: { in: '1 2', out: '3' } },
        limits: {
          ExtraWallTime: '0s',
          MemoryLimit: 67108864,
          OutputLimit: 10240,
          OverallWallTimeLimit: '30s',
          TimeLimit: '1s',
        },
        validator: { name: 'token-caseless' },
      };

      mount(ProblemMarkdown, {
        propsData: {
          markdown: '# Hello',
          problemSettings: settings,
        },
      });

      expect(makeHtmlWithImagesSpy).toHaveBeenCalledWith(
        '# Hello',
        {},
        {},
        settings,
      );
    });

    it('uses makeHtmlWithImages when both imageMapping and problemSettings are provided', () => {
      const makeHtmlWithImagesSpy = jest.spyOn(
        markdown.Converter.prototype,
        'makeHtmlWithImages',
      );
      const imageMapping = { 'fig.png': 'data:image/png;base64,abc' };
      const settings = {
        cases: {},
        limits: {
          ExtraWallTime: '0s',
          MemoryLimit: 67108864,
          OutputLimit: 10240,
          OverallWallTimeLimit: '30s',
          TimeLimit: '1s',
        },
        validator: { name: 'token-caseless' },
      };

      mount(ProblemMarkdown, {
        propsData: {
          markdown: '# Both',
          imageMapping,
          problemSettings: settings,
        },
      });

      expect(makeHtmlWithImagesSpy).toHaveBeenCalledWith(
        '# Both',
        imageMapping,
        {},
        settings,
      );
    });

    it('handles empty markdown string', () => {
      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: '',
        },
      });

      // Should render without errors, root should have no meaningful content
      expect(wrapper.find('[data-markdown-statement]').exists()).toBe(true);
    });

    it('applies full-width class when fullWidth prop is true', () => {
      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: '# Hello',
          fullWidth: true,
        },
      });

      expect(wrapper.find('[data-markdown-statement]').classes()).toContain(
        'full-width',
      );
    });

    it('does not apply full-width class by default', () => {
      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: '# Hello',
        },
      });

      expect(wrapper.find('[data-markdown-statement]').classes()).not.toContain(
        'full-width',
      );
    });
  });

  // =========================================================================
  // Group 2: Clipboard Button Injection
  // =========================================================================

  describe('Clipboard button injection', () => {
    it('adds clipboard buttons to <pre> elements with content', async () => {
      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: '```\nhello world\n```',
        },
      });

      await wrapper.vm.$nextTick();

      const buttons = wrapper.findAll('button.clipboard');
      expect(buttons.length).toBeGreaterThan(0);
    });

    it('does not add clipboard button to <pre> with no firstChild', async () => {
      // Spy on makeHtml to return a <pre> with no children
      jest
        .spyOn(markdown.Converter.prototype, 'makeHtml')
        .mockReturnValue('<pre></pre>');

      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: 'anything',
        },
      });

      await wrapper.vm.$nextTick();

      expect(wrapper.findAll('button.clipboard').length).toBe(0);
    });

    it('clipboard button calls ui.copyToClipboard on click', async () => {
      const copyToClipboardSpy = jest
        .spyOn(ui, 'copyToClipboard')
        .mockImplementation(() => { });

      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: '```\ntest content\n```',
        },
      });

      await wrapper.vm.$nextTick();

      const clipboardButton = wrapper.find('button.clipboard');
      expect(clipboardButton.exists()).toBe(true);
      await clipboardButton.trigger('click');

      expect(copyToClipboardSpy).toHaveBeenCalled();
    });

    it('clipboard button has correct class, title, and emoji content', async () => {
      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: '```\nsome code\n```',
        },
      });

      await wrapper.vm.$nextTick();

      const btn = wrapper.find('button.clipboard');
      expect(btn.exists()).toBe(true);
      expect(btn.classes()).toContain('btn');
      expect(btn.classes()).toContain('btn-light');
      expect(btn.attributes('title')).toBe(T.wordsCopyToClipboard);
      expect(btn.text()).toBe('📋');
    });

    it('adds clipboard buttons to sample_io table cells', async () => {
      // Mock makeHtml to guarantee a sample_io table structure that matches
      // the selector: .sample_io > tbody > tr > td:first-of-type > pre
      jest
        .spyOn(markdown.Converter.prototype, 'makeHtml')
        .mockReturnValue(
          '<table class="sample_io"><tbody>' +
            '<tr><td><pre>1 2</pre></td><td><pre>3</pre></td></tr>' +
            '</tbody></table>',
        );

      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: 'anything',
        },
      });

      await wrapper.vm.$nextTick();

      const buttons = wrapper.findAll('table.sample_io button.clipboard');
      expect(buttons.length).toBeGreaterThan(0);
    });
  });

  // =========================================================================
  // Group 3: MathJax Integration
  // =========================================================================

  describe('MathJax integration', () => {
    it('first load: creates MathJax config, queues element, and creates script tag', async () => {
      // Ensure MathJax is not set
      delete (window as any).MathJax;

      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: '$x^2$',
        },
      });

      await wrapper.vm.$nextTick();

      // Config should be created
      expect(window.MathJax).toBeDefined();
      expect(window.MathJax!.tex).toBeDefined();
      expect(window.MathJax!.tex.inlineMath).toEqual([
        ['$', '$'],
        ['\\(', '\\)'],
      ]);
      expect(window.MathJax!.startup).toBeDefined();

      // Element should be queued (falls through from config creation)
      expect(window.MathJax!.startup.elements).toBeDefined();
      expect(window.MathJax!.startup.elements!.length).toBeGreaterThan(0);

      // Script tag should be created
      const scriptTag = document.getElementById(
        'MathJax-script',
      ) as HTMLScriptElement;
      expect(scriptTag).not.toBeNull();
      expect(scriptTag.src).toContain('mathjax');
    });

    it('does not create duplicate script elements on second mount', async () => {
      delete (window as any).MathJax;

      // First mount creates the script
      const wrapper1 = mount(ProblemMarkdown, {
        propsData: { markdown: '$x$' },
      });
      await wrapper1.vm.$nextTick();

      const scriptsAfterFirst = document.querySelectorAll('#MathJax-script');
      expect(scriptsAfterFirst.length).toBe(1);

      // Second mount (MathJax.startup exists now, so script creation is skipped)
      // But MathJax.typeset is still not available so element is queued
      const wrapper2 = mount(ProblemMarkdown, {
        propsData: { markdown: '$y$' },
      });
      await wrapper2.vm.$nextTick();

      const scriptsAfterSecond = document.querySelectorAll('#MathJax-script');
      expect(scriptsAfterSecond.length).toBe(1);
    });

    it('queues element when MathJax.startup exists but typeset is not ready', async () => {
      // Simulate: script is loaded, config exists, but typeset not yet available
      (window as any).MathJax = {
        tex: {},
        startup: {
          typeset: true,
          elements: [],
          ready: () => { },
        },
        options: {},
        loader: {},
      };

      const wrapper = mount(ProblemMarkdown, {
        propsData: { markdown: '$z$' },
      });

      await wrapper.vm.$nextTick();

      // Element should be queued since typeset function doesn't exist
      expect(window.MathJax!.startup.elements!.length).toBe(1);
    });

    it('calls MathJax.typeset directly when fully loaded', async () => {
      const typesetMock = jest.fn();
      (window as any).MathJax = {
        tex: {},
        startup: {
          typeset: true,
          elements: [],
          ready: () => { },
        },
        typeset: typesetMock,
        options: {},
        loader: {},
      };

      const wrapper = mount(ProblemMarkdown, {
        propsData: { markdown: '$w$' },
      });

      await wrapper.vm.$nextTick();

      expect(typesetMock).toHaveBeenCalledWith([wrapper.vm.$refs.root]);
    });

    it('MathJax ready callback processes queued elements', () => {
      delete (window as any).MathJax;

      mount(ProblemMarkdown, {
        propsData: { markdown: '$q$' },
      });

      // Now MathJax config should exist with a ready callback
      expect(window.MathJax!.startup.ready).toBeDefined();

      // Simulate MathJax library finishing load: set typeset and defaultReady
      const typesetMock = jest.fn();
      const defaultReadyMock = jest.fn();
      window.MathJax!.typeset = typesetMock;
      window.MathJax!.startup.defaultReady = defaultReadyMock;

      // There should be a queued element
      expect(window.MathJax!.startup.elements!.length).toBeGreaterThan(0);

      // Call ready() — simulates MathJax library calling this internally
      window.MathJax!.startup.ready();

      expect(defaultReadyMock).toHaveBeenCalled();
      expect(typesetMock).toHaveBeenCalled();
      // Elements array should be emptied (spliced)
      expect(window.MathJax!.startup.elements!.length).toBe(0);
    });
  });

  // =========================================================================
  // Group 4: Mermaid Rendering
  // =========================================================================

  describe('Mermaid rendering', () => {
    it('calls renderMermaidDiagrams on the root element', async () => {
      const renderMermaidSpy = jest
        .spyOn(markdown.Converter.prototype, 'renderMermaidDiagrams')
        .mockImplementation(() => { });

      const wrapper = mount(ProblemMarkdown, {
        propsData: { markdown: '# Hello' },
      });

      await wrapper.vm.$nextTick();

      expect(renderMermaidSpy).toHaveBeenCalledWith(wrapper.vm.$refs.root);
    });

    it('catches synchronous errors from renderMermaidDiagrams', async () => {
      // Must mock console.error because test.setup.ts throws on it
      jest.spyOn(console, 'error').mockImplementation(() => { });

      jest
        .spyOn(markdown.Converter.prototype, 'renderMermaidDiagrams')
        .mockImplementation(() => {
          throw new Error('sync error');
        });

      // Should not throw — the error is caught internally
      const wrapper = mount(ProblemMarkdown, {
        propsData: { markdown: '# Hello' },
      });

      // Need to wait for the async renderMermaid to settle
      await wrapper.vm.$nextTick();
      await Vue.nextTick();

      expect(console.error).toHaveBeenCalledWith(
        'Error rendering Mermaid diagrams:',
        expect.any(Error),
      );
    });
  });

  // =========================================================================
  // Group 5: Template Injection
  // =========================================================================

  describe('Template injection', () => {
    it('ignores unknown template names without crashing', async () => {
      // The converter turns unrecognized templates into an alert span,
      // not into a <template> element. So injectTemplates() simply finds
      // nothing and skips. This should not throw.
      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: '{{unknown:template}}',
        },
      });

      await wrapper.vm.$nextTick();

      // Should not have any injected template components
      expect(wrapper.find('.libinteractive-download').exists()).toBe(false);
      expect(wrapper.find('.output-only-download').exists()).toBe(false);
    });

    it('stores mounted Vue instances in vueInstances array', async () => {
      // The real converter generates static HTML divs for templates,
      // not <template data-template-name> elements.
      // To exercise injectTemplates(), we mock makeHtml to return HTML
      // with <template> elements that injectTemplates() can process.
      jest
        .spyOn(markdown.Converter.prototype, 'makeHtml')
        .mockReturnValue(
          '<template data-template-name="output-only:download"></template>' +
          '<template data-template-name="libinteractive:download"></template>',
        );

      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: 'anything',
        },
      });

      await wrapper.vm.$nextTick();

      const vm = wrapper.vm as any;
      expect(vm.vueInstances).toBeDefined();
      expect(vm.vueInstances.length).toBe(2);
    });
  });

  // =========================================================================
  // Group 6: Event Emissions
  // =========================================================================

  describe('Event emissions', () => {
    it('emits "rendered" event on mount', async () => {
      const wrapper = mount(ProblemMarkdown, {
        propsData: { markdown: '# Hello' },
      });

      await wrapper.vm.$nextTick();

      expect(wrapper.emitted('rendered')).toBeTruthy();
      expect(wrapper.emitted('rendered')!.length).toBeGreaterThanOrEqual(1);
    });

    it('emits "rendered" event when markdown prop changes', async () => {
      const wrapper = mount(ProblemMarkdown, {
        propsData: { markdown: '# Hello' },
      });

      await wrapper.vm.$nextTick();

      // Clear previous emissions by noting current count
      const initialCount = wrapper.emitted('rendered')!.length;

      await wrapper.setProps({ markdown: '# Updated' });
      await wrapper.vm.$nextTick();

      expect(wrapper.emitted('rendered')!.length).toBeGreaterThan(
        initialCount,
      );
    });
  });

  // =========================================================================
  // Group 7: Lifecycle & Cleanup
  // =========================================================================

  describe('Lifecycle and cleanup', () => {
    it('mounted() calls renderMathJax, injectTemplates, and renderMermaid', async () => {
      const renderMermaidDiagramsSpy = jest
        .spyOn(markdown.Converter.prototype, 'renderMermaidDiagrams')
        .mockImplementation(() => { });

      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: '{{output-only:download}}',
        },
      });

      await wrapper.vm.$nextTick();

      // renderMathJax evidence: innerHTML is set and MathJax is initialized
      expect(window.MathJax).toBeDefined();

      // injectTemplates evidence: template component was mounted
      expect(wrapper.find('.output-only-download').exists()).toBe(true);

      // renderMermaid evidence: renderMermaidDiagrams was called
      expect(renderMermaidDiagramsSpy).toHaveBeenCalled();
    });

    it('beforeDestroy calls $destroy on all Vue instances', async () => {
      // Mock converter to produce template elements so injectTemplates() works
      jest
        .spyOn(markdown.Converter.prototype, 'makeHtml')
        .mockReturnValue(
          '<template data-template-name="output-only:download"></template>',
        );

      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: 'anything',
        },
      });

      await wrapper.vm.$nextTick();

      const vm = wrapper.vm as any;
      const instances = [...vm.vueInstances];
      expect(instances.length).toBe(1);

      const destroySpies = instances.map((instance: Vue) =>
        jest.spyOn(instance, '$destroy'),
      );

      wrapper.destroy();

      for (const spy of destroySpies) {
        expect(spy).toHaveBeenCalled();
      }
    });

    it('beforeDestroy removes DOM elements from parent', async () => {
      jest
        .spyOn(markdown.Converter.prototype, 'makeHtml')
        .mockReturnValue(
          '<template data-template-name="output-only:download"></template>',
        );

      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: 'anything',
        },
      });

      await wrapper.vm.$nextTick();

      const vm = wrapper.vm as any;
      const instanceEl = vm.vueInstances[0].$el;

      // The injected element should be attached to a parent before destroy
      expect(instanceEl.parentNode).not.toBeNull();

      wrapper.destroy();

      // After destroy, the element should be detached from its parent
      expect(instanceEl.parentNode).toBeNull();
    });

    it('beforeDestroy clears the vueInstances array', async () => {
      // Mock converter to produce template elements so injectTemplates() works
      jest
        .spyOn(markdown.Converter.prototype, 'makeHtml')
        .mockReturnValue(
          '<template data-template-name="output-only:download"></template>' +
          '<template data-template-name="libinteractive:download"></template>',
        );

      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: 'anything',
        },
      });

      await wrapper.vm.$nextTick();

      const vm = wrapper.vm as any;
      expect(vm.vueInstances.length).toBe(2);

      wrapper.destroy();

      expect(vm.vueInstances.length).toBe(0);
    });

    it('documents Vue instance leak: markdown change does not destroy stale instances (Bug #3)', async () => {
      // Mock converter to produce template element so injectTemplates() works
      const makeHtmlSpy = jest
        .spyOn(markdown.Converter.prototype, 'makeHtml')
        .mockReturnValue(
          '<template data-template-name="output-only:download"></template>',
        );

      const wrapper = mount(ProblemMarkdown, {
        propsData: {
          markdown: 'with-template',
        },
      });

      await wrapper.vm.$nextTick();

      const vm = wrapper.vm as any;
      expect(vm.vueInstances.length).toBe(1);

      const staleInstance = vm.vueInstances[0];
      const destroySpy = jest.spyOn(staleInstance, '$destroy');

      // Now change mock to return plain HTML (no templates)
      makeHtmlSpy.mockReturnValue('<p>Plain text only</p>');

      // Change markdown — renderMathJax replaces innerHTML, orphaning old
      // template DOM nodes, but does NOT $destroy the stored Vue instances
      await wrapper.setProps({ markdown: 'Plain text only' });
      await wrapper.vm.$nextTick();

      // Bug: $destroy was NOT called on the stale instance
      expect(destroySpy).not.toHaveBeenCalled();

      // Bug: vueInstances still holds the orphaned reference
      expect(vm.vueInstances.length).toBe(1);

      // The template is gone from DOM (innerHTML was replaced)
      expect(wrapper.find('.output-only-download').exists()).toBe(false);
    });
  });

  // =========================================================================
  // Group 8: Watcher Behavior
  // =========================================================================

  describe('Watcher behavior', () => {
    it('re-renders HTML content when markdown prop changes', async () => {
      const wrapper = mount(ProblemMarkdown, {
        propsData: { markdown: '# Original' },
      });

      await wrapper.vm.$nextTick();
      expect(wrapper.html()).toContain('Original');

      await wrapper.setProps({ markdown: '# Updated Content' });
      await wrapper.vm.$nextTick();

      expect(wrapper.html()).toContain('Updated Content');
      expect(wrapper.html()).not.toContain('Original');
    });

    it('calls renderMermaid when markdown prop changes', async () => {
      const renderMermaidSpy = jest
        .spyOn(markdown.Converter.prototype, 'renderMermaidDiagrams')
        .mockImplementation(() => { });

      const wrapper = mount(ProblemMarkdown, {
        propsData: { markdown: '# First' },
      });

      await wrapper.vm.$nextTick();

      // Reset the spy call count
      renderMermaidSpy.mockClear();

      await wrapper.setProps({ markdown: '# Second' });
      await wrapper.vm.$nextTick();

      expect(renderMermaidSpy).toHaveBeenCalledWith(wrapper.vm.$refs.root);
    });
  });
});
