import { mount } from '@vue/test-utils';

import { types } from '../../api_types';

import user_Profile from './Profile.vue';

const profile: types.UserProfileInfo = {
  country: 'Mexico',
  country_id: 'MX',
  classname: 'user-rank-unranked',
  username: 'test_user_0',
  hide_problem_tags: false,
  is_private: false,
  preferred_language: 'py3',
  programming_languages: { py3: 'python3' },
  rankinfo: {
    name: 'Test User',
    problems_solved: 0,
    rank: 0,
  },
  is_own_profile: true,
  gravatar_92: 'https://secure.gravatar.com/avatar/x?s=92',
};

function mountProfile(viewProfileSelectedTab: string | null = null) {
  return mount(user_Profile, {
    propsData: {
      profile,
      data: null,
      profileBadges: new Set<string>(),
      visitorBadges: new Set<string>(),
      identities: [],
      apiTokens: [],
      countries: [],
      programmingLanguages: {},
      hasPassword: true,
      selectedTab: 'view-profile',
      viewProfileSelectedTab,
      searchResultSchools: [],
      availableYears: [],
      isLoggedIn: true,
      followStatus: {
        followersCount: 0,
        followingCount: 1,
        isFollowing: false,
      },
      followers: { users: [], totalRows: 0, page: 1 },
      following: { users: [], totalRows: 0, page: 1 },
      followListPageSize: 20,
    },
  });
}

describe('follow list navigation', () => {
  it('Should show the follow list when the sidebar asks for it', async () => {
    const wrapper = mountProfile();

    // The profile tabs are visible to begin with.
    expect(wrapper.find('[data-profile-navtabs]').exists()).toBe(true);
    expect(wrapper.find('[data-follow-list-pane]').exists()).toBe(false);

    // Clicking a count in the sidebar bubbles up to the entrypoint, which
    // feeds the choice back down through this prop.
    await wrapper.setProps({ viewProfileSelectedTab: 'following' });

    expect(wrapper.find('[data-follow-list-pane]').exists()).toBe(true);
    expect(wrapper.find('[data-profile-navtabs]').exists()).toBe(false);
  });

  it('Should bubble show-list when a tab inside the list is clicked', async () => {
    const wrapper = mountProfile('following');

    await wrapper.find('[data-follow-list-tab-followers]').trigger('click');

    expect(wrapper.emitted('show-list')?.[0]?.[0]).toBe('followers');
  });

  it('Should tell the entrypoint when the back button leaves the list', async () => {
    const wrapper = mountProfile('followers');

    await wrapper.find('[data-follow-list-back]').trigger('click');

    const updates = wrapper.emitted('update:viewProfileSelectedTab') ?? [];
    expect(updates[updates.length - 1]?.[0]).toBe('badges');
    expect(wrapper.find('[data-follow-list-pane]').exists()).toBe(false);
  });
});
