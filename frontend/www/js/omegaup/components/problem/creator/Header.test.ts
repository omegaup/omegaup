import { shallowMount, createLocalVue } from '@vue/test-utils';

import Header from './Header.vue';
import BootstrapVue, { IconsPlugin, BButton, BFormInput } from 'bootstrap-vue';
import T from '../../../lang';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('Header.vue', () => {
  it('Should contain the header buttons and problem name input', async () => {
    const wrapper = shallowMount(Header, { localVue });

    const buttons = wrapper.findAllComponents(BButton);
    const expectedText = [
      T.problemCreatorLoadProblem,
      T.problemCreatorGenerateProblem,
      T.problemCreatorNewProblem,
    ];

    for (let i = 0; i < buttons.length; i++) {
      expect(buttons.at(i).text()).toBe(expectedText[i]);
    }

    expect(wrapper.findComponent(BFormInput).exists()).toBe(true);
  });
});
