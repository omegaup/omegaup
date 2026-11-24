import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import T from '../../lang';
import { types } from '../../api_types';

import coderOfTheMonth_CodersList from './CodersList.vue';

const CodersList = coderOfTheMonth_CodersList as ComponentOptions<Vue>;

const sampleCoder: types.CoderOfTheMonthList = {
  classname: 'user-rank-unranked',
  country_id: 'MX',
  date: '2024-01-01',
  gravatar_32: 'https://example.com/avatar.png',
  username: 'test_user',
};

describe('CodersList.vue', () => {
  it('Should render coder rows', () => {
    const wrapper = shallowMount(CodersList, {
      propsData: {
        coders: [sampleCoder],
      },
    });

    expect(wrapper.find('table').exists()).toBeTruthy();
    expect(wrapper.text()).toContain(T.codersOfTheMonthUser);
    expect(wrapper.find('img').attributes().src).toBe(sampleCoder.gravatar_32);
    expect(wrapper.find('img').attributes().alt).toBe(sampleCoder.username);
    expect(wrapper.text()).toContain(sampleCoder.date);
  });
});
