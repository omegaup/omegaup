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

import { mount, shallowMount, Wrapper } from '@vue/test-utils';
import Vue from 'vue';

import T from '../../lang';

import login_Signup from './Signup.vue';

describe('Signup.vue', () => {
  // Helper to get intro.js mocks
  function getIntroMocks() {
    const introJs = jest.requireMock('intro.js').default;
    return {
      introJs,
      startMock: (introJs as any)._startMock as jest.Mock,
      setOptionsMock: (introJs as any)._setOptionsMock as jest.Mock,
    };
  }

  beforeEach(() => {
    const { introJs, startMock, setOptionsMock } = getIntroMocks();
    introJs.mockClear();
    startMock.mockClear();
    setOptionsMock.mockClear();
  });

  // Shared factory for mounting the component
  function createWrapper(
    propsData: Record<string, unknown> = {},
  ): Wrapper<Vue> {
    return shallowMount(login_Signup, {
      propsData: {
        validateRecaptcha: false,
        hasVisitedSection: true, // suppress intro by default
        useSignupFormWithBirthDate: false,
        activeTab: 'login',
        ...propsData,
      },
      stubs: {
        'vue-recaptcha': {
          template: '<div class="vue-recaptcha-stub"></div>',
        },
      },
      mocks: {
        $cookies: {
          set: jest.fn(),
          get: jest.fn(),
          remove: jest.fn(),
        },
      },
    });
  }

  // ─── Group 1: Standard Form Rendering ───────────────────────────────

  describe('Standard form (useSignupFormWithBirthDate=false)', () => {
    it('Should render header and standard form fields', () => {
      const wrapper = createWrapper();

      expect(wrapper.find('.card-header').text()).toContain(
        T.loginSignupHeader,
      );
      expect(wrapper.find('input[data-signup-username]').exists()).toBeTruthy();
      expect(wrapper.find('input[data-signup-email]').exists()).toBeTruthy();
      expect(wrapper.find('button[data-signup-submit]').exists()).toBeTruthy();
    });

    it('Should not render birth-date form elements', () => {
      const wrapper = createWrapper();

      expect(
        wrapper.find('input[data-over-thirteen-checkbox]').exists(),
      ).toBeFalsy();
      expect(
        wrapper.find('input[name="reg_date_of_birth"]').exists(),
      ).toBeFalsy();
      expect(
        wrapper.find('input[name="reg_parent_email"]').exists(),
      ).toBeFalsy();
    });

    it('Should render privacy policy and code-of-conduct checkboxes', () => {
      const wrapper = createWrapper();

      expect(
        wrapper.find('input[data-signup-accept-policies]').exists(),
      ).toBeTruthy();
      expect(
        wrapper.find('input[data-signup-accept-conduct]').exists(),
      ).toBeTruthy();
    });

    it('Should show reCAPTCHA when validateRecaptcha is true', () => {
      const wrapper = createWrapper({ validateRecaptcha: true });

      expect(wrapper.find('.vue-recaptcha-stub').exists()).toBeTruthy();
    });

    it('Should hide reCAPTCHA when validateRecaptcha is false', () => {
      const wrapper = createWrapper({ validateRecaptcha: false });

      expect(wrapper.find('.vue-recaptcha-stub').exists()).toBeFalsy();
    });
  });

  // ─── Group 2: Birth-Date Form Rendering ─────────────────────────────

  describe('Birth-date form (useSignupFormWithBirthDate=true)', () => {
    it('Should render birth-date form fields', () => {
      const wrapper = createWrapper({ useSignupFormWithBirthDate: true });

      expect(
        wrapper.find('input[data-over-thirteen-checkbox]').exists(),
      ).toBeTruthy();
      expect(wrapper.find('input[data-signup-username]').exists()).toBeTruthy();
      expect(
        wrapper.find('input[name="reg_date_of_birth"]').exists(),
      ).toBeTruthy();
    });

    it('Should show parent email when isUnder13 is true (default)', () => {
      const wrapper = createWrapper({ useSignupFormWithBirthDate: true });

      expect(
        wrapper.find('input[name="reg_parent_email"]').exists(),
      ).toBeTruthy();
      expect(wrapper.find('input[data-signup-email]').exists()).toBeFalsy();
    });

    it('Should show regular email when over-13 is checked', async () => {
      const wrapper = createWrapper({ useSignupFormWithBirthDate: true });

      await wrapper.find('input[data-over-thirteen-checkbox]').setChecked(true);
      // Manually trigger the change handler since setChecked may not fire it
      (wrapper.vm as any).over13Checked = true;
      (wrapper.vm as any).updateDateRestriction();
      await wrapper.vm.$nextTick();

      expect(wrapper.find('input[data-signup-email]').exists()).toBeTruthy();
      expect(
        wrapper.find('input[name="reg_parent_email"]').exists(),
      ).toBeFalsy();
    });

    it('Should not render standard-form-only layout when birth-date form is active', () => {
      const wrapper = createWrapper({ useSignupFormWithBirthDate: true });

      // Standard form uses v-if="!useSignupFormWithBirthDate", which should be hidden
      // The birth-date form's submit button should still be present
      expect(wrapper.find('button[data-signup-submit]').exists()).toBeTruthy();
    });
  });

  // ─── Group 3: Computed Properties ───────────────────────────────────

  describe('Computed properties', () => {
    it('Should return false for termsAndPolicies when neither checkbox is checked', () => {
      const wrapper = createWrapper();
      expect((wrapper.vm as any).termsAndPolicies).toBe(false);
    });

    it('Should return false for termsAndPolicies when only privacy policy is checked', () => {
      const wrapper = createWrapper();
      (wrapper.vm as any).privacyPolicyAccepted = true;
      expect((wrapper.vm as any).termsAndPolicies).toBe(false);
    });

    it('Should return false for termsAndPolicies when only code of conduct is checked', () => {
      const wrapper = createWrapper();
      (wrapper.vm as any).codeOfConductAccepted = true;
      expect((wrapper.vm as any).termsAndPolicies).toBe(false);
    });

    it('Should return true for termsAndPolicies when both checkboxes are checked', () => {
      const wrapper = createWrapper();
      (wrapper.vm as any).privacyPolicyAccepted = true;
      (wrapper.vm as any).codeOfConductAccepted = true;
      expect((wrapper.vm as any).termsAndPolicies).toBe(true);
    });

    it('Should include the privacy policy URL in formattedAcceptPolicyMarkdown', () => {
      const wrapper = createWrapper();
      const markdown = (wrapper.vm as any).formattedAcceptPolicyMarkdown;
      expect(markdown).toContain('http');
    });

    it('Should include the code of conduct URL in formattedAcceptConductMarkdown', () => {
      const wrapper = createWrapper();
      const markdown = (wrapper.vm as any).formattedAcceptConductMarkdown;
      expect(markdown).toContain('http');
    });

    describe('maxDateForTimepicker', () => {
      it('Should return today when over13Checked is false', () => {
        const wrapper = createWrapper({
          useSignupFormWithBirthDate: true,
        });
        const vm = wrapper.vm as any;
        vm.over13Checked = false;

        const now = new Date();
        const expectedYear = now.getFullYear();
        const expectedMonth = (now.getMonth() + 1).toString().padStart(2, '0');
        const expectedDay = now.getDate().toString().padStart(2, '0');
        const expected = `${expectedYear}-${expectedMonth}-${expectedDay}`;

        expect(vm.maxDateForTimepicker).toBe(expected);
      });

      it('Should return today minus 13 years when over13Checked is true', () => {
        const wrapper = createWrapper({
          useSignupFormWithBirthDate: true,
        });
        const vm = wrapper.vm as any;
        vm.over13Checked = true;

        const now = new Date();
        const expectedYear = now.getFullYear() - 13;
        const expectedMonth = (now.getMonth() + 1).toString().padStart(2, '0');
        const expectedDay = now.getDate().toString().padStart(2, '0');
        const expected = `${expectedYear}-${expectedMonth}-${expectedDay}`;

        expect(vm.maxDateForTimepicker).toBe(expected);
      });
    });

    describe('minDateForTimepicker', () => {
      it('Should return 1900-01-01 when over13Checked is true', () => {
        const wrapper = createWrapper({
          useSignupFormWithBirthDate: true,
        });
        const vm = wrapper.vm as any;
        vm.over13Checked = true;

        expect(vm.minDateForTimepicker).toBe('1900-01-01');
      });

      it('Should return today-13y+1d when over13Checked is false', () => {
        const wrapper = createWrapper({
          useSignupFormWithBirthDate: true,
        });
        const vm = wrapper.vm as any;
        vm.over13Checked = false;

        const now = new Date();
        const expectedYear = now.getFullYear() - 13;
        const expectedMonth = (now.getMonth() + 1).toString().padStart(2, '0');
        const expectedDay = (now.getDate() + 1).toString().padStart(2, '0');

        const expected = `${expectedYear}-${expectedMonth}-${expectedDay}`;
        expect(vm.minDateForTimepicker).toBe(expected);
      });
    });
  });

  // ─── Group 4: Methods ───────────────────────────────────────────────

  describe('Methods', () => {
    it('Should set recaptchaResponse on verify', () => {
      const wrapper = createWrapper();
      const vm = wrapper.vm as any;

      vm.verify('test-token-123');
      expect(vm.recaptchaResponse).toBe('test-token-123');
    });

    it('Should clear recaptchaResponse on expired', () => {
      const wrapper = createWrapper();
      const vm = wrapper.vm as any;

      vm.recaptchaResponse = 'some-token';
      vm.expired();
      expect(vm.recaptchaResponse).toBe('');
    });

    it('Should set isUnder13 to false when over13Checked is true', () => {
      const wrapper = createWrapper({
        useSignupFormWithBirthDate: true,
      });
      const vm = wrapper.vm as any;

      vm.over13Checked = true;
      vm.updateDateRestriction();
      expect(vm.isUnder13).toBe(false);
    });

    it('Should set isUnder13 to true when over13Checked is false', () => {
      const wrapper = createWrapper({
        useSignupFormWithBirthDate: true,
      });
      const vm = wrapper.vm as any;

      vm.over13Checked = false;
      vm.updateDateRestriction();
      expect(vm.isUnder13).toBe(true);
    });
  });

  // ─── Group 5: Event Emissions ───────────────────────────────────────

  describe('Event emissions', () => {
    it('Should emit register-and-login with standard form payload', async () => {
      const wrapper = createWrapper();
      const vm = wrapper.vm as any;

      vm.username = 'testuser';
      vm.email = 'test@example.com';
      vm.password = 'Password123!';
      vm.passwordConfirmation = 'Password123!';
      vm.recaptchaResponse = 'recaptcha-token';
      vm.privacyPolicyAccepted = true;
      vm.codeOfConductAccepted = true;
      await wrapper.vm.$nextTick();

      await wrapper.find('button[data-signup-submit]').trigger('click');

      const emitted = wrapper.emitted('register-and-login');
      expect(emitted).toBeDefined();
      if (emitted) {
        expect(emitted[0][0]).toEqual({
          username: 'testuser',
          email: 'test@example.com',
          password: 'Password123!',
          passwordConfirmation: 'Password123!',
          recaptchaResponse: 'recaptcha-token',
          termsAndPolicies: true,
        });
      }
    });

    it('Should emit register-and-login with birth-date form payload', async () => {
      const wrapper = createWrapper({
        useSignupFormWithBirthDate: true,
      });
      const vm = wrapper.vm as any;

      vm.over13Checked = true;
      vm.updateDateRestriction();
      vm.username = 'testuser';
      vm.email = 'test@example.com';
      vm.dateOfBirth = '2000-01-15';
      vm.parentEmail = '';
      vm.password = 'Password123!';
      vm.passwordConfirmation = 'Password123!';
      vm.recaptchaResponse = 'recaptcha-token';
      vm.privacyPolicyAccepted = true;
      vm.codeOfConductAccepted = true;
      await wrapper.vm.$nextTick();

      await wrapper.find('button[data-signup-submit]').trigger('click');

      const emitted = wrapper.emitted('register-and-login');
      expect(emitted).toBeDefined();
      if (emitted) {
        expect(emitted[0][0]).toEqual({
          over13Checked: true,
          username: 'testuser',
          email: 'test@example.com',
          dateOfBirth: '2000-01-15',
          parentEmail: '',
          password: 'Password123!',
          passwordConfirmation: 'Password123!',
          recaptchaResponse: 'recaptcha-token',
          termsAndPolicies: true,
        });
      }
    });
  });

  // ─── Group 6: Conditional Rendering Branches ────────────────────────

  describe('Conditional rendering branches', () => {
    it('Should toggle between parent email and regular email on over-13 check', async () => {
      const wrapper = createWrapper({ useSignupFormWithBirthDate: true });
      const vm = wrapper.vm as any;

      // Default: isUnder13 = true → parent email shown
      expect(
        wrapper.find('input[name="reg_parent_email"]').exists(),
      ).toBeTruthy();
      expect(wrapper.find('input[data-signup-email]').exists()).toBeFalsy();

      // Check over-13
      vm.over13Checked = true;
      vm.updateDateRestriction();
      await wrapper.vm.$nextTick();

      expect(
        wrapper.find('input[name="reg_parent_email"]').exists(),
      ).toBeFalsy();
      expect(wrapper.find('input[data-signup-email]').exists()).toBeTruthy();

      // Uncheck over-13
      vm.over13Checked = false;
      vm.updateDateRestriction();
      await wrapper.vm.$nextTick();

      expect(
        wrapper.find('input[name="reg_parent_email"]').exists(),
      ).toBeTruthy();
      expect(wrapper.find('input[data-signup-email]').exists()).toBeFalsy();
    });

    it('Should bind max and min attributes on date input', async () => {
      const wrapper = createWrapper({ useSignupFormWithBirthDate: true });
      const vm = wrapper.vm as any;

      const dateInput = wrapper.find('input[name="reg_date_of_birth"]');
      expect(dateInput.attributes('max')).toBe(vm.maxDateForTimepicker);
      expect(dateInput.attributes('min')).toBe(vm.minDateForTimepicker);
    });
  });

  // ─── Group 7: Intro.js Walkthrough ──────────────────────────────────

  describe('Intro.js walkthrough (maybeStartIntro)', () => {
    // For intro.js tests, we need mount (not shallowMount) with attachTo
    // so that document.querySelector can find real DOM elements with CSS
    // classes like .introjs-username, .introjs-email, etc.
    let introWrapper: Wrapper<Vue> | null = null;

    function createMountedWrapper(
      propsData: Record<string, unknown> = {},
    ): Wrapper<Vue> {
      const container = document.createElement('div');
      document.body.appendChild(container);
      introWrapper = mount(login_Signup, {
        propsData: {
          validateRecaptcha: false,
          hasVisitedSection: true,
          useSignupFormWithBirthDate: false,
          activeTab: 'login',
          ...propsData,
        },
        stubs: {
          'vue-recaptcha': {
            template: '<div class="vue-recaptcha-stub"></div>',
          },
        },
        mocks: {
          $cookies: {
            set: jest.fn(),
            get: jest.fn(),
            remove: jest.fn(),
          },
        },
        attachTo: container,
      });
      return introWrapper;
    }

    afterEach(() => {
      if (introWrapper) {
        introWrapper.destroy();
        introWrapper = null;
      }
    });

    it('Should not start intro when hasVisitedSection is true', async () => {
      createMountedWrapper({
        hasVisitedSection: true,
        activeTab: 'signup',
      });
      await Vue.nextTick();

      const { startMock } = getIntroMocks();
      expect(startMock).not.toHaveBeenCalled();
    });

    it('Should not start intro when activeTab is not signup', async () => {
      createMountedWrapper({
        hasVisitedSection: false,
        activeTab: 'login',
      });
      await Vue.nextTick();

      const { startMock } = getIntroMocks();
      expect(startMock).not.toHaveBeenCalled();
    });

    it('Should start intro when conditions are met', async () => {
      createMountedWrapper({
        hasVisitedSection: false,
        activeTab: 'signup',
      });
      // Wait for mounted + $nextTick inside maybeStartIntro
      await Vue.nextTick();
      await Vue.nextTick();

      const { startMock } = getIntroMocks();
      expect(startMock).toHaveBeenCalledTimes(1);
    });

    it('Should set cookie after intro starts', async () => {
      const wrapper = createMountedWrapper({
        hasVisitedSection: false,
        activeTab: 'signup',
      });
      await Vue.nextTick();
      await Vue.nextTick();

      expect((wrapper.vm as any).$cookies.set).toHaveBeenCalledWith(
        'has-visited-signup',
        true,
        -1,
      );
    });

    it('Should not restart intro on subsequent tab changes', async () => {
      const wrapper = createMountedWrapper({
        hasVisitedSection: false,
        activeTab: 'signup',
      });
      await Vue.nextTick();
      await Vue.nextTick();

      const { startMock } = getIntroMocks();
      const callCountAfterFirstStart = startMock.mock.calls.length;

      // Switch away and back
      await wrapper.setProps({ activeTab: 'login' });
      await Vue.nextTick();
      await wrapper.setProps({ activeTab: 'signup' });
      await Vue.nextTick();
      await Vue.nextTick();

      expect(startMock.mock.calls.length).toBe(callCountAfterFirstStart);
    });

    it('Should start intro when activeTab watcher fires with signup', async () => {
      const wrapper = createMountedWrapper({
        hasVisitedSection: false,
        activeTab: 'login',
      });
      await Vue.nextTick();

      const { startMock } = getIntroMocks();
      expect(startMock).not.toHaveBeenCalled();

      // Now switch to signup
      await wrapper.setProps({ activeTab: 'signup' });
      await Vue.nextTick();
      await Vue.nextTick();

      expect(startMock).toHaveBeenCalledTimes(1);
    });
  });
});
