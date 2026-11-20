import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import user_EmailEdit from './EmailEdit.vue';

const EmailEdit = user_EmailEdit;

const profile: types.UserProfileInfo = {
  country: 'Mexico',
  country_id: 'MX',
  classname: 'user-rank-master',
  username: 'omegaup',
  hide_problem_tags: false,
  is_private: false,
  preferred_language: 'py3',
  programming_languages: {
    py3: 'Python 3',
  },
  rankinfo: {
    name: 'Test',
    problems_solved: 2,
    rank: 0,
  },
  is_own_profile: true,
  gravatar_92:
    'https://secure.gravatar.com/avatar/307aeed2f8a75f6fe407411671e3ca87?s=51',
};

describe('EmailEdit.vue', () => {
  it('Should render the initial email with proper input attributes and label binding', () => {
    const wrapper = shallowMount(EmailEdit, {
      propsData: {
        email: 'coder@omegaup.com',
        profile,
      },
    });

    const label = wrapper.find('label[for="email"]');
    const input = wrapper.find('input[data-email-input]');
    const submitButton = wrapper.find('button[data-submit-email]');

    expect(label.exists()).toBe(true);
    expect(input.attributes('id')).toBe('email');
    expect(input.attributes('type')).toBe('email');
    expect(input.attributes('required')).toBe('required');
    expect((input.element as HTMLInputElement).value).toBe('coder@omegaup.com');
    expect(submitButton.attributes('disabled')).toBe('disabled');
  });

  it('Should disable submit button and prevent emission when email is empty or unchanged', async () => {
    const wrapper = shallowMount(EmailEdit, {
      propsData: {
        email: 'coder@omegaup.com',
        profile,
      },
    });

    const input = wrapper.find('input[data-email-input]');
    const submitButton = wrapper.find('button[data-submit-email]');

    await wrapper.find('form').trigger('submit');
    expect(wrapper.emitted('submit')).toBeUndefined();

    await input.setValue('   ');
    expect(submitButton.attributes('disabled')).toBe('disabled');

    await wrapper.find('form').trigger('submit');
    expect(wrapper.emitted('submit')).toBeUndefined();
  });

  it('Should emit trimmed email when a modified valid email is submitted', async () => {
    const wrapper = shallowMount(EmailEdit, {
      propsData: {
        email: 'coder@omegaup.com',
        profile,
      },
    });

    const input = wrapper.find('input[data-email-input]');
    const submitButton = wrapper.find('button[data-submit-email]');

    await input.setValue('  new_coder@omegaup.com  ');
    expect(submitButton.attributes('disabled')).toBeUndefined();

    await wrapper.find('form').trigger('submit');
    expect(wrapper.emitted('submit')).toEqual([['new_coder@omegaup.com']]);
  });

  it('Should update currentEmail reactively when the email prop changes', async () => {
    const wrapper = shallowMount(EmailEdit, {
      propsData: {
        email: 'coder@omegaup.com',
        profile,
      },
    });

    await wrapper.setProps({ email: 'updated@omegaup.com' });
    const input = wrapper.find('input[data-email-input]');
    expect((input.element as HTMLInputElement).value).toBe(
      'updated@omegaup.com',
    );
  });

  it('Should allow submission when starting with empty email and entering a valid email', async () => {
    const wrapper = shallowMount(EmailEdit, {
      propsData: {
        email: '',
        profile,
      },
    });

    const input = wrapper.find('input[data-email-input]');
    const submitButton = wrapper.find('button[data-submit-email]');
    expect(submitButton.attributes('disabled')).toBe('disabled');

    await input.setValue('first_email@omegaup.com');
    expect(submitButton.attributes('disabled')).toBeUndefined();

    await wrapper.find('form').trigger('submit');
    expect(wrapper.emitted('submit')).toEqual([['first_email@omegaup.com']]);
  });
});
