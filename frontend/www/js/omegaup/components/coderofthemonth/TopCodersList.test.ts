import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import T from '../../lang';
import { types } from '../../api_types';

import coderOfTheMonth_TopCodersList from './TopCodersList.vue';

const TopCodersList = coderOfTheMonth_TopCodersList as ComponentOptions<Vue>;

const sampleCoder: types.CoderOfTheMonthList = {
  classname: 'user-rank-unranked',
  country_id: 'MX',
  date: '2024-01-01',
  gravatar_32: 'https://example.com/avatar.png',
  username: 'test_user',
};

describe('TopCodersList.vue', () => {
  it('Should render coder rows', () => {
    const wrapper = shallowMount(TopCodersList, {
      propsData: {
        coders: [sampleCoder],
        selectedTab: 'codersOfPreviousMonth',
        isDisabled: false,
      },
    });

    expect(wrapper.find('table').exists()).toBeTruthy();
    expect(wrapper.text()).toContain(T.codersOfTheMonthUser);
    expect(wrapper.find('img').attributes().alt).toBe(sampleCoder.username);
  });

  it('Should show maintainance message when disabled', () => {
    const wrapper = shallowMount(TopCodersList, {
      propsData: {
        coders: [],
        selectedTab: 'codersOfPreviousMonth',
        isDisabled: true,
      },
    });

    expect(wrapper.find('table').exists()).toBeFalsy();
    expect(wrapper.find('.system-in-maintainance').exists()).toBeTruthy();
  });
});
