import { shallowMount } from '@vue/test-utils';

import problem_Settings from './Settings.vue';

const baseSettingsProps = {
  errors: [],
  extraWallTimeLimit: 0,
  inputLimit: 10240,
  memoryLimit: 32768,
  outputLimit: 10240,
  overallWallTimeLimit: 6000,
  timeLimit: 1000,
  validatorTimeLimit: 0,
};

describe('Settings.vue', () => {
  it('Should handle problem settings with disabled elements', async () => {
    const wrapper = shallowMount(problem_Settings, {
      propsData: baseSettingsProps,
    });

    expect(
      wrapper.find('input[name="validator_time_limit"]').attributes('disabled'),
    ).toBe('disabled');

    const languages = wrapper.find('select[name="languages"]')
      .element as HTMLInputElement;
    languages.value = 'cat';
    await languages.dispatchEvent(new Event('change'));

    const validator = wrapper.find('select[name="validator"]')
      .element as HTMLInputElement;
    validator.value = 'custom';
    await validator.dispatchEvent(new Event('change'));

    expect(
      wrapper.find('input[name="validator_time_limit"]').attributes('disabled'),
    ).toBeFalsy();
    expect(
      (wrapper.find('select[name="languages"]').element as HTMLInputElement)
        .value,
    ).toBe('cat');
    expect(
      (wrapper.find('select[name="validator"]').element as HTMLInputElement)
        .value,
    ).toBe('custom');
  });
});
