import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import homepage_Sponsors from './Sponsors.vue';

const Sponsors = homepage_Sponsors as ComponentOptions<Vue>;

describe('Sponsors.vue', () => {
  it('Should render sponsor logos', () => {
    const wrapper = shallowMount(Sponsors, {
      propsData: {
        title: 'Sponsors',
        logos: [
          {
            class: 'img-fluid',
            src: '/media/homepage/airbnb_logo.svg',
            alt: 'airbnbLogo',
            href: 'https://example.com',
          },
        ],
      },
    });

    expect(wrapper.text()).toContain('Sponsors');
    expect(wrapper.find('img[alt="airbnbLogo"]').exists()).toBe(true);
    expect(wrapper.find('a[href="https://example.com"]').exists()).toBe(true);
  });
});
