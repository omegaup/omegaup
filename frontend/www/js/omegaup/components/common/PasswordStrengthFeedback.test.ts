import { shallowMount } from '@vue/test-utils';
import PasswordStrengthFeedback from './PasswordStrengthFeedback.vue';

describe('PasswordStrengthFeedback.vue', () => {
  it('shows all requirements as unmet for empty password', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: '' },
    });
    const vm = wrapper.vm as any;

    expect(vm.hasMinLength).toBe(false);
    expect(vm.hasUppercase).toBe(false);
    expect(vm.hasLowercase).toBe(false);
    expect(vm.hasDigit).toBe(false);
    expect(vm.hasSpecialChar).toBe(false);
    expect(vm.isValid).toBe(false);
  });

  it('shows length requirement as met when password has 8+ characters', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: 'abcdefgh' },
    });
    const vm = wrapper.vm as any;

    expect(vm.hasMinLength).toBe(true);
  });

  it('shows length requirement as unmet when password has less than 8 characters', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: 'abc' },
    });
    const vm = wrapper.vm as any;

    expect(vm.hasMinLength).toBe(false);
  });

  it('shows uppercase requirement as met when password has uppercase letter', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: 'Password' },
    });
    const vm = wrapper.vm as any;

    expect(vm.hasUppercase).toBe(true);
  });

  it('shows lowercase requirement as met when password has lowercase letter', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: 'PASSWORD1a' },
    });
    const vm = wrapper.vm as any;

    expect(vm.hasLowercase).toBe(true);
  });

  it('shows digit requirement as met when password has a number', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: 'password1' },
    });
    const vm = wrapper.vm as any;

    expect(vm.hasDigit).toBe(true);
  });

  it('shows special char requirement as met when password has special character', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: 'password!' },
    });
    const vm = wrapper.vm as any;

    expect(vm.hasSpecialChar).toBe(true);
  });

  it('shows isValid as true when all requirements are met', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: 'Password1!' },
    });
    const vm = wrapper.vm as any;

    expect(vm.hasMinLength).toBe(true);
    expect(vm.hasUppercase).toBe(true);
    expect(vm.hasLowercase).toBe(true);
    expect(vm.hasDigit).toBe(true);
    expect(vm.hasSpecialChar).toBe(true);
    expect(vm.isValid).toBe(true);
  });

  it('emits validity-change event when validity changes', async () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: '' },
    });

    // Initial emit with false
    expect(wrapper.emitted('validity-change')).toBeDefined();
    expect(wrapper.emitted('validity-change')![0]).toEqual([false]);

    // Update to valid password
    await wrapper.setProps({ password: 'Password1!' });
    expect(wrapper.emitted('validity-change')![1]).toEqual([true]);
  });

  it('renders requirement elements with correct classes', () => {
    const wrapper = shallowMount(PasswordStrengthFeedback, {
      propsData: { password: 'Password' },
    });

    const requirements = wrapper.findAll('.requirement');
    expect(requirements.length).toBe(5);

    // Check that met class is applied correctly
    // Password has length >= 8, uppercase, and lowercase
    expect(requirements.at(0).classes()).toContain('met'); // length
    expect(requirements.at(1).classes()).toContain('met'); // uppercase
    expect(requirements.at(2).classes()).toContain('met'); // lowercase
    expect(requirements.at(3).classes()).not.toContain('met'); // digit
    expect(requirements.at(4).classes()).not.toContain('met'); // special char
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
      const vm = wrapper.vm as any;
      expect(vm.hasSpecialChar).toBe(true);
    });
  });
});
