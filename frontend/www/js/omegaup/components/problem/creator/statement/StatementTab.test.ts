import { createLocalVue, shallowMount } from '@vue/test-utils';

import StatementTab from './StatementTab.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import store from '@/js/omegaup/problem/creator/store';
import T from '../../../../lang';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('StatementTab.vue', () => {
  it('Should contain markdown buttons and contents and update the store accordingly', async () => {
    const wrapper = shallowMount(StatementTab, {
      localVue,
      store,
    });

    const markdownButtons = wrapper.find('div.wmd-button-bar');
    expect(markdownButtons.exists()).toBe(true);

    const textArea = wrapper.find('textarea.wmd-input');
    expect(textArea.exists()).toBe(true);
    textArea.setValue('Hello omegaUp');

    expect(wrapper.vm.currentMarkdown).toBe('Hello omegaUp');

    const markdownContent = wrapper.find('omegaup-markdown-stub');
    expect(markdownContent.exists()).toBe(true);

    await wrapper.trigger('click');

    expect(markdownContent.props()['markdown']).toBe(
      T.problemCreatorMarkdownPreviewInitialRender + 'Hello omegaUp',
    );

    expect(wrapper.vm.$store.state.problemMarkdown).toBe('');

    const markdownSaveButton = wrapper.find('button.btn-primary');
    expect(markdownSaveButton.exists()).toBe(true);
    await markdownSaveButton.trigger('click');

    expect(wrapper.vm.$store.state.problemMarkdown).toBe('Hello omegaUp');
  });
});
