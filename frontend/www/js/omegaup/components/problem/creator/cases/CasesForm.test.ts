import { createLocalVue, shallowMount, mount } from '@vue/test-utils';

import CasesForm from './CasesForm.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import store from '@/js/omegaup/problem/creator/store';
import Vue from 'vue';
import T from '../../../../lang';
import {
  generateCase,
  generateGroup,
} from '@/js/omegaup/problem/creator/modules/cases';
import * as ui from '@/js/omegaup/ui';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('CasesForm.vue', () => {
  beforeEach(() => {
    store.commit('casesStore/resetStore');
  });

  it('Should render commit message input and hidden fields', async () => {
    const wrapper = shallowMount(CasesForm, {
      localVue,
      store: store,
      provide: { problemAlias: 'problem-alias' },
      propsData: { isCaseEdit: true },
    });

    await Vue.nextTick();

    const commitInput = wrapper.find('input.form-control');
    expect(commitInput.exists()).toBeTruthy();
    expect((commitInput.element as HTMLInputElement).value).toBe(
      T.problemEditUpdatingCase,
    );

    const requestHidden = wrapper.find('input[type="hidden"][name="request"]');
    expect(requestHidden.exists()).toBeTruthy();
    expect((requestHidden.element as HTMLInputElement).value).toBe('cases');

    const aliasHidden = wrapper.find(
      'input[type="hidden"][name="problem_alias"]',
    );
    expect(aliasHidden.exists()).toBeTruthy();
    expect((aliasHidden.element as HTMLInputElement).value).toBe(
      'problem-alias',
    );

    const messageHidden = wrapper.find('input[type="hidden"][name="message"]');
    expect(messageHidden.exists()).toBeTruthy();
    expect((messageHidden.element as HTMLInputElement).value).toBe(
      T.problemEditUpdatingCase,
    );
  });

  it('Should show input and output file fields when truncated', async () => {
    const wrapper = shallowMount(CasesForm, {
      localVue,
      store: store,
      provide: { problemAlias: 'alias' },
      propsData: { isTruncatedInput: true, isTruncatedOutput: true },
    });

    await Vue.nextTick();

    const inputFile = wrapper.find('input[type="file"][name="input_file"]');
    const outputFile = wrapper.find('input[type="file"][name="output_file"]');
    expect(inputFile.exists()).toBeTruthy();
    expect(outputFile.exists()).toBeTruthy();
  });

  it('Should compute contentsPayload for case edit', async () => {
    const group = generateGroup({ name: 'group', ungroupedCase: false });
    const caze = generateCase({ name: 'case', groupID: group.groupID });

    store.commit('casesStore/addGroup', group);
    store.commit('casesStore/addCase', caze);
    store.commit('casesStore/setSelected', {
      groupID: group.groupID,
      caseID: caze.caseID,
    });

    const wrapper = shallowMount(CasesForm, {
      localVue,
      store,
      provide: { problemAlias: 'alias' },
      propsData: { isCaseEdit: true },
    });
    await Vue.nextTick();

    const contentsHidden = wrapper.find(
      'input[type="hidden"][name="contents"]',
    );
    const parsed = JSON.parse(
      (contentsHidden.element as HTMLInputElement).value,
    );

    expect(parsed.group.groupID).toBe(group.groupID);
    expect(parsed.case.caseID).toBe(caze.caseID);
  });

  it('Should compute contentsPayload for group edit', async () => {
    const editGroup = generateGroup({
      name: 'editGroup',
      ungroupedCase: false,
    });

    const wrapper = shallowMount(CasesForm, {
      localVue,
      store,
      provide: { problemAlias: 'alias' },
      propsData: { isCaseEdit: false, editGroup: editGroup },
    });
    await Vue.nextTick();

    const contentsHidden = wrapper.find(
      'input[type="hidden"][name="contents"]',
    );
    const parsed = JSON.parse(
      (contentsHidden.element as HTMLInputElement).value,
    );

    expect(parsed.group.groupID).toBe(editGroup.groupID);

    expect(parsed.case).toBeUndefined();
  });

  it('Should disable button, show error and prevent submission when commitMessage empty', async () => {
    const errorSpy = jest.spyOn(ui, 'error').mockImplementation(() => {});

    const wrapper = mount(CasesForm, {
      localVue,
      store: store,
      provide: { problemAlias: 'alias' },
      propsData: { isCaseEdit: false },
    });

    wrapper.setData({ commitMessage: '     ' });
    await Vue.nextTick();

    const submitBtn = wrapper.find('button[type="submit"]');
    expect(submitBtn.attributes('disabled')).toBe('disabled');

    const form = wrapper.find('form');
    await form.trigger('submit');

    expect(errorSpy).toHaveBeenCalledWith(T.editFieldRequired);

    errorSpy.mockRestore();
  });

  it('Should hide submit button when isEmbedded is true', async () => {
    const wrapper = mount(CasesForm, {
      localVue,
      store: store,
      provide: { problemAlias: 'alias' },
      propsData: { isEmbedded: true },
    });

    const submitBtn = wrapper.find('button[type="submit"]');
    expect(submitBtn.element).not.toBeVisible();
  });

  it('Should initialize commitMessage for Case edit', () => {
    const wrapper = shallowMount(CasesForm, {
      localVue,
      store,
      provide: { problemAlias: 'alias' },
      propsData: { isCaseEdit: true },
    });
    expect((wrapper.vm as any).commitMessage).toBe(T.problemEditUpdatingCase);
  });

  it('Should initialize commitMessage for Group edit', () => {
    const wrapper = shallowMount(CasesForm, {
      localVue,
      store,
      provide: { problemAlias: 'alias' },
      propsData: { isCaseEdit: false },
    });
    expect((wrapper.vm as any).commitMessage).toBe(T.problemEditUpdatingGroup);
  });

  it('Should render submit button with correct text', async () => {
    const wrapper = shallowMount(CasesForm, {
      localVue,
      store,
      provide: { problemAlias: 'alias' },
      propsData: { isCaseEdit: true },
    });

    const submitBtn = wrapper.find('button[type="submit"]');
    expect(submitBtn.exists()).toBeTruthy();
    expect(submitBtn.text()).toBe(T.problemEditSaveCase);
  });
});
