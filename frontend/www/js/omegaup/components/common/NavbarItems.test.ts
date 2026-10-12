import { mount } from '@vue/test-utils';

import common_NavbarItems from './NavbarItems.vue';

const baseProps = {
  omegaUpLockDown: false,
  inContest: false,
  isLoggedIn: true,
  isReviewer: false,
  isAdmin: false,
  isMainUserIdentity: true,
  navbarSection: '',
  isUnder13User: false,
};

describe('NavbarItems.vue', () => {
  it('Should render the entries from the menu configuration', () => {
    const wrapper = mount(common_NavbarItems, { propsData: baseProps });

    expect(wrapper.find('[data-nav-contests-arena]').exists()).toBe(true);
    expect(wrapper.find('[data-nav-courses-all]').exists()).toBe(true);
    expect(wrapper.find('[data-nav-problems-list]').exists()).toBe(true);
    expect(wrapper.find('a[href="/rank/"]').exists()).toBe(true);
  });

  it('Should hide creation entries for under 13 users', () => {
    const wrapper = mount(common_NavbarItems, {
      propsData: { ...baseProps, isUnder13User: true },
    });

    expect(wrapper.find('[data-nav-contests-create]').exists()).toBe(false);
    expect(wrapper.find('[data-nav-courses-create]').exists()).toBe(false);
  });

  it('Should show the nomination queue only to reviewers', () => {
    const asUser = mount(common_NavbarItems, { propsData: baseProps });
    expect(asUser.find('a[href="/nomination/"]').exists()).toBe(false);

    const asReviewer = mount(common_NavbarItems, {
      propsData: { ...baseProps, isReviewer: true },
    });
    expect(asReviewer.find('a[href="/nomination/"]').exists()).toBe(true);
  });

  it('Should show both create problem links when logged in and only the direct one when logged out', () => {
    const loggedIn = mount(common_NavbarItems, { propsData: baseProps });
    expect(loggedIn.find('a[href="/problem/creator/"]').exists()).toBe(true);
    expect(loggedIn.find('[data-nav-problems-create]').exists()).toBe(true);

    const loggedOut = mount(common_NavbarItems, {
      propsData: { ...baseProps, isLoggedIn: false, isMainUserIdentity: false },
    });
    expect(loggedOut.find('a[href="/problem/creator/"]').exists()).toBe(true);
    expect(loggedOut.find('[data-nav-problems-create]').exists()).toBe(false);
  });
});
