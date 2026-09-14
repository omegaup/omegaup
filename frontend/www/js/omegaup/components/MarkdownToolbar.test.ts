import { mount, createLocalVue } from '@vue/test-utils';
import Vue from 'vue';
import MarkdownToolbar from './MarkdownToolbar.vue';

const localVue = createLocalVue();

const mountEditor = async (props: { hideImage?: boolean } = {}) => {
  const Parent = Vue.extend({
    components: { MarkdownToolbar },
    props: {
      hideImage: { type: Boolean, default: false },
    },
    data() {
      return {
        text: 'Live Preview Test ',
      };
    },
    methods: {
      getTextarea(): HTMLTextAreaElement | null {
        return (this.$refs.textarea as HTMLTextAreaElement) || null;
      },
    },
    template: `
      <div>
        <markdown-toolbar
          :get-textarea="getTextarea"
          :hide-image="hideImage"
          @input="text = $event"
        />
        <textarea ref="textarea" v-model="text" />
      </div>
    `,
  });
  const wrapper = mount(Parent, {
    localVue,
    propsData: props,
  });
  await Vue.nextTick();
  return wrapper;
};

describe('MarkdownToolbar.vue', () => {
  it('renders required toolbar controls', async () => {
    const wrapper = await mountEditor();

    expect(wrapper.find('[data-markdown-toolbar]').exists()).toBe(true);
    expect(wrapper.find('[data-markdown-toolbar-bold]').exists()).toBe(true);
    expect(wrapper.find('[data-markdown-toolbar-image]').exists()).toBe(true);
  });

  it('hides the image button when hideImage is set', async () => {
    const wrapper = await mountEditor({ hideImage: true });

    expect(wrapper.find('[data-markdown-toolbar-image]').exists()).toBe(false);
  });

  it('inserts a strong-text placeholder when bold is clicked with no selection', async () => {
    const wrapper = await mountEditor();
    const textarea = wrapper.find('textarea').element as HTMLTextAreaElement;
    textarea.setSelectionRange(textarea.value.length, textarea.value.length);

    await wrapper.find('[data-markdown-toolbar-bold]').trigger('click');

    expect((wrapper.vm as any).text).toBe(
      'Live Preview Test **strong text**',
    );
  });

  it('wraps the current selection in bold markers', async () => {
    const wrapper = await mountEditor();
    const textarea = wrapper.find('textarea').element as HTMLTextAreaElement;
    const start = textarea.value.indexOf('Preview');
    textarea.setSelectionRange(start, start + 'Preview'.length);

    await wrapper.find('[data-markdown-toolbar-bold]').trigger('click');

    expect((wrapper.vm as any).text).toBe('Live **Preview** Test ');
  });
});
