import { createLocalVue, shallowMount } from '@vue/test-utils';

import SolutionTab from './SolutionTab.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import store from '@/js/omegaup/problem/creator/store';
import T from '../../../../lang';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('SolutionTab.vue', () => {
  it('Should contain markdown buttons and contents and update the store accordingly', async () => {
    const wrapper = shallowMount(SolutionTab, {
      localVue,
      store,
    });

    const markdownButtons = wrapper.find('div.wmd-button-bar');
    expect(markdownButtons.exists()).toBe(true);

    wrapper.vm.currentSolutionMarkdown = 'Hello omegaUp';

    const markdownPreviewButton = wrapper.find('button.btn-success');
    const markdownSaveButton = wrapper.find('button.btn-primary');

    expect(markdownPreviewButton.exists()).toBe(true);
    await markdownPreviewButton.trigger('click');

    const markdownContent = wrapper.find('omegaup-markdown-stub');
    expect(markdownContent.exists()).toBe(true);
    expect(markdownContent.props()['markdown']).toBe(
      T.problemCreatorMarkdownPreviewInitialRender + 'Hello omegaUp',
    );

    expect(wrapper.vm.$store.state.problemSolutionMarkdown).toBe('');

    wrapper.vm.currentSolutionMarkdown = 'Hello omegaUp creator store';

    expect(markdownSaveButton.exists()).toBe(true);
    await markdownSaveButton.trigger('click');

    expect(markdownContent.props()['markdown']).toBe(
      T.problemCreatorMarkdownPreviewInitialRender +
        'Hello omegaUp creator store',
    );

    expect(wrapper.vm.$store.state.problemSolutionMarkdown).toBe(
      'Hello omegaUp creator store',
    );
  });
});
