import { shallowMount, mount } from '@vue/test-utils';
import { types } from '../../api_types';
import T from '../../lang';

import user_SidebarMainInfo, { urlMapping } from './SidebarMainInfo.vue';

const profile: types.UserProfileInfo = {
  country: 'Mexico',
  country_id: 'MX',
  classname: 'user-rank-master',
  username: 'omegaup',
  hide_problem_tags: false,
  is_private: false,
  preferred_language: 'py2',
  programming_languages: {
    py2: 'python2',
  },
  rankinfo: {
    name: 'Test',
    problems_solved: 2,
    rank: 0,
  },
  is_own_profile: true,
  gravatar_92:
    'https://secure.gravatar.com/avatar/307aeed2f8a75f6fe407411671e3ca87?s=51',
};

const data: types.ExtraProfileDetails = {
  badges: [],
  contests: {},
  createdProblems: [],
  createdContests: [],
  createdCourses: [],
  ownedBadges: [],
  bookmarkedProblems: [],
  solvedProblems: [
    {
      accepted: 1,
      alias: 'alias1',
      difficulty: 0,
      submissions: 2,
      title: 'title',
      quality_seal: false,
    },
    {
      accepted: 1,
      alias: 'alias2',
      difficulty: 1,
      submissions: 3,
      title: 'title2',
      quality_seal: false,
    },
    {
      accepted: 1,
      alias: 'alias3',
      difficulty: 2,
      submissions: 5,
      title: 'title3',
      quality_seal: false,
    },
  ],
  stats: [],
  unsolvedProblems: [],
  hasPassword: true,
};

const rankingMapping: { classname: string; rank: string }[] = [
  { classname: 'user-rank-beginner', rank: T.profileRankBeginner },
  { classname: 'user-rank-specialist', rank: T.profileRankSpecialist },
  { classname: 'user-rank-expert', rank: T.profileRankExpert },
  { classname: 'user-rank-master', rank: T.profileRankMaster },
  {
    classname: 'user-rank-international-master',
    rank: T.profileRankInternationalMaster,
  },
];

describe('SidebarMainInfo.vue', () => {
  it('Should display visible buttons', () => {
    const wrapper = shallowMount(user_SidebarMainInfo, {
      propsData: { profile, data },
    });
    for (const url of urlMapping.filter(
      (url: { key: string; title: string; visible: boolean }) => url.visible,
    )) {
      const urlSelector = `a[href="/profile/#${url.key}"]`;
      expect(wrapper.find(urlSelector).exists()).toBeTruthy();
      expect(wrapper.find(urlSelector).text()).toBe(url.title);
    }
    for (const url of urlMapping.filter(
      (url: { key: string; title: string; visible: boolean }) => !url.visible,
    )) {
      const urlSelector = `a[href="/profile/#${url.key}"]`;
      expect(wrapper.find(urlSelector).exists()).toBeFalsy();
    }
  });

  it('Should display number of solved problems', () => {
    const wrapper = shallowMount(user_SidebarMainInfo, {
      propsData: { profile, data },
    });
    expect(wrapper.find('div[data-solved-problems]>h4').text()).toBe('3');
  });

  it('Should not display buttons for a different user profile', () => {
    const wrapper = shallowMount(user_SidebarMainInfo, {
      propsData: {
        profile: { ...profile, ...{ is_own_profile: false } },
        data,
      },
    });

    for (const url of urlMapping) {
      const urlSelector = `a[href="/profile/#${url.key}"]`;
      expect(wrapper.find(urlSelector).exists()).toBeFalsy();
    }
  });

  it('Should display Add password button when user does not have password', () => {
    const wrapper = shallowMount(user_SidebarMainInfo, {
      propsData: {
        profile,
        data: { ...data, ...{ hasPassword: false } },
      },
    });

    expect(
      wrapper.find('a[href="/profile/#add-password"]').exists(),
    ).toBeTruthy();
    expect(
      wrapper.find('a[href="/profile/#change-password"]').exists(),
    ).toBeFalsy();
  });
});

describe.each(rankingMapping)(`A user:`, (rank) => {
  it(`whose classname is ${rank.classname} should have rank ${rank.rank}`, () => {
    const wrapper = shallowMount(user_SidebarMainInfo, {
      propsData: {
        profile: { ...profile, ...{ classname: rank.classname } },
        data,
      },
    });
    expect(wrapper.text()).toContain(rank.rank);
  });
});

describe('Profile Picture Edit Feature', () => {
  it('Should display profile edit overlay when viewing own profile', () => {
    const wrapper = mount(user_SidebarMainInfo, {
      propsData: { profile, data },
    });

    const profileContainer = wrapper.find('.profile-picture-container');
    const editOverlay = wrapper.find('.profile-edit-overlay');
    const pencilIcon = wrapper.find('.edit-icon');

    expect(profileContainer.exists()).toBeTruthy();
    expect(editOverlay.exists()).toBeTruthy();
    expect(pencilIcon.exists()).toBeTruthy();
  });

  it('Should not display profile edit overlay when viewing other user profile', () => {
    const otherUserProfile = { ...profile, is_own_profile: false };
    const wrapper = mount(user_SidebarMainInfo, {
      propsData: { profile: otherUserProfile, data },
    });

    const profileContainer = wrapper.find('.profile-picture-container');
    const editOverlay = wrapper.find('.profile-edit-overlay');

    expect(profileContainer.exists()).toBeFalsy();
    expect(editOverlay.exists()).toBeFalsy();
  });

  it('Should have correct profile picture source', () => {
    const wrapper = mount(user_SidebarMainInfo, {
      propsData: { profile, data },
    });

    const profilePicture = wrapper.find('.profile-picture');
    expect(profilePicture.exists()).toBeTruthy();
    expect(profilePicture.attributes('src')).toBe(profile.gravatar_92);
    expect(profilePicture.attributes('class')).toContain('rounded-circle');
  });

  it('Should have edit overlay as anchor link to Gravatar', () => {
    const wrapper = mount(user_SidebarMainInfo, {
      propsData: { profile, data },
    });

    const editOverlay = wrapper.find('.profile-edit-overlay');
    expect(editOverlay.element.tagName).toBe('A');
    expect(editOverlay.attributes('href')).toBe('https://www.gravatar.com');
    expect(editOverlay.attributes('target')).toBe('_blank');
  });

  it('Should not have click handler on profile picture', () => {
    const wrapper = mount(user_SidebarMainInfo, {
      propsData: { profile, data },
    });

    const profilePicture = wrapper.find('.profile-picture');
    expect(profilePicture.exists()).toBeTruthy();
    // Verify there is no click handler on the image itself
    expect(profilePicture.element.onclick).toBeNull();
  });

  it('Should have correct tooltip text', () => {
    const wrapper = mount(user_SidebarMainInfo, {
      propsData: { profile, data },
    });

    const editOverlay = wrapper.find('.profile-edit-overlay');
    expect(editOverlay.attributes('title')).toBe(T.userEditProfileImage);
  });

  it('Should have correct CSS classes for styling', () => {
    const wrapper = mount(user_SidebarMainInfo, {
      propsData: { profile, data },
    });

    const profileContainer = wrapper.find('.profile-picture-container');
    const editOverlay = wrapper.find('.profile-edit-overlay');
    const pencilIcon = wrapper.find('.edit-icon');

    // Check container classes
    expect(profileContainer.classes()).toContain('profile-picture-container');

    // Check overlay classes
    expect(editOverlay.classes()).toContain('profile-edit-overlay');

    // Check icon classes
    expect(pencilIcon.classes()).toContain('edit-icon');
  });

  it('Should display FontAwesome pencil icon', () => {
    const wrapper = mount(user_SidebarMainInfo, {
      propsData: { profile, data },
    });

    const editIconContainer = wrapper.find('.edit-icon');
    const faIcon = wrapper.find('i.fa.fa-pencil-alt');

    expect(editIconContainer.exists()).toBeTruthy();
    expect(faIcon.exists()).toBeTruthy();
    expect(faIcon.classes()).toContain('fa');
    expect(faIcon.classes()).toContain('fa-pencil-alt');
  });

  it('Should not show edit overlay for non-own profile even if clicked', async () => {
    const otherUserProfile = { ...profile, is_own_profile: false };
    const wrapper = mount(user_SidebarMainInfo, {
      propsData: { profile: otherUserProfile, data },
    });

    // Should not have the edit elements
    expect(wrapper.find('.profile-edit-overlay').exists()).toBeFalsy();
    expect(wrapper.find('.profile-picture-container').exists()).toBeFalsy();

    // Should have regular profile picture - check all rounded-circle elements
    const regularProfilePictures = wrapper.findAll('.rounded-circle');
    let foundGravatarPicture = false;

    for (let i = 0; i < regularProfilePictures.length; i++) {
      const img = regularProfilePictures.at(i);
      if (img.attributes('src') === profile.gravatar_92) {
        foundGravatarPicture = true;
        break;
      }
    }

    expect(foundGravatarPicture).toBeTruthy();
  });
});
