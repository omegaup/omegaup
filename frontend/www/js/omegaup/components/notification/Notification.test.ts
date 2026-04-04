import { mount } from '@vue/test-utils';

import notification_Notification from './Notification.vue';

describe('Notification.vue', () => {
  it('Should handle empty url in contents body', () => {
    const courseName = 'Curso de prueba';
    const wrapper = mount(notification_Notification, {
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
});
