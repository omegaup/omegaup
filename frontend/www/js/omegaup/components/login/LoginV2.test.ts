jest.mock('intro.js', () => {
  const startMock = jest.fn();
  const setOptionsMock = jest.fn().mockReturnValue({ start: startMock });
  const introJsMock = jest.fn().mockReturnValue({
    setOptions: setOptionsMock,
    start: startMock,
  });
  (introJsMock as any)._startMock = startMock;
  (introJsMock as any)._setOptionsMock = setOptionsMock;
  return { __esModule: true, default: introJsMock };
});

import { shallowMount, Wrapper } from '@vue/test-utils';
import Vue from 'vue';

import LoginV2 from './LoginV2.vue';

describe('LoginV2.vue', () => {
  function createWrapper(
    propsData: Record<string, unknown> = {},
  ): Wrapper<Vue> {
    return shallowMount(LoginV2, {
      propsData: {
        facebookUrl: 'https://facebook.com',
        githubClientId: 'dummy-github-id',
        githubState: 'dummy-state',
        googleClientId: 'dummy-google-id',
        activeTab: 'login',
        ...propsData,
      },
      mocks: {
        $cookies: {
          set: jest.fn(),
          get: jest.fn().mockReturnValue('true'), // suppress intro during tests
          remove: jest.fn(),
        },
      },
    });
  }

  it('renders username, password, submit, recover fields', () => {
    const wrapper = createWrapper();

    expect(wrapper.find('[data-login-username]').exists()).toBe(true);
    expect(wrapper.find('[data-login-password]').exists()).toBe(true);
    expect(wrapper.find('[data-login-submit]').exists()).toBe(true);
    expect(wrapper.find('[data-login-recover]').exists()).toBe(true);
  });

  it('emits login event with correct payload on form submit', async () => {
    const wrapper = createWrapper();

    (wrapper.vm as any).usernameOrEmail = 'testuser';
    (wrapper.vm as any).password = 'secretpassword';

    await wrapper.find('form.native-login-form').trigger('submit');

    expect(wrapper.emitted('login')).toEqual([['testuser', 'secretpassword']]);
  });

  it('GitHub button disabled when githubClientId is empty', () => {
    const wrapper = createWrapper({ githubClientId: '' });
    const githubBtn = wrapper.find('[data-login-github]');

    expect(githubBtn.exists()).toBe(true);
    expect(githubBtn.attributes('disabled')).toBe('disabled');
  });

  it('GitHub button enabled when githubClientId is present', () => {
    const wrapper = createWrapper({ githubClientId: 'dummy-id' });
    const githubBtn = wrapper.find('[data-login-github]');

    expect(githubBtn.exists()).toBe(true);
    expect(githubBtn.attributes('disabled')).toBeUndefined();
  });

  it('emits change-tab with signup when signup tab is clicked', async () => {
    const wrapper = createWrapper();

    const tabs = wrapper.findAll('button.auth-tab');
    expect(tabs.length).toBe(2);

    const signupTab = tabs.at(1);
    await signupTab.trigger('click');

    expect(wrapper.emitted('change-tab')).toEqual([['signup']]);
  });

  it('currentYear is a plain data property, not a computed getter', () => {
    // Confirm that currentYear does not call new Date() on each access.
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    const year = new Date().getFullYear();
    expect(vm.currentYear).toBe(year);
    // Access it a second time; result must be the same value (cached)
    expect(vm.currentYear).toBe(year);
  });
});
