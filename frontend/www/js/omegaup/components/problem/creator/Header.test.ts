import { shallowMount, createLocalVue, mount } from '@vue/test-utils';

import Header from './Header.vue';
import BootstrapVue, { IconsPlugin, BButton, BFormInput } from 'bootstrap-vue';
import store from '@/js/omegaup/problem/creator/store';
import T from '../../../lang';

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

  it('Should reset the store on clicking the reset button', async () => {
    const original = window.location;

    Object.defineProperty(window, 'location', {
      configurable: true,
      value: { reload: jest.fn() },
    });

    const wrapper = mount(Header, { localVue, store });

    const buttonsList = wrapper.findAll('button');
    expect(buttonsList.length).toBe(3);

    const resetButton = buttonsList.at(2);
    expect(resetButton.exists()).toBe(true);

    const testText = 'Hello';
    const emptyText = '';
    wrapper.vm.$store.state.problemName = testText;
    wrapper.vm.$store.state.problemMarkdown = testText;
    wrapper.vm.$store.state.problemCodeContent = testText;
    wrapper.vm.$store.state.problemCodeExtension = testText;
    wrapper.vm.$store.state.problemSolutionMarkdown = testText;

    await resetButton.trigger('click');

    expect(window.location.reload).toHaveBeenCalledTimes(1);

    expect(wrapper.vm.$store.state.problemName).toBe(
      T.problemCreatorNewProblem,
    );
    expect(wrapper.vm.$store.state.problemMarkdown).toBe(emptyText);
    expect(wrapper.vm.$store.state.problemCodeContent).toBe(emptyText);
    expect(wrapper.vm.$store.state.problemCodeExtension).toBe(emptyText);
    expect(wrapper.vm.$store.state.problemSolutionMarkdown).toBe(emptyText);

    jest.restoreAllMocks();
    Object.defineProperty(window, 'location', {
      configurable: true,
      value: original,
    });
  });
});
