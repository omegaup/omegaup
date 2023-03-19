import { mount } from '@vue/test-utils';

import login_Signup from './Signup.vue';
import omegaup_DatePicker from '../DatePicker.vue';

describe('signup.vue', () => {
  // Setting an specific datetime to avoid flakiness in a leap-year
  const now = new Date('2022-01-01T00:00:00Z').getTime();
  let dateNowSpy: jest.SpyInstance<number, []> | null = null;
  beforeEach(() => {
    dateNowSpy = jest.spyOn(Date, 'now').mockImplementation(() => now);
  });
  afterEach(() => {
    if (dateNowSpy) {
      dateNowSpy.mockRestore();
    }
  });
  const expectedValues: {
    username: string;
    email: null | string;
    parent_email: null | string;
    password: string;
    password_confirmation: string;
    recaptcha: string;
    birth_date: Date;
  } = {
    username: 'Omegaup',
    email: null,
    parent_email: 'parentEmail@gmail.com',
    password: 'pass12345678',
    password_confirmation: 'pass12345678',
    recaptcha: '',
    birth_date: new Date('2012-01-01'),
  };

  it('should handle register form with initial values', () => {
    const wrapper = mount(login_Signup, {
      propsData: {
        validateRecaptcha: false,
      },
    });

    // All the fields in the form are disabled by default
    expect(
      wrapper.find('input[name="reg_username"]').attributes().disabled,
    ).toBeTruthy();
    expect(
      wrapper.find('input[name="reg_email"]').attributes().disabled,
    ).toBeTruthy();
    expect(
      wrapper.find('input[name="reg_password"]').attributes().disabled,
    ).toBeTruthy();
    expect(
      wrapper.find('input[name="reg_password_confirmation"]').attributes()
        .disabled,
    ).toBeTruthy();
    expect(
      wrapper.find('input[type="checkbox"]').attributes().disabled,
    ).toBeTruthy();
    expect(
      wrapper.find('button[name="sign_up"]').attributes().disabled,
    ).toBeTruthy();

    // Except the birthdate field
    expect(
      wrapper.find('input[name="reg_birthdate"]').attributes().disabled,
    ).toBeFalsy();
  });

  it('should handle an uncomplete registration for Under13 user', async () => {
    const wrapper = mount(login_Signup, {
      propsData: {
        validateRecaptcha: false,
      },
    });
    // The birthdate field is the only one initially enabled
    expect(wrapper.find('input[name="reg_birthdate"]').element).toBeEnabled();

    // All the rest of fields in the form are initially disabled
    expect(wrapper.find('input[name="reg_username"]').element).toBeDisabled();
    expect(wrapper.find('input[name="reg_email"]').element).toBeDisabled();
    expect(wrapper.find('input[name="reg_parent_email"]').exists()).toBeFalsy();
    expect(wrapper.find('input[name="reg_password"]').element).toBeDisabled();
    expect(
      wrapper.find('input[name="reg_password_confirmation"]').element,
    ).toBeDisabled();

    const policyPrivacyCheckbox = wrapper.find(
      'input[name="reg_accept_policies"]',
    ).element;
    expect(policyPrivacyCheckbox).toBeDisabled();

    await wrapper.findComponent(omegaup_DatePicker).setValue('2012-01-01');

    // Once reg_birthdate has been set, all the fields are enabled
    expect(wrapper.find('input[name="reg_birthdate"]').element).toBeEnabled();
    expect(wrapper.find('input[name="reg_username"]').element).toBeEnabled();
    // Now, email field doesn't exist because user is U13, so they should provide parent's email
    expect(wrapper.find('input[name="reg_email"]').exists()).toBeFalsy();
    expect(
      wrapper.find('input[name="reg_parent_email"]').element,
    ).toBeEnabled();
    expect(wrapper.find('input[name="reg_password"]').element).toBeEnabled();
    expect(
      wrapper.find('input[name="reg_password_confirmation"]').element,
    ).toBeEnabled();
    expect(policyPrivacyCheckbox).toBeEnabled();

    // Check that the button is disabled until required fields are filled
    expect(wrapper.find('button[name="sign_up"]').element).toBeDisabled();

    // Fill in all required fields and check the policy privacy checkbox
    await wrapper.find('input[name="reg_username"]').setValue('John Doe');
    await wrapper
      .find('input[name="reg_parent_email"]')
      .setValue('parentEmail@gmail.com');
    await wrapper.find('input[name="reg_password"]').setValue('pass12345678');
    await wrapper
      .find('input[name="reg_password_confirmation"]')
      .setValue('pass12345678');
    await wrapper.find('input[name="reg_birthdate"]').setValue('2005-01-01');
    await policyPrivacyCheckbox.click();

    // The button should now be enabled
    expect(wrapper.find('button[name="sign_up"]').element).toBeEnabled();

    // There should be no warning displayed, so the following assertion should pass
    expect(wrapper.find('.status').exists()).toBe(false);
  });

  it('should handle a complete registration for Under13 user', async () => {
    const wrapper = mount(login_Signup, {
      propsData: {
        validateRecaptcha: false,
      },
    });

    await wrapper.findComponent(omegaup_DatePicker).setValue('2012-01-01');

    const policyPrivacyCheckbox = wrapper.find('input[type="checkbox"]')
      .element as HTMLInputElement;

    await policyPrivacyCheckbox.click();

    await wrapper.find('input[name="reg_username"]').setValue('Omegaup');
    await wrapper
      .find('input[name="reg_parent_email"]')
      .setValue('parentEmail@gmail.com');
    await wrapper.find('input[name="reg_password"]').setValue('pass12345678');
    await wrapper
      .find('input[name="reg_password_confirmation"]')
      .setValue('pass12345678');

    await wrapper.find('button[name="sign_up"]').trigger('click');

    expect(wrapper.emitted('register-and-login')).toEqual([[expectedValues]]);
  });

  it('should handle an uncomplete registration for Over13 user', async () => {
    const wrapper = mount(login_Signup, {
      propsData: {
        validateRecaptcha: false,
      },
    });

    await wrapper.findComponent(omegaup_DatePicker).setValue('2009-01-01');

    // All the fields in the form enabled when birthdate is filled
    expect(
      wrapper.find('input[name="reg_username"]').attributes().disabled,
    ).toBeFalsy();
    expect(
      wrapper.find('input[name="reg_email"]').attributes().disabled,
    ).toBeFalsy();
    expect(
      wrapper.find('input[name="reg_password"]').attributes().disabled,
    ).toBeFalsy();
    expect(
      wrapper.find('input[name="reg_password_confirmation"]').attributes()
        .disabled,
    ).toBeFalsy();
    expect(
      wrapper.find('input[type="checkbox"]').attributes().disabled,
    ).toBeFalsy();
    expect(
      wrapper.find('input[name="reg_birthdate"]').attributes().disabled,
    ).toBeFalsy();

    // Now, parent email field doesn't exist because user is Over13, so they should provide their own email
    expect(wrapper.find('input[name="reg_parent_email"]').exists()).toBe(false);

    // The button should be enabled until policy privacy is checked
    expect(
      wrapper.find('button[name="sign_up"]').attributes().disabled,
    ).toBeTruthy();

    const policyPrivacyCheckbox = wrapper.find('input[type="checkbox"]')
      .element as HTMLInputElement;

    await policyPrivacyCheckbox.click();

    expect(
      wrapper.find('button[name="sign_up"]').attributes().disabled,
    ).toBeFalsy();

    expect(wrapper.find('form').classes('was-validated')).toBeFalsy();
    await wrapper.find('button[name="sign_up"]').trigger('click');
    expect(wrapper.find('form').classes('was-validated')).toBeTruthy();
  });

  it('should handle a complete registration for Over13 user', async () => {
    const wrapper = mount(login_Signup, {
      propsData: {
        validateRecaptcha: false,
      },
    });

    await wrapper.findComponent(omegaup_DatePicker).setValue('2009-01-01');

    const policyPrivacyCheckbox = wrapper.find('input[type="checkbox"]')
      .element as HTMLInputElement;

    await policyPrivacyCheckbox.click();

    await wrapper.find('input[name="reg_username"]').setValue('Omegaup');
    await wrapper.find('input[name="reg_email"]').setValue('email@gmail.com');
    await wrapper.find('input[name="reg_password"]').setValue('pass12345678');
    await wrapper
      .find('input[name="reg_password_confirmation"]')
      .setValue('pass12345678');

    await wrapper.find('button[name="sign_up"]').trigger('click');

    expectedValues.parent_email = null;
    expectedValues.email = 'email@gmail.com';
    expectedValues.birth_date = new Date('2009-01-01');
    expect(wrapper.emitted('register-and-login')).toEqual([[expectedValues]]);
  });
});
