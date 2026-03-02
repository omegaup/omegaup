import { mount } from '@vue/test-utils';
import ProblemMarkdown from './ProblemMarkdown.vue';

describe('problem/ProblemMarkdown.vue', () => {
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
});
