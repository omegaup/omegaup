import { mount } from '@vue/test-utils';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faVideo } from '@fortawesome/free-solid-svg-icons';

import common_NavbarItem from './NavbarItem.vue';

library.add(faVideo);

describe('NavbarItem.vue', () => {
  it('Should render a simple link with only a title', () => {
    const wrapper = mount(common_NavbarItem, {
      propsData: {
        title: 'Plain Item',
        href: '/plain/',
      },
    });

    const anchor = wrapper.find('a');
    expect(anchor.exists()).toBe(true);
    expect(anchor.attributes('href')).toBe('/plain/');
    expect(anchor.text()).toBe('Plain Item');
    expect(anchor.classes()).toContain('dropdown-item');
    expect(anchor.classes()).not.toContain('help-dropdown-item');
    expect(wrapper.find('.help-item-icon').exists()).toBe(false);
    expect(wrapper.find('small').exists()).toBe(false);
  });

  it('Should render an icon, title and description when provided', () => {
    const wrapper = mount(common_NavbarItem, {
      propsData: {
        title: 'Tutorials',
        description: 'Watch the videos',
        icon: ['fas', 'video'],
        href: 'https://youtube.example/',
        target: '_blank',
        rel: 'noopener noreferrer',
      },
    });

    const anchor = wrapper.find('a');
    expect(anchor.attributes('href')).toBe('https://youtube.example/');
    expect(anchor.attributes('target')).toBe('_blank');
    expect(anchor.attributes('rel')).toBe('noopener noreferrer');
    expect(anchor.classes()).toContain('help-dropdown-item');
    expect(wrapper.find('.help-item-icon').exists()).toBe(true);
    expect(wrapper.text()).toContain('Tutorials');
    expect(wrapper.find('small').text()).toBe('Watch the videos');
  });

  it('Should accept a plain string icon name', () => {
    const wrapper = mount(common_NavbarItem, {
      propsData: {
        title: 'Tutorials',
        icon: 'video',
        href: '/videos/',
      },
    });

    expect(wrapper.find('.help-item-icon').exists()).toBe(true);
  });

  it('Should mark links that leave the current host', () => {
    const wrapper = mount(common_NavbarItem, {
      propsData: {
        title: 'Discord',
        href: 'https://discord.example/invite',
      },
    });

    expect(wrapper.find('.external-link-icon').exists()).toBe(true);
  });

  it('Should not mark links within the current host', () => {
    const wrapper = mount(common_NavbarItem, {
      propsData: {
        title: 'Statement editor',
        href: '/problem/statement/',
      },
    });

    expect(wrapper.find('.external-link-icon').exists()).toBe(false);
  });
});
