import { shallowMount } from '@vue/test-utils';

import Creator from './Creator.vue';
import Header from './Header.vue';
import Tabs from './Tabs.vue';

import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';

describe('Creator.vue', () => {
  it('Should contain Header and Tabs Components', async () => {
    const wrapper = shallowMount(Creator, {});

    expect(wrapper.findComponent(Header).exists()).toBe(true);
    expect(wrapper.findComponent(Tabs).exists()).toBe(true);
  });
});
