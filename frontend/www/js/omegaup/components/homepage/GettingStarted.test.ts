import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';
import T from '../../lang';

import homepage_GettingStarted from './GettingStarted.vue';

const GettingStarted = homepage_GettingStarted as ComponentOptions<Vue>;

describe('GettingStarted.vue', () => {
  it('Should show create links for users 13+', () => {
    const wrapper = shallowMount(GettingStarted, {
      propsData: { isUnder13User: false },
    });

    expect(wrapper.text()).toContain(T.homepageGetStartedTitle);
    expect(wrapper.text()).toContain(T.buttonCreateProblem);
    expect(wrapper.text()).toContain(T.buttonCreateContest);
  });

  it('Should hide create links for under-13 users', () => {
    const wrapper = shallowMount(GettingStarted, {
      propsData: { isUnder13User: true },
    });

    expect(wrapper.text()).toContain(T.homepageGetStartedTitle);
    expect(wrapper.text()).not.toContain(T.buttonCreateProblem);
    expect(wrapper.text()).not.toContain(T.buttonCreateContest);
  });
});
