import { mount, shallowMount } from '@vue/test-utils';
import common_EmptyState from './EmptyState.vue';

describe('EmptyState.vue', () => {
  it('Should display title and description', () => {
    const wrapper = shallowMount(common_EmptyState, {
      propsData: {
        title: 'No items found',
        description: 'Try adding a new item to get started.',
      },
    });

    expect(wrapper.text()).toContain('No items found');
    expect(wrapper.text()).toContain('Try adding a new item to get started.');
  });

  it('Should render an action link when buttonLink is provided', () => {
    const wrapper = shallowMount(common_EmptyState, {
      propsData: {
        title: 'Empty List',
        buttonText: 'Create New',
        buttonLink: '/new',
      },
    });

    const link = wrapper.find('a.btn-primary');
    expect(link.exists()).toBe(true);
    expect(link.attributes('href')).toBe('/new');
    expect(link.text()).toBe('Create New');
  });

  it('Should render an action button and emit action event on click when buttonLink is not provided', async () => {
    const wrapper = shallowMount(common_EmptyState, {
      propsData: {
        title: 'Empty List',
        buttonText: 'Click Me',
      },
    });

    const button = wrapper.find('button.btn-primary');
    expect(button.exists()).toBe(true);
    expect(button.text()).toBe('Click Me');

    await button.trigger('click');
    expect(wrapper.emitted('action')).toBeTruthy();
  });

  it('Should render custom content in the action slot', () => {
    const wrapper = mount(common_EmptyState, {
      propsData: {
        title: 'Custom Action',
      },
      slots: {
        action: '<button id="custom-btn">Custom Action Button</button>',
      },
    });

    expect(wrapper.find('#custom-btn').exists()).toBe(true);
    expect(wrapper.find('#custom-btn').text()).toBe('Custom Action Button');
  });
});
