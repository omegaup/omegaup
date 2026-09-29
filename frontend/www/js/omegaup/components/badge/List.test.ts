import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import badge_List from './List.vue';

// defineComponent() is typed for Vue 2.7/3 interop; @vue/test-utils@1 expects
// ComponentOptions<Vue>. Runtime is correct, assertion removed with test-utils@2.
const List = (badge_List as unknown) as ComponentOptions<Vue>;

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
