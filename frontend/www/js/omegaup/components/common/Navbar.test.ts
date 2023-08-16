import { mount, shallowMount } from '@vue/test-utils';

import T from '../../lang';

import common_Navbar from './Navbar.vue';
import UserObjectivesQuestions from '../user/ObjectivesQuestions.vue';
import user_NextRegisteredContest from '../user/NextRegisteredContest.vue';

describe('Navbar.vue', () => {
  const propsData = {
    currentUsername: 'user',
    errorMessage: null,
    graderInfo: null,
    graderQueueLength: -1,
    gravatarURL51:
      'https://secure.gravatar.com/avatar/568c0ec2147500d7cd09cc8bbc8e5ec4?s=51',
    inContest: true,
    clarifications: [],
    isAdmin: false,
    isLoggedIn: true,
    isMainUserIdentity: false,
    isReviewer: false,
    lockDownImage: 'data:image/png;base64...',
    navbarSection: '',
    omegaUpLockDown: false,
    associatedIdentities: [{ username: 'user', default: true }],
    notifications: [],
    fromLogin: false,
    userTypes: [],
    nextRegisteredContest: null,
  };

  it('Should handle empty navbar (in contest only)', () => {
    const wrapper = mount(common_Navbar, {
      propsData,
    });
    expect(wrapper.find('.nav-contests').exists()).toBe(false);
    expect(wrapper.find('.nav-courses').exists()).toBe(false);
    expect(wrapper.find('.nav-problems').exists()).toBe(false);
    expect(wrapper.find('.nav-rank').exists()).toBe(false);
  });

  it('Should handle common navbar to logged user', () => {
    const wrapper = mount(common_Navbar, {
      propsData: {
        ...propsData,
        ...{ inContest: false, userTypes: ['student', 'teacher'] },
      },
    });

    expect(wrapper.find('.nav-contests').exists()).toBe(true);
    expect(wrapper.find('.nav-courses').exists()).toBe(true);
    expect(wrapper.find('.nav-problems').exists()).toBe(true);
    expect(wrapper.find('.nav-rank').exists()).toBe(true);
    expect(wrapper.find('[data-login-button]').exists()).toBe(false);
  });

  it('Should handle common navbar to not-logged user', () => {
    const wrapper = mount(common_Navbar, {
      propsData: { ...propsData, ...{ inContest: false, isLoggedIn: false } },
    });

    expect(wrapper.find('.nav-problems').exists()).toBe(true);
    expect(wrapper.find('[data-nav-course]').exists()).toBe(true);
    expect(wrapper.find('.nav-rank').exists()).toBe(true);
    expect(wrapper.find('[data-login-button]').exists()).toBe(true);
    expect(wrapper.find('.navbar-right').text()).toBe(T.navLogIn);
  });

  it('Should show objectives modal only when a main user identity is logged', async () => {
    const wrapper = shallowMount(common_Navbar, {
      propsData: { ...propsData, ...{ fromLogin: true } },
    });

    expect(wrapper.findComponent(UserObjectivesQuestions).exists()).toBe(false);

    await wrapper.setProps({ isMainUserIdentity: true });

    expect(wrapper.findComponent(UserObjectivesQuestions).exists()).toBe(true);
  });

  it('Should show the next registered contest modal when the user is registered to a current or upcoming contest', () => {
    const wrapper = shallowMount(common_Navbar, {
      propsData: {
        ...propsData,
        ...{
          fromLogin: true,
          nextRegisteredContest: {
            admission_mode: '',
            alias: 'prueba',
            contest_id: 1,
            contestants: 10,
            description: 'Este es un concurso de prueba',
            duration: null,
            finish_time: new Date('2023-08-20'),
            last_updated: new Date('2023-08-16'),
            organizer: '',
            original_finish_time: new Date('2023-08-17'),
            participating: true,
            problemset_id: 1,
            recommended: true,
            rerun_id: 1,
            score_mode: null,
            scoreboard_url: null,
            scoreboard_url_admin: null,
            start_time: new Date('2023-08-16'),
            title: '',
            window_length: null,
          },
        },
      },
    });

    expect(wrapper.findComponent(user_NextRegisteredContest).exists()).toBe(true);
  });

  it('Should not show next registered contest modal when the user is not registered to a current or upcoming contest', () => {
    const wrapper = shallowMount(common_Navbar, {
      propsData: { ...propsData, ...{ fromLogin: true } },
    });

    expect(wrapper.findComponent(user_NextRegisteredContest).exists()).toBe(false);
  });
});
