import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import user_CodeTemplates_Edit from './CodeTemplatesEdit.vue';
import { sourceTemplates } from '../../grader/GraderTemplates';

const profile: types.UserProfileInfo = {
  name: 'omegaUp admin',
  classname: 'user-rank-unranked',
  email: 'admin@omegaup.com',
  username: 'omegaup',
  verified: true,
  hide_problem_tags: false,
  is_private: false,
  preferred_language: 'py2',
  programming_languages: {
    py2: 'python2',
    rb: 'ruby',
  },
  rankinfo: {
    name: 'Test',
    problems_solved: 2,
    rank: 1,
  },
  is_own_profile: true,
  birth_date: new Date('1999-09-09'),
  locale: 'es',
  gender: 'decline',
  has_competitive_objective: true,
  has_learning_objective: true,
  has_scholar_objective: false,
  has_teaching_objective: false,
};

describe('CodeTemplatesEdit.vue', () => {
  afterEach(() => {
    localStorage.clear();
  });

  it('Should preselect the preferred language and show the system boilerplate', () => {
    const wrapper = shallowMount(user_CodeTemplates_Edit, {
      propsData: { profile },
    });

    const vm = wrapper.vm as any;
    expect(vm.selectedLanguage).toBe('py2');
    expect(
      (wrapper.find('textarea[data-code-templates-source]')
        .element as HTMLTextAreaElement).value,
    ).toBe(sourceTemplates.py);
  });

  it('Should save a custom template for the selected language', async () => {
    const wrapper = shallowMount(user_CodeTemplates_Edit, {
      propsData: { profile },
    });

    await wrapper
      .find('textarea[data-code-templates-source]')
      .setValue('print("mine")');
    await wrapper.find('button[data-code-templates-save]').trigger('click');

    expect(localStorage.getItem('codeTemplates:omegaup:py')).toBe(
      'print("mine")',
    );
  });

  it('Should load a previously saved template', () => {
    localStorage.setItem('codeTemplates:omegaup:py', 'print("mine")');

    const wrapper = shallowMount(user_CodeTemplates_Edit, {
      propsData: { profile },
    });

    expect(
      (wrapper.find('textarea[data-code-templates-source]')
        .element as HTMLTextAreaElement).value,
    ).toBe('print("mine")');
  });

  it('Should load the template of the newly selected language', async () => {
    localStorage.setItem('codeTemplates:omegaup:rb', 'puts "mine"');

    const wrapper = shallowMount(user_CodeTemplates_Edit, {
      propsData: { profile },
    });

    await wrapper
      .find('select[data-code-templates-language]')
      .find('option[value="rb"]')
      .setSelected();

    expect(
      (wrapper.find('textarea[data-code-templates-source]')
        .element as HTMLTextAreaElement).value,
    ).toBe('puts "mine"');
  });

  it('Should restore the system boilerplate and remove the custom template', async () => {
    localStorage.setItem('codeTemplates:omegaup:py', 'print("mine")');

    const wrapper = shallowMount(user_CodeTemplates_Edit, {
      propsData: { profile },
    });

    await wrapper.find('button[data-code-templates-restore]').trigger('click');

    expect(localStorage.getItem('codeTemplates:omegaup:py')).toBeNull();
    expect(
      (wrapper.find('textarea[data-code-templates-source]')
        .element as HTMLTextAreaElement).value,
    ).toBe(sourceTemplates.py);
  });
});
