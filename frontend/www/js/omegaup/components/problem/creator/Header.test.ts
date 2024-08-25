import { shallowMount, createLocalVue, mount } from '@vue/test-utils';

import Header from './Header.vue';
import BootstrapVue, { IconsPlugin, BButton, BFormInput } from 'bootstrap-vue';
import store from '@/js/omegaup/problem/creator/store';
import T from '../../../lang';
import Vue from 'vue';
import {
  generateCase,
  generateGroup,
} from '@/js/omegaup/problem/creator/modules/cases';

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

  it('Should download the zip file', async () => {
    const wrapper = mount(Header, { localVue, store });

    const generateProblemSpy = jest.spyOn(wrapper.vm, 'generateProblem');

    const downloadButton = wrapper.find('button[data-download-zip]');
    expect(downloadButton.exists()).toBe(true);

    await downloadButton.trigger('click');
    await Vue.nextTick();
    expect(generateProblemSpy).toHaveBeenCalled();

    jest.restoreAllMocks();
  });

  it('Should correctly extract the inputs', async () => {
    const wrapper = mount(Header, { localVue, store });

    const newUngroupedCasegroup = generateGroup({
      name: 'New Ungrouped Case Group',
      ungroupedCase: true,
    });
    const newUngroupedCase = generateCase({
      name: 'New Ungrouped Case',
      groupID: newUngroupedCasegroup.groupID,
    });
    const newGroup = generateGroup({
      name: 'New Group',
      ungroupedCase: false,
    });
    const newCase = generateCase({
      name: 'New Case',
      groupID: newGroup.groupID,
    });
    const fileFolderList = [
      'statements/',
      'statements/es.markdown',
      'solutions/',
      'solutions/es.markdown',
      'cases/',
      'cases/New Ungrouped Case.in',
      'cases/New Ungrouped Case.out',
      'cases/New Group.New Case.in',
      'cases/New Group.New Case.out',
      'testplan',
      'cdp.data',
    ];

    store.commit('casesStore/resetStore');
    store.commit('casesStore/addGroup', newUngroupedCasegroup);
    store.commit('casesStore/addCase', newUngroupedCase);
    store.commit('casesStore/addGroup', newGroup);
    store.commit('casesStore/addCase', newCase);

    const downloadButton = wrapper.find('button[data-download-zip]');
    expect(downloadButton.exists()).toBe(true);

    await downloadButton.trigger('click');
    await Vue.nextTick();

    expect(Object.keys(wrapper.vm.zip.files)).toStrictEqual(fileFolderList);

    jest.restoreAllMocks();
  });
});
