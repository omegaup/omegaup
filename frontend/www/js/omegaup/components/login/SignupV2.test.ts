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

import T from '../../lang';
import SignupV2 from './SignupV2.vue';

describe('SignupV2.vue', () => {
  function createWrapper(
    propsData: Record<string, unknown> = {},
  ): Wrapper<Vue> {
    return shallowMount(SignupV2, {
      propsData: {
        githubClientId: 'dummy-github-id',
        githubState: 'dummy-state',
        googleClientId: 'dummy-google-id',
        validateRecaptcha: false,
        activeTab: 'signup',
        ...propsData,
      },
      mocks: {
        $cookies: {
          set: jest.fn(),
          get: jest.fn().mockReturnValue('true'),
          remove: jest.fn(),
        },
      },
    });
  }

  // ─── Rendering ───────────────────────────────────────────────────────

  it('renders all required signup form fields', () => {
    const wrapper = createWrapper();

    expect(wrapper.find('[data-signup-username]').exists()).toBe(true);
    expect(wrapper.find('[data-signup-email]').exists()).toBe(true);
    expect(wrapper.find('[data-signup-password]').exists()).toBe(true);
    expect(wrapper.find('[data-signup-repeat-password]').exists()).toBe(true);
    expect(wrapper.find('[data-signup-password-confirm]').exists()).toBe(true);
    expect(wrapper.find('[data-signup-submit]').exists()).toBe(true);
    expect(wrapper.find('[data-signup-accept-policies]').exists()).toBe(true);
    expect(wrapper.find('[data-signup-accept-privacy]').exists()).toBe(true);
    expect(wrapper.find('[data-signup-accept-conduct]').exists()).toBe(true);
    expect(wrapper.find('[data-signup-accept-terms]').exists()).toBe(true);
  });

  it('renders the left pane headline from T', () => {
    const wrapper = createWrapper();
    expect(wrapper.text()).toContain(T.signupV2Headline);
  });

  it('renders the signup card title from T', () => {
    const wrapper = createWrapper();
    expect(wrapper.find('.signup-title').text()).toContain(T.signupV2Header);
  });

  it('renders two tabs: login and signup', () => {
    const wrapper = createWrapper();
    const tabs = wrapper.findAll('button.auth-tab');
    expect(tabs.length).toBe(2);
  });

  it('marks the signup tab as active when activeTab is signup', () => {
    const wrapper = createWrapper({ activeTab: 'signup' });
    const tabs = wrapper.findAll('button.auth-tab');
    const signupTab = tabs.at(1);
    expect(signupTab.classes()).toContain('active');
    expect(signupTab.attributes('aria-selected')).toBe('true');
  });

  it('marks the login tab as active when activeTab is login', () => {
    const wrapper = createWrapper({ activeTab: 'login' });
    const tabs = wrapper.findAll('button.auth-tab');
    const loginTab = tabs.at(0);
    expect(loginTab.classes()).toContain('active');
    expect(loginTab.attributes('aria-selected')).toBe('true');
  });

  // ─── Events ──────────────────────────────────────────────────────────

  it('emits change-tab with login when login tab is clicked', async () => {
    const wrapper = createWrapper({ activeTab: 'signup' });
    const tabs = wrapper.findAll('button.auth-tab');
    await tabs.at(0).trigger('click');

    expect(wrapper.emitted('change-tab')).toBeTruthy();
    expect(wrapper.emitted('change-tab')![0]).toEqual(['login']);
  });

  it('emits change-tab with signup when signup tab is clicked', async () => {
    const wrapper = createWrapper({ activeTab: 'login' });
    const tabs = wrapper.findAll('button.auth-tab');
    await tabs.at(1).trigger('click');

    expect(wrapper.emitted('change-tab')).toBeTruthy();
    expect(wrapper.emitted('change-tab')![0]).toEqual(['signup']);
  });

  it('emits register-and-login with correct payload on form submit', async () => {
    const wrapper = createWrapper();

    (wrapper.vm as any).username = 'newuser';
    (wrapper.vm as any).email = 'newuser@example.com';
    (wrapper.vm as any).password = 'password123';
    (wrapper.vm as any).passwordConfirmation = 'password123';
    (wrapper.vm as any).privacyPolicyAccepted = true;
    (wrapper.vm as any).codeOfConductAccepted = true;

    await wrapper.find('form.signup-form').trigger('submit');

    expect(wrapper.emitted('register-and-login')).toBeTruthy();
    const emittedPayload = wrapper.emitted('register-and-login')![0][0] as any;
    expect(emittedPayload.username).toBe('newuser');
    expect(emittedPayload.email).toBe('newuser@example.com');
    expect(emittedPayload.password).toBe('password123');
    expect(emittedPayload.passwordConfirmation).toBe('password123');
    expect(emittedPayload.termsAndPolicies).toBe(true);
  });

  it('emits keyboard left/right to change tabs via keydown on tablist', async () => {
    const wrapper = createWrapper();
    const tablist = wrapper.find('[role="tablist"]');

    await tablist.trigger('keydown.left');
    expect(wrapper.emitted('change-tab')).toBeTruthy();
    expect(wrapper.emitted('change-tab')![0]).toEqual(['login']);

    await tablist.trigger('keydown.right');
    expect(wrapper.emitted('change-tab')![1]).toEqual(['signup']);
  });

  // ─── Accessibility ───────────────────────────────────────────────────

  it('tablist has role="tablist"', () => {
    const wrapper = createWrapper();
    expect(wrapper.find('[role="tablist"]').exists()).toBe(true);
  });

  it('tabs have role="tab"', () => {
    const wrapper = createWrapper();
    const tabs = wrapper.findAll('[role="tab"]');
    expect(tabs.length).toBe(2);
  });

  it('tab panels have role="tabpanel" and aria-labelledby', () => {
    const wrapper = createWrapper();
    const panels = wrapper.findAll('[role="tabpanel"]');
    expect(panels.length).toBeGreaterThanOrEqual(1);
    panels.wrappers.forEach((panel) => {
      expect(panel.attributes('aria-labelledby')).toBeTruthy();
    });
  });

  it('active tab has tabindex 0 and inactive tab has tabindex -1', () => {
    const wrapper = createWrapper({ activeTab: 'signup' });
    const tabs = wrapper.findAll('button.auth-tab');
    expect(tabs.at(0).attributes('tabindex')).toBe('-1');
    expect(tabs.at(1).attributes('tabindex')).toBe('0');
  });

  // ─── GitHub / Social Buttons ─────────────────────────────────────────

  it('GitHub button is disabled when githubClientId is empty', () => {
    const wrapper = createWrapper({ githubClientId: '' });
    const githubBtn = wrapper.find('[data-signup-github]');
    expect(githubBtn.exists()).toBe(true);
    expect(githubBtn.attributes('disabled')).toBe('disabled');
  });

  it('GitHub button is enabled when githubClientId is present', () => {
    const wrapper = createWrapper({ githubClientId: 'dummy-id' });
    const githubBtn = wrapper.find('[data-signup-github]');
    expect(githubBtn.exists()).toBe(true);
    expect(githubBtn.attributes('disabled')).toBeUndefined();
  });

  // ─── termsAndPolicies computed ───────────────────────────────────────

  it('termsAndPolicies is false when neither checkbox is checked', () => {
    const wrapper = createWrapper();
    (wrapper.vm as any).privacyPolicyAccepted = false;
    (wrapper.vm as any).codeOfConductAccepted = false;
    expect((wrapper.vm as any).termsAndPolicies).toBe(false);
  });

  it('termsAndPolicies is true when both checkboxes are checked', () => {
    const wrapper = createWrapper();
    (wrapper.vm as any).privacyPolicyAccepted = true;
    (wrapper.vm as any).codeOfConductAccepted = true;
    expect((wrapper.vm as any).termsAndPolicies).toBe(true);
  });

  // ─── Current Year ────────────────────────────────────────────────────

  it('currentYear returns the correct year', () => {
    const wrapper = createWrapper();
    expect((wrapper.vm as any).currentYear).toBe(new Date().getFullYear());
  });
});
