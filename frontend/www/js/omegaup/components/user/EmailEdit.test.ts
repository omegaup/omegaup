import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type { types } from '../../api_types';
import user_EmailEdit from './EmailEdit.vue';

const profile: types.UserProfileInfo = {
  country: 'Mexico',
  country_id: 'MX',
  classname: 'user-rank-master',
  username: 'omegaup',
  hide_problem_tags: false,
  is_private: false,
  preferred_language: 'py2',
  programming_languages: {
    py2: 'python2',
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
  it('Should display initial email in input field', () => {
    const wrapper = shallowMount(
      (user_EmailEdit as unknown) as ComponentOptions<Vue>,
      {
        propsData: {
          email: 'initial@omegaup.com',
          profile,
        },
      },
    );

    const input = wrapper.find('input[name="email"]');
    expect(input.exists()).toBe(true);
    expect((input.element as HTMLInputElement).value).toBe(
      'initial@omegaup.com',
    );
  });

  it('Should emit submit with updated email on form submission', async () => {
    const wrapper = shallowMount(
      (user_EmailEdit as unknown) as ComponentOptions<Vue>,
      {
        propsData: {
          email: 'old@omegaup.com',
          profile,
        },
      },
    );

    const input = wrapper.find('input[name="email"]');
    await input.setValue('new@omegaup.com');
    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.emitted('submit')).toBeTruthy();
    expect(wrapper.emitted('submit')?.[0]).toEqual(['new@omegaup.com']);
  });

  it('Should update currentEmail when email prop changes', async () => {
    const wrapper = shallowMount(
      (user_EmailEdit as unknown) as ComponentOptions<Vue>,
      {
        propsData: {
          email: 'first@omegaup.com',
          profile,
        },
      },
    );

    await wrapper.setProps({ email: 'second@omegaup.com' });
    expect((wrapper.vm as any).currentEmail).toBe('second@omegaup.com');
  });
});
