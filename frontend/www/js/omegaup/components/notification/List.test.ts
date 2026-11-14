import { mount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';
import type { types } from '../../api_types';
import T from '../../lang';

import notification_List from './List.vue';

describe('List.vue', () => {
  const Component = notification_List as ComponentOptions<Vue>;

  const sampleNotifications: types.Notification[] = [
    {
      notification_id: 1,
      contents: {
        type: 'general_notification',
        message: 'Test notification 1',
        url: 'https://omegaup.com',
      },
      timestamp: new Date(0),
    },
    {
      notification_id: 2,
      contents: {
        type: 'general_notification',
        message: 'Test notification 2',
      },
      timestamp: new Date(0),
    },
  ];

  it('Should handle empty notifications list', () => {
    const wrapper = mount(Component, {
      propsData: {
        notifications: [],
      },
    });

    expect(wrapper.text()).toContain(T.notificationsNoNewNotifications);
    expect(wrapper.find('.count-badge').attributes('style')).toBe(
      'display: none;',
    );
  });

  it('Should display notification count badge and mark all as read button when notifications exist', async () => {
    const wrapper = mount(Component, {
      propsData: {
        notifications: sampleNotifications,
      },
    });

    expect(wrapper.find('.count-badge').text()).toBe('2');
    expect(wrapper.text()).toContain(T.notificationsMarkAllAsRead);

    await wrapper.find('a.dropdown-item').trigger('click');
    expect(wrapper.emitted('read')).toBeTruthy();
    expect(wrapper.emitted('read')?.[0]).toEqual([sampleNotifications, null]);
  });

  it('Should emit read when a single notification is removed', () => {
    const wrapper = mount(Component, {
      propsData: {
        notifications: sampleNotifications,
      },
    });

    const singleNotification = wrapper.findComponent({
      name: 'Notification',
    });
    singleNotification.vm.$emit(
      'remove',
      sampleNotifications[0],
      'https://omegaup.com',
    );

    expect(wrapper.emitted('read')).toBeTruthy();
    expect(wrapper.emitted('read')?.[0]).toEqual([
      [sampleNotifications[0]],
      'https://omegaup.com',
    ]);
  });
});
