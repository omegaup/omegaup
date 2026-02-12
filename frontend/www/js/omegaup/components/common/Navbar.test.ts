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
    expect(wrapper.find('.navbar-right').text()).toContain(T.navbarLogin);
    expect(wrapper.find('.navbar-right').text()).toContain(T.navbarRegister);
  });

  it('Should show objectives modal only when a main user identity is logged', async () => {
    const wrapper = shallowMount(common_Navbar, {
      propsData: { ...propsData, ...{ fromLogin: true } },
    });

    expect(wrapper.findComponent(UserObjectivesQuestions).exists()).toBe(false);

    await wrapper.setProps({ isMainUserIdentity: true });

    expect(wrapper.findComponent(UserObjectivesQuestions).exists()).toBe(true);
  });

  it('Should show the information of the next registered contest when the user is registered to a current or upcoming contest', () => {
    const currentDate = new Date();
    const startTime = new Date();
    const finishTime = new Date();
    startTime.setHours(startTime.getHours() + 1);
    finishTime.setHours(finishTime.getHours() + 2);

    const wrapper = mount(common_Navbar, {
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
            finish_time: finishTime,
            last_updated: currentDate,
            organizer: 'omegaup',
            original_finish_time: finishTime,
            participating: true,
            problemset_id: 1,
            recommended: true,
            rerun_id: 1,
            score_mode: null,
            scoreboard_url: null,
            scoreboard_url_admin: null,
            start_time: startTime,
            title: 'Concurso de prueba',
            window_length: null,
          },
        },
      },
    });

    const nextRegisteredContest = wrapper.findComponent(
      user_NextRegisteredContest,
    );
    const startDate =
      startTime.toLocaleDateString() + ' ' + startTime.toLocaleTimeString();
    expect(nextRegisteredContest.exists()).toBeTruthy();
    expect(nextRegisteredContest.text()).toContain('Concurso de prueba');
    expect(nextRegisteredContest.text()).toContain('omegaup');
    expect(nextRegisteredContest.text()).toContain('10');
    expect(nextRegisteredContest.text()).toContain('Inicia: ' + startDate);
    expect(nextRegisteredContest.text()).toContain('01:00:00');
  });

  it('Should not show the information of a next registered contest when the user is not registered to a current or upcoming contest', () => {
    const wrapper = shallowMount(common_Navbar, {
      propsData: { ...propsData, ...{ fromLogin: true } },
    });

    expect(
      wrapper.findComponent(user_NextRegisteredContest).exists(),
    ).toBeFalsy();
  });
});
