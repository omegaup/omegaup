import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import problem_Edit from './Edit.vue';

describe('Edit.vue', () => {
  it('Should handle an existing problem', async () => {
    const wrapper = shallowMount(problem_Edit, {
      propsData: {
        data: {
          title: 'problem',
          alias: 'problem-alias',
          timeLimit: 1000,
          extraWallTime: 0,
          memoryLimit: 32768,
          outputLimit: 10240,
          inputLimit: 10240,
          overallWallTimeLimit: 1000,
          validatorTimeLimit: 0,
          emailClarifications: 1,
          visibility: 2,
          allowUserAddTags: false,
          source: 'omegaUp classic',
          validator: 'token-numeric',
          languages: 'py2,py3',
          selectedTags: [],
          statement: {
            language: 'es',
          },
          solution: {
            language: 'es',
          },
          problemsetter: {
            username: 'username',
            name: 'name',
          },
        },
        initialLanguage: 'es',
        initialTab: 'edit',
      },
    });

    expect(wrapper.text()).toContain(T.problemEditEditProblem);

    // All the links are available
    await wrapper.find('a[data-tab-markdown]').trigger('click');
    await wrapper.find('a[data-tab-version]').trigger('click');
    await wrapper.find('a[data-tab-solution]').trigger('click');
    await wrapper.find('a[data-tab-admins]').trigger('click');
    await wrapper.find('a[data-tab-tags]').trigger('click');
    await wrapper.find('a[data-tab-edit]').trigger('click');
    await wrapper.find('a[data-tab-download]').trigger('click');
    expect(wrapper.find('.card-body .form .form-group button').text()).toBe(
      T.wordsDownload,
    );
    await wrapper.find('a[data-tab-delete]').trigger('click');
    expect(wrapper.find('.alert-heading').text()).toBe(T.wordsDangerZone);

    const deleteModal = wrapper.find('b-modal-stub');
    deleteModal.vm.$emit('ok');
    expect(wrapper.emitted('remove')).toBeDefined();
    expect(wrapper.emitted('remove')).toEqual([['problem-alias']]);
  });
});
