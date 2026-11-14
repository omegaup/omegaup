import { mount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';

import notification_Notification from './Notification.vue';

describe('Notification.vue', () => {
  const Component = notification_Notification as ComponentOptions<Vue>;

  it('Should handle empty url in contents body', () => {
    const courseName = 'Curso de prueba';
    const wrapper = mount(Component, {
      propsData: {
        notification: {
          contents: {
            type: 'course-registration-rejected',
            body: {
              localizationString: 'notificationCourseRegistrationRejected',
              localizationParams: {
                courseName,
              },
              url: '',
              iconUrl: '/media/info.png',
            },
          },
          timestamp: new Date(),
        },
      },
    });

    expect(wrapper.find('button.close').text()).toBe('❌');
    expect(wrapper.text()).toEqual(expect.stringContaining(courseName));
  });

  it('Should handle click on notification with url', async () => {
    const testUrl = 'https://omegaup.com/arena/test';
    const notification = {
      contents: {
        type: 'general_notification',
        message: 'Notification with url',
        url: testUrl,
      },
      timestamp: new Date(),
    };
    const wrapper = mount(Component, {
      propsData: {
        notification,
      },
    });

    await wrapper.find('.notification-link').trigger('click');
    expect(wrapper.emitted('remove')).toBeTruthy();
    expect(wrapper.emitted('remove')?.[0]).toEqual([notification, testUrl]);
  });
});
