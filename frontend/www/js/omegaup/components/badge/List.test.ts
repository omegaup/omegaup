import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import T from '../../lang';
import badge_List from './List.vue';

const List = badge_List as ComponentOptions<Vue>;

describe('List.vue', () => {
  it('Should display badges link', () => {
    const badgeAlias = 'contestManager';
    const wrapper = shallowMount(List, {
      propsData: {
        showAllBadgesLink: true,
        allBadges: new Set([badgeAlias]) as Set<string>,
        visitorBadges: new Set([badgeAlias]) as Set<string>,
      },
    });
    expect(wrapper.find('.badges-link').text()).toBe(T.wordsBadgesSeeAll);
  });
});
