import { mount } from '@vue/test-utils';
import { types } from '../../api_types';
import T from '../../lang';

import notification_Clarifications from './Clarifications.vue';

describe('Clarifications.vue', () => {
  const clarifications = [
    {
      answer: 'yes',
      author: 'user',
      author_classname: 'user-rank-unranked',
      clarification_id: 1,
      message: 'Is this the question?',
      problem_alias: 'problem',
      public: false,
      time: new Date(0),
    },
    {
      answer: 'yes',
      author: 'user',
      author_classname: 'user-rank-unranked',
      clarification_id: 2,
      message: 'Is this another question?',
      problem_alias: 'problem',
      public: false,
      time: new Date(0),
    },
  ] as types.Clarification[];
  it('Should handle empty notifications list for clarifications', () => {
    const wrapper = mount(notification_Clarifications, {
      propsData: {
        clarifications: [],
      },
    });

    expect(wrapper.text()).toBe(T.notificationsNoNewNotifications);
  });

  it('Should handle mark all as read button for clarifications', async () => {
    const wrapper = mount(notification_Clarifications, {
      propsData: {
        clarifications,
      },
    });

    expect(wrapper.find('li[data-mark-all-as-read-button] a').text()).toBe(
      T.notificationsMarkAllAsRead,
    );
    await wrapper.find('li[data-mark-all-as-read-button] a').trigger('click');
    expect(wrapper.text()).toBe(T.notificationsNoNewNotifications);
  });

  it('Should handle close button for a certain clarification', async () => {
    const wrapper = mount(notification_Clarifications, {
      propsData: {
        clarifications,
      },
    });

    expect(wrapper.find('li[data-mark-all-as-read-button] a').text()).toBe(
      T.notificationsMarkAllAsRead,
    );
    await wrapper
      .find('div[data-clarification="1"] button[class="close"]')
      .trigger('click');
    // There is only one notification, so "Mark as all read" button does not appear
    expect(wrapper.text()).not.toContain(T.notificationsMarkAllAsRead);
  });
});
