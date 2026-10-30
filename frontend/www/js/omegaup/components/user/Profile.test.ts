import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import user_Profile from './Profile.vue';

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

const stubs = {
  'omegaup-user-profile-wrapper': {
    template:
      '<div><slot name="message"/><slot name="title"/><slot name="content"/></div>',
  },
};

describe('Profile.vue', () => {
  it('Should display correct title for own profile', () => {
    const wrapper = shallowMount(
      (user_Profile as unknown) as ComponentOptions<Vue>,
      {
        propsData: {
          profile,
          selectedTab: 'view-profile',
        },
        stubs,
      },
    );

    expect(wrapper.find('h3').text()).toBe(T.userEditViewProfile);
    expect((wrapper.vm as any).currentTitle).toBe(T.userEditViewProfile);
  });

  it('Should display default title for other user profile', () => {
    const wrapper = shallowMount(
      (user_Profile as unknown) as ComponentOptions<Vue>,
      {
        propsData: {
          profile: { ...profile, is_own_profile: false },
          selectedTab: 'view-profile',
        },
        stubs,
      },
    );

    expect(wrapper.find('h3').text()).toBe(T.omegaupTitleProfile);
    expect((wrapper.vm as any).currentTitle).toBe(T.omegaupTitleProfile);
  });

  it('Should display private message for private other user profile', () => {
    const infoSpy = jest.spyOn(ui, 'info').mockImplementation(() => {});
    shallowMount((user_Profile as unknown) as ComponentOptions<Vue>, {
      propsData: {
        profile: { ...profile, is_own_profile: false, is_private: true },
      },
      stubs,
    });

    expect(infoSpy).toHaveBeenCalledWith(T.userProfileIsPrivate);
    infoSpy.mockRestore();
  });

  it('Should update currentSelectedTab and title when selectedTab prop changes', async () => {
    const wrapper = shallowMount(
      (user_Profile as unknown) as ComponentOptions<Vue>,
      {
        propsData: {
          profile,
          selectedTab: 'view-profile',
        },
        stubs,
      },
    );

    await wrapper.setProps({ selectedTab: 'edit-preferences' });
    expect((wrapper.vm as any).currentSelectedTab).toBe('edit-preferences');
    expect(wrapper.find('h3').text()).toBe(T.userEditPreferences);
  });
});
