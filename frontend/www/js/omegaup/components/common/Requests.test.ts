import { mount, shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';

import common_Requests from './Requests.vue';

describe('Requests.vue', () => {
  const data: types.IdentityRequest[] = [
    {
      accepted: false,
      admin: {
        username: 'test_user',
      },
      last_update: new Date(),
      request_time: new Date(),
      username: 'test_user',
      classname: 'user-rank-unranked',
    },
  ];

  const propsData = {
    data,
    textAddParticipant: T.contestAdduserAddContestant,
  };

  it('Should handle initital props', () => {
    const wrapper = shallowMount(common_Requests, { propsData });

    expect(wrapper.text()).toContain(T.contestAdduserAddContestant);
  });

  it('Should handle deny request event', async () => {
    const wrapper = shallowMount(common_Requests, { propsData });

    expect(wrapper.vm.showFeedbackModal).toBe(false);
    await wrapper.find('button.text-danger').trigger('click');
    expect(wrapper.vm.showFeedbackModal).toBe(true);

    const feedbackModal = wrapper.find('b-modal-stub');
    expect(feedbackModal.exists()).toBe(true);

    wrapper.vm.resolutionText = 'Hello';
    feedbackModal.vm.$emit('ok');
    expect(wrapper.emitted('deny-request')).toBeDefined();
    expect(wrapper.emitted('deny-request')).toEqual([['test_user', 'Hello']]);
  });

  it('Should handle accept request event', async () => {
    const wrapper = shallowMount(common_Requests, { propsData });

    await wrapper.find('button.text-success').trigger('click');
    expect(wrapper.emitted('accept-request')).toBeDefined();
    expect(wrapper.emitted('accept-request')).toEqual([
      [{ username: 'test_user' }],
    ]);
  });

  it('Should handle the whole list of requests', async () => {
    const wrapper = mount(common_Requests, {
      propsData: {
        data: [
          {
            accepted: false,
            admin: {
              username: 'test_user',
            },
            last_update: new Date(),
            request_time: new Date(),
            username: 'test_user_1',
            classname: 'user-rank-unranked',
          },
          {
            accepted: true,
            admin: {
              username: 'test_user',
            },
            last_update: new Date(),
            request_time: new Date(),
            username: 'test_user_2',
            classname: 'user-rank-unranked',
          },
        ],
        textAddParticipant: T.wordsAddStudent,
      },
    });

    expect(wrapper.find('table tbody').text()).toContain('test_user_1');
    expect(wrapper.find('table tbody').text()).not.toContain('test_user_2');

    await wrapper.find('div[data-requests] input').trigger('click');
    expect(wrapper.find('table tbody').text()).toContain('test_user_2');
  });
});
