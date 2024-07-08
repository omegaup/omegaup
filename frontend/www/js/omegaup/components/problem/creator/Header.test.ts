import { shallowMount, createLocalVue, mount} from '@vue/test-utils';

import Header from './Header.vue';
import BootstrapVue, { IconsPlugin, BButton, BFormInput } from 'bootstrap-vue';
import store from '@/js/omegaup/problem/creator/store';
import T from '../../../lang';
import Vue from 'vue';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('Header.vue', () => {
  it('Should contain the header buttons and problem name input', async () => {
    const wrapper = shallowMount(Header, { localVue, store });

    const buttons = wrapper.findAllComponents(BButton);
    const expectedText = [
      T.problemCreatorLoadProblem,
      T.problemCreatorGenerateProblem,
      T.problemCreatorNewProblem,
    ];

    expect(expectedText.length).toBe(buttons.length);
    for (let i = 0; i < expectedText.length; i++) {
      expect(buttons.at(i).text()).toBe(expectedText[i]);
    }

    expect(wrapper.findComponent(BFormInput).exists()).toBe(true);
  });

  it('Should download the zip file', async () => {
    const wrapper = mount(Header, { localVue, store });

    const generateProblemSpy = jest.spyOn(wrapper.vm, 'generateProblem');

    const downloadButton = wrapper.findAll('button').at(1);
    expect(downloadButton.exists()).toBe(true);

    await downloadButton.trigger('click');
    await Vue.nextTick();
    expect(generateProblemSpy).toHaveBeenCalled();

    jest.restoreAllMocks();

  })
});
