import { mount } from '@vue/test-utils';

import T from '../../lang';
import admin_Settings from './Settings.vue';

const mountSettings = (propsData: Record<string, unknown> = {}) =>
  mount(admin_Settings, {
    propsData,
    stubs: { 'font-awesome-icon': true },
  });

describe('Settings.vue', () => {
  it('Should show the card as enabled by default', () => {
    const wrapper = mountSettings();

    expect(wrapper.text()).toContain(T.wordsEphemeralGrader);
    expect(wrapper.text()).toContain(T.ephemeralGraderDescription);
    expect(wrapper.text()).toContain(T.ephemeralGraderStatusEnabled);
    expect(
      (wrapper.find('input[type="checkbox"]').element as HTMLInputElement)
        .checked,
    ).toBe(true);
  });

  it('Should show the card as disabled', () => {
    const wrapper = mountSettings({ ephemeralGraderEnabled: false });

    expect(wrapper.text()).toContain(T.ephemeralGraderStatusDisabled);
    expect(
      (wrapper.find('input[type="checkbox"]').element as HTMLInputElement)
        .checked,
    ).toBe(false);
  });

  it('Should emit the new value when the switch changes', async () => {
    const wrapper = mountSettings();

    await wrapper.find('input[type="checkbox"]').setChecked(false);

    expect(wrapper.emitted('update-ephemeral-grader')).toEqual([[false]]);
    expect(wrapper.text()).toContain(T.ephemeralGraderStatusDisabled);
  });
});
