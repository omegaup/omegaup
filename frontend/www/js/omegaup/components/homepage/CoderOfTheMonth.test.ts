import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';
import { types } from '../../api_types';
import T from '../../lang';

import homepage_CoderOfTheMonth from './CoderOfTheMonth.vue';

const CoderOfTheMonth = homepage_CoderOfTheMonth as ComponentOptions<Vue>;

describe('CoderOfTheMonth.vue', () => {
  const coderOfTheMonth = {
    username: 'omegaUp',
    classname: 'user-rank-master',
    gravatar_92: 'https://example.com/avatar.png',
    name: 'omegaUp User',
    school: 'Test School',
    school_id: 1,
    state: 'CDMX',
    country: 'Mexico',
    country_id: 'MX',
    is_private: false,
  } as types.UserProfile;

  it('Should render all-category coder of the month', () => {
    const wrapper = shallowMount(CoderOfTheMonth, {
      propsData: {
        category: 'all',
        coderOfTheMonth,
      },
    });

    expect(wrapper.text()).toContain(T.coderOfTheMonth);
    expect(wrapper.text()).toContain(T.coderOfTheMonthFullList);
    expect(wrapper.find('a[href="/coderofthemonth/"]').exists()).toBe(true);
  });

  it('Should render female-category coder of the month', () => {
    const wrapper = shallowMount(CoderOfTheMonth, {
      propsData: {
        category: 'female',
        coderOfTheMonth,
      },
    });

    expect(wrapper.text()).toContain(T.coderOfTheMonthFemale);
    expect(wrapper.find('a[href="/coderofthemonth/female/"]').exists()).toBe(
      true,
    );
  });
});
