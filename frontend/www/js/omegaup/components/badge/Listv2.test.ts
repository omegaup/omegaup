import { shallowMount, mount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';
import badge_list from './Listv2.vue';

describe('Listv2.vue', () => {
  it('Should display badges link', () => {
    const badgeAlias = 'contestManager';
    const wrapper = shallowMount(badge_list, {
      propsData: {
        showAllBadgesLink: true,
        allBadges: <Set<string>>new Set([badgeAlias]),
        visitorBadges: <Set<string>>new Set([badgeAlias]),
      },
    });
    expect(wrapper.find('.badges-link').text()).toBe(T.wordsBadgesSeeAll);
  });
});
