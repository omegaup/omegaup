import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';
import user_Username from './Username.vue';

describe('Username.vue', () => {
  it('Should display username only when name is null', () => {
    const wrapper = shallowMount(user_Username as ComponentOptions<Vue>, {
      propsData: {
        username: 'alice',
      },
    });

    expect(wrapper.text()).toContain('alice');
    expect(wrapper.find('a').exists()).toBe(false);
  });

  it('Should display name and username when name is provided', () => {
    const wrapper = shallowMount(user_Username as ComponentOptions<Vue>, {
      propsData: {
        username: 'alice',
        name: 'Alice Wonder',
      },
    });

    expect(wrapper.text()).toContain('Alice Wonder (alice)');
  });

  it('Should render profile link when linkify is true', () => {
    const wrapper = shallowMount(user_Username as ComponentOptions<Vue>, {
      propsData: {
        username: 'alice',
        linkify: true,
      },
    });

    const link = wrapper.find('a');
    expect(link.exists()).toBe(true);
    expect(link.attributes('href')).toBe('/profile/alice/');
  });

  it('Should emit click event when emitClickEvent is true', async () => {
    const wrapper = shallowMount(user_Username as ComponentOptions<Vue>, {
      propsData: {
        username: 'alice',
        linkify: true,
        emitClickEvent: true,
        href: '#user-modal',
      },
    });

    const link = wrapper.find('a');
    expect(link.attributes('href')).toBe('#user-modal');
    await link.trigger('click');

    expect(wrapper.emitted('click')).toBeDefined();
    expect(wrapper.emitted('click')?.[0]).toEqual(['alice']);
  });

  it('Should render country flag when country is provided', () => {
    const wrapper = shallowMount(user_Username as ComponentOptions<Vue>, {
      propsData: {
        username: 'alice',
        country: 'mx',
      },
    });

    const flag = wrapper.findComponent({ name: 'CountryFlag' });
    expect(flag.exists()).toBe(true);
  });
});
