import { mount, shallowMount } from '@vue/test-utils';

import T from '../../lang';

import common_Navbar from './Navbar.vue';
import UserObjectivesQuestions from '../user/ObjectivesQuestions.vue';

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
});
