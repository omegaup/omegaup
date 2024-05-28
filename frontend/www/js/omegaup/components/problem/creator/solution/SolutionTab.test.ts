import { createLocalVue, shallowMount } from '@vue/test-utils';

import SolutionTab from './SolutionTab.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import store from '@/js/omegaup/problem/creator/store';

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

    const markdownSaveButton = wrapper.find('button');
    expect(markdownSaveButton.exists()).toBe(true);
    await markdownSaveButton.trigger('click');

    expect(wrapper.vm.$store.state.problemSolutionMarkdown).toBe(
      'Hello omegaUp',
    );

    const markdownContent = wrapper.find('omegaup-markdown-stub');
    expect(markdownContent.exists()).toBe(true);
    expect(markdownContent.props()['markdown']).toBe('Hello omegaUp');
  });
});
