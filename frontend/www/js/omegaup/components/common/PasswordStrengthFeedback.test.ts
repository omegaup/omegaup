import { shallowMount } from '@vue/test-utils';
import PasswordStrengthFeedback from './PasswordStrengthFeedback.vue';
import {
  PASSWORD_EMPTY,
  PASSWORD_TOO_SHORT,
  PASSWORD_ONLY_LOWERCASE,
  PASSWORD_WITH_UPPERCASE,
  PASSWORD_WITH_LOWERCASE,
  PASSWORD_WITH_DIGIT,
  PASSWORD_WITH_SPECIAL,
  PASSWORD_VALID,
} from './testPasswordConstants';

describe('PasswordStrengthFeedback.vue', () => {
  it('shows all requirements as unmet for empty password', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: PASSWORD_EMPTY },
    });

    expect(wrapper.vm.hasMinLength).toBe(false);
    expect(wrapper.vm.hasUppercase).toBe(false);
    expect(wrapper.vm.hasLowercase).toBe(false);
    expect(wrapper.vm.hasDigit).toBe(false);
    expect(wrapper.vm.hasSpecialChar).toBe(false);
    expect(wrapper.vm.isValid).toBe(false);
  });

  it('shows length requirement as met when password has 8+ characters', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: PASSWORD_ONLY_LOWERCASE },
    });

    expect(wrapper.vm.hasMinLength).toBe(true);
  });

  it('shows length requirement as unmet when password has less than 8 characters', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: PASSWORD_TOO_SHORT },
    });

    expect(wrapper.vm.hasMinLength).toBe(false);
  });

  it('shows uppercase requirement as met when password has uppercase letter', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: PASSWORD_WITH_UPPERCASE },
    });

    expect(wrapper.vm.hasUppercase).toBe(true);
  });

  it('shows lowercase requirement as met when password has lowercase letter', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: PASSWORD_WITH_LOWERCASE },
    });

    expect(wrapper.vm.hasLowercase).toBe(true);
  });

  it('shows digit requirement as met when password has a number', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: PASSWORD_WITH_DIGIT },
    });

    expect(wrapper.vm.hasDigit).toBe(true);
  });

  it('shows special char requirement as met when password has special character', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: PASSWORD_WITH_SPECIAL },
    });

    expect(wrapper.vm.hasSpecialChar).toBe(true);
  });

  it('shows isValid as true when all requirements are met', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: PASSWORD_VALID },
    });

    expect(wrapper.vm.hasMinLength).toBe(true);
    expect(wrapper.vm.hasUppercase).toBe(true);
    expect(wrapper.vm.hasLowercase).toBe(true);
    expect(wrapper.vm.hasDigit).toBe(true);
    expect(wrapper.vm.hasSpecialChar).toBe(true);
    expect(wrapper.vm.isValid).toBe(true);
  });

  it('emits validity-change event when validity changes', async () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: PASSWORD_EMPTY },
    });

    // Initial emit with false
    const emitted = wrapper.emitted('validity-change');
    expect(emitted).toBeDefined();
    expect(emitted?.[0]).toEqual([false]);

    // Update to valid password
    await wrapper.setProps({ password: PASSWORD_VALID });
    expect(emitted?.[1]).toEqual([true]);
  });

  it('shows weak badge when password is invalid', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: PASSWORD_EMPTY },
    });

    const badge = wrapper.find('.strength-badge');
    expect(badge.classes()).toContain('weak');
    expect(badge.classes()).not.toContain('strong');
  });

  it('shows strong badge when all requirements are met', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: PASSWORD_VALID },
    });

    const badge = wrapper.find('.strength-badge');
    expect(badge.classes()).toContain('strong');
    expect(badge.classes()).not.toContain('weak');
  });

  it('shows requirements tooltip on hover with correct classes', async () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: PASSWORD_WITH_UPPERCASE },
    });

    // Requirements not visible before hover
    expect(wrapper.find('.requirements-tooltip').exists()).toBe(false);

    // Hover to show details
    await wrapper.find('.strength-badge').trigger('mouseenter');

    const requirements = wrapper.findAll('.requirement');
    expect(requirements.length).toBe(5);

    // Password has length >= 8, uppercase, and lowercase
    expect(requirements.at(0).classes()).toContain('met'); // length
    expect(requirements.at(1).classes()).toContain('met'); // uppercase
    expect(requirements.at(2).classes()).toContain('met'); // lowercase
    expect(requirements.at(3).classes()).not.toContain('met'); // digit
    expect(requirements.at(4).classes()).not.toContain('met'); // special char
  });

  it('hides requirements tooltip on mouse leave', async () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: PASSWORD_WITH_UPPERCASE },
    });

    await wrapper.find('.strength-badge').trigger('mouseenter');
    expect(wrapper.find('.requirements-tooltip').exists()).toBe(true);

    await wrapper.find('.strength-badge').trigger('mouseleave');
    expect(wrapper.find('.requirements-tooltip').exists()).toBe(false);
  });

  it('handles various special characters correctly', () => {
    const specialChars = [
      '!',
      '@',
      '#',
      '$',
      '%',
      '^',
      '&',
      '*',
      '(',
      ')',
      ',',
      '.',
      '?',
      '"',
      ':',
      '{',
      '}',
      '|',
      '<',
      '>',
    ];

    specialChars.forEach((char) => {
      const wrapper = shallowMount(PasswordStrengthFeedback, {
        propsData: { password: `test${char}` },
      });
      expect(wrapper.vm.hasSpecialChar).toBe(true);
    });
  });
});
