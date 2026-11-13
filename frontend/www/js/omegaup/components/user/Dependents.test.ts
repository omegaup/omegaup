import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';
import user_Dependents from './Dependents.vue';
import type { types } from '../../api_types';
import T from '../../lang';

describe('Dependents.vue', () => {
  it('Should render empty state message when dependents array is empty', () => {
    const wrapper = shallowMount(user_Dependents as ComponentOptions<Vue>, {
      propsData: {
        dependents: [],
      },
    });

    expect(wrapper.find('h5.card-header').text()).toBe(T.dependentsTitle);
    expect(wrapper.find('.empty-category').text()).toBe(T.dependentsEmptyState);
  });

  it('Should render dependent rows with verified status', () => {
    const dependents: types.UserDependent[] = [
      {
        username: 'child_user',
        classname: 'user-rank-unranked',
        parent_verified: true,
      },
    ];

    const wrapper = shallowMount(user_Dependents as ComponentOptions<Vue>, {
      propsData: {
        dependents,
      },
    });

    expect(wrapper.find('.empty-category').exists()).toBe(false);
    expect(wrapper.find('small').text()).toBe(T.dependentsVerified);
    expect(wrapper.find('.background-success').exists()).toBe(true);
  });

  it('Should render warning banner when verification deadline is in future', () => {
    const futureDate = new Date();
    futureDate.setDate(futureDate.getDate() + 5);

    const dependents: types.UserDependent[] = [
      {
        username: 'pending_child',
        classname: 'user-rank-unranked',
        parent_verified: false,
        parent_email_verification_deadline: futureDate,
      },
    ];

    const wrapper = shallowMount(user_Dependents as ComponentOptions<Vue>, {
      propsData: {
        dependents,
      },
    });

    expect(wrapper.find('.background-warning').exists()).toBe(true);
    expect(wrapper.find('.text-black').exists()).toBe(true);
  });

  it('Should render blocked message when deadline is expired', () => {
    const pastDate = new Date();
    pastDate.setDate(pastDate.getDate() - 2);

    const dependents: types.UserDependent[] = [
      {
        username: 'blocked_child',
        classname: 'user-rank-unranked',
        parent_verified: false,
        parent_email_verification_deadline: pastDate,
      },
    ];

    const wrapper = shallowMount(user_Dependents as ComponentOptions<Vue>, {
      propsData: {
        dependents,
      },
    });

    expect(wrapper.find('.background-secondary').exists()).toBe(true);
    expect(wrapper.find('small').text()).toBe(T.dependentsBlockedMessage);
  });
});
