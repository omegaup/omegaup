import { mount } from '@vue/test-utils';
import MarkdownEditor from './MarkdownEditor.vue';

describe('MarkdownEditor.vue', () => {
  it('renders toolbar without image button', () => {
    const wrapper = mount(MarkdownEditor, {
      propsData: { value: '', idPostfix: '-test' },
      attachTo: document.body,
    });
    expect(wrapper.find('.wmd-button-bar').exists()).toBe(true);
    expect(wrapper.find('[id^="wmd-bold-button"]').exists()).toBe(true);
    expect(wrapper.find('[id^="wmd-image-button"]').exists()).toBe(false);
    wrapper.destroy();
  });

  it('emits input when typing', async () => {
    const wrapper = mount(MarkdownEditor, {
      propsData: { value: '', idPostfix: '-test' },
      attachTo: document.body,
    });
    const textarea = wrapper.find('textarea');
    (textarea.element as HTMLTextAreaElement).value = '**hi**';
    await textarea.trigger('input');
    expect(wrapper.emitted('input')).toEqual([['**hi**']]);
    wrapper.destroy();
  });

  it('emits input after a toolbar action', async () => {
    const wrapper = mount(MarkdownEditor, {
      propsData: { value: 'text', idPostfix: '-test' },
      attachTo: document.body,
    });
    const textarea = wrapper.find('textarea').element as HTMLTextAreaElement;
    textarea.value = 'text **bold**';
    await wrapper.find('.wmd-button-bar').trigger('click');
    await wrapper.vm.$nextTick();
    expect(wrapper.emitted('input')).toBeTruthy();
    wrapper.destroy();
  });

  it('toggles between edit and preview', async () => {
    const wrapper = mount(MarkdownEditor, {
      propsData: { value: '# Hi', idPostfix: '-test' },
      attachTo: document.body,
    });
    await wrapper.find('[data-markdown-editor-preview]').trigger('click');
    expect(wrapper.find('.wmd-preview').exists()).toBe(true);
    await wrapper.find('[data-markdown-editor-edit]').trigger('click');
    expect(wrapper.find('.wmd-preview').exists()).toBe(false);
    wrapper.destroy();
  });

  it('is read-only without toolbar or toggle', () => {
    const wrapper = mount(MarkdownEditor, {
      propsData: { value: 'x', idPostfix: '-test', readOnly: true },
    });
    expect(wrapper.find('textarea').attributes('readonly')).toBeDefined();
    expect(wrapper.find('[id^="wmd-bold-button"]').exists()).toBe(false);
    expect(wrapper.find('[data-markdown-editor-preview]').exists()).toBe(false);
  });

  it('blocks the Ctrl+G image shortcut', () => {
    const wrapper = mount(MarkdownEditor, {
      propsData: { value: '', idPostfix: '-test' },
      attachTo: document.body,
    });
    const event = new KeyboardEvent('keydown', {
      key: 'g',
      ctrlKey: true,
      bubbles: true,
      cancelable: true,
    });
    wrapper.find('textarea').element.dispatchEvent(event);
    expect(event.defaultPrevented).toBe(true);
    wrapper.destroy();
  });

  it('passes extra attributes to the textarea', () => {
    const wrapper = mount(MarkdownEditor, {
      propsData: { value: '', idPostfix: '-test' },
      attrs: { 'data-course-objective': '' },
      attachTo: document.body,
    });
    expect(wrapper.find('textarea[data-course-objective]').exists()).toBe(true);
    wrapper.destroy();
  });
});
