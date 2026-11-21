import { ComponentOptions } from 'vue';
import { shallowMount } from '@vue/test-utils';
import arena_ContestSkeleton from './ContestSkeleton.vue';

describe('ContestSkeleton.vue', () => {
  it('Should render contest loading skeleton elements', () => {
    const wrapper = shallowMount(
      arena_ContestSkeleton as ComponentOptions<Vue>,
    );

    expect(wrapper.find('.skeleton-title').exists()).toBe(true);
    expect(wrapper.find('.skeleton-subtitle').exists()).toBe(true);
    expect(wrapper.find('.skeleton-organizer').exists()).toBe(true);
    expect(wrapper.find('.skeleton-button').exists()).toBe(true);
    expect(wrapper.find('.skeleton-status').exists()).toBe(true);
    expect(wrapper.find('.skeleton-date').exists()).toBe(true);
  });
});
