import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';
import user_ProfileWrapper from './ProfileWrapper.vue';
import type { types } from '../../api_types';

describe('ProfileWrapper.vue', () => {
  const profile: types.UserProfileInfo = {
    name: 'omegaUp admin',
    classname: 'user-rank-unranked',
    email: 'admin@omegaup.com',
    username: 'omegaup',
    verified: true,
    hide_problem_tags: false,
    is_private: false,
    preferred_language: 'py2',
    programming_languages: { py2: 'python2' },
    rankinfo: {
      name: 'Test',
      problems_solved: 2,
      rank: 1,
    },
    is_own_profile: true,
    birth_date: new Date('1999-09-09'),
    locale: 'es',
    gender: 'decline',
  };

  it('Should render slots content correctly', () => {
    const wrapper = shallowMount(user_ProfileWrapper as ComponentOptions<Vue>, {
      propsData: {
        profile,
        data: null,
        selectedTab: 'basic-info',
        hasPassword: true,
      },
      slots: {
        message: '<div class="test-message">Alert Message</div>',
        title: '<h2 class="test-title">Settings Title</h2>',
        content: '<p class="test-body">Content Body</p>',
      },
    });

    expect(wrapper.find('.test-message').text()).toBe('Alert Message');
    expect(wrapper.find('.test-title').text()).toBe('Settings Title');
    expect(wrapper.find('.test-body').text()).toBe('Content Body');
  });

  it('Should emit update:selectedTab when currentSelectedTab changes', async () => {
    const wrapper = shallowMount(user_ProfileWrapper as ComponentOptions<Vue>, {
      propsData: {
        profile,
        data: null,
        selectedTab: 'basic-info',
        hasPassword: true,
      },
    });

    await wrapper.setData({ currentSelectedTab: 'edit-password' });
    expect(wrapper.emitted('update:selectedTab')).toBeDefined();
    expect(wrapper.emitted('update:selectedTab')?.[0]).toEqual([
      'edit-password',
    ]);
  });
});
