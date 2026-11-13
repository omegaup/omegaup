import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';
import { types } from '../../api_types';

import homepage_Carousel from './Carousel.vue';

const Carousel = homepage_Carousel as ComponentOptions<Vue>;

describe('Carousel.vue', () => {
  it('Should render slides from carousel items', () => {
    const carouselItems: types.CarouselItem[] = [
      {
        carousel_item_id: 1,
        title: JSON.stringify({
          en: 'Welcome',
          es: 'Bienvenido',
          pt: 'Bem-vindo',
        }),
        excerpt: JSON.stringify({
          en: 'Start coding',
          es: 'Empieza a programar',
          pt: 'Comece a programar',
        }),
        image_url: '/media/homepage/slide.svg',
        link: '/problem/',
        button_title: JSON.stringify({
          en: 'Go',
          es: 'Ir',
          pt: 'Ir',
        }),
        is_active: true,
      },
    ];

    const wrapper = shallowMount(Carousel, {
      propsData: { carouselItems },
    });

    expect(wrapper.findAll('omegaup-homepage-slide-stub').length).toBe(1);
    expect(wrapper.find('.carousel').exists()).toBe(true);
  });

  it('Should skip items without content or image', () => {
    const carouselItems: types.CarouselItem[] = [
      {
        carousel_item_id: 2,
        title: '',
        excerpt: '',
        image_url: '',
        link: '',
        button_title: '',
        is_active: true,
      },
    ];

    const wrapper = shallowMount(Carousel, {
      propsData: { carouselItems },
    });

    expect(wrapper.findAll('omegaup-homepage-slide-stub').length).toBe(0);
  });
});
