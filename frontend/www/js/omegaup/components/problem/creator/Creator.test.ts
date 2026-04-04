import { shallowMount, createLocalVue } from '@vue/test-utils';

import Creator from './Creator.vue';
import Header from './Header.vue';
import Tabs from './Tabs.vue';

import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('Creator.vue', () => {
  it('Should contain Header and Tabs Components', async () => {
    const wrapper = shallowMount(Creator, { localVue });

    expect(wrapper.findComponent(Header).exists()).toBe(true);
    expect(wrapper.findComponent(Tabs).exists()).toBe(true);
  });
});
