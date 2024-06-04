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

    wrapper.vm.currentMarkdown = 'Hello omegaUp';

    const markdownPreviewButton = wrapper.find('button.btn-success');
    const markdownSaveButton = wrapper.find('button.btn-primary');

    expect(markdownPreviewButton.exists()).toBe(true);
    await markdownPreviewButton.trigger('click');

    const markdownContent = wrapper.find('omegaup-markdown-stub');
    expect(markdownContent.exists()).toBe(true);
    expect(markdownContent.props()['markdown']).toBe(
      T.problemCreatorMarkdownPreviewInitialRender + 'Hello omegaUp',
    );

    expect(wrapper.vm.$store.state.problemMarkdown).toBe('');

    wrapper.vm.currentMarkdown = 'Hello omegaUp creator store';

    expect(markdownSaveButton.exists()).toBe(true);
    await markdownSaveButton.trigger('click');

    expect(markdownContent.props()['markdown']).toBe(
      T.problemCreatorMarkdownPreviewInitialRender +
        'Hello omegaUp creator store',
    );
    expect(wrapper.vm.$store.state.problemMarkdown).toBe(
      'Hello omegaUp creator store',
    );
  });
});
