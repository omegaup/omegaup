import { shallowMount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';

import Creator from './Creator.vue';
import Header from './Header.vue';
import Tabs from './Tabs.vue';
import T from '../../../lang';

import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);
localVue.use(Vuex);

describe('Creator.vue', () => {
  it('Should contain Header and Tabs Components', async () => {
    const store = new Vuex.Store({
      state: {
        problemName: T.problemCreatorNewProblem,
        problemMarkdown: T.problemCreatorEmpty,
        problemCodeContent: T.problemCreatorEmpty,
        problemCodeExtension: T.problemCreatorEmpty,
        problemSolutionMarkdown: T.problemCreatorEmpty,
      },
    });

    const wrapper = shallowMount(Creator, {
      localVue,
      store,
      mocks: {
        $cookies: {
          get: () => true,
        },
      },
    });

    expect(wrapper.findComponent(Header).exists()).toBe(true);
    expect(wrapper.findComponent(Tabs).exists()).toBe(true);
  });
});
