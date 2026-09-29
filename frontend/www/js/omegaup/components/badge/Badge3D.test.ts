import { mount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import badge_Badge3D from './Badge3D.vue';

const Badge3D = (badge_Badge3D as unknown) as ComponentOptions<Vue>;

describe('Badge3D.vue', () => {
  it('Should render the slot content', () => {
    const wrapper = mount(Badge3D, {
      slots: {
        default: '<img src="/media/dist/badges/contestManager.svg" alt="x" />',
      },
    });

    expect(wrapper.find('.badge-3d').exists()).toBeTruthy();
    expect(wrapper.findAll('img').length).toBeGreaterThan(0);
  });

  it('Should clear transform on mouse leave', async () => {
    const wrapper = mount(Badge3D, {
      slots: {
        default: '<span class="slot-content">badge</span>',
      },
    });

    const badgeEl = wrapper.find('.badge-3d').element as HTMLElement;
    badgeEl.style.transform = 'rotateX(10deg)';

    await wrapper.find('.badge-3d-wrapper').trigger('mouseleave');

    expect(badgeEl).toHaveStyle({ transform: '' });
  });
});
