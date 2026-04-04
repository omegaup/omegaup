import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import badge_List from './List.vue';

describe('List.vue', () => {
  it('Should display badges link', () => {
    const badgeAlias = 'contestManager';
    const wrapper = shallowMount(badge_List, {
      propsData: {
        showAllBadgesLink: true,
        allBadges: new Set([badgeAlias]) as Set<string>,
        visitorBadges: new Set([badgeAlias]) as Set<string>,
      },
    });
    expect(wrapper.find('.badges-link').text()).toBe(T.wordsBadgesSeeAll);
  });
});
