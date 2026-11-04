import { shallowMount, mount } from '@vue/test-utils';
import Vue, { ComponentOptions } from 'vue';
import user_CompareCard from './CompareCard.vue';
import { types } from '../../api_types';
import T from '../../lang';

const CompareCard = user_CompareCard as ComponentOptions<Vue>;

describe('CompareCard.vue', () => {
  const defaultProfile: types.UserProfileInfo = {
    username: 'testcoder',
    name: 'Test Coder',
    classname: 'user-rank-master',
    country: 'Mexico',
    country_id: 'mx',
    school: 'Test School',
    is_private: false,
    hide_problem_tags: false,
    is_own_profile: false,
    programming_languages: {},
    rankinfo: {
      rank: 42,
      author_ranking: 5,
    },
  };

  it('Should render profile info, rank, solved problems, and contests count', () => {
    const wrapper = mount(CompareCard, {
      propsData: {
        profile: defaultProfile,
        solvedProblemsCount: 150,
        contestsCount: 20,
        comparisonClass: 'compare-winner',
      },
    });

    expect(wrapper.text()).toContain('testcoder');
    expect(wrapper.text()).toContain('Test Coder');
    expect(wrapper.text()).toContain('#42');
    expect(wrapper.text()).toContain('150');
    expect(wrapper.text()).toContain('20');
    expect(wrapper.text()).toContain('#5');
    expect(wrapper.text()).toContain('Mexico');
    expect(wrapper.text()).toContain('Test School');
    expect(wrapper.classes()).toContain('compare-winner');
  });

  it('Should display private profile notice when profile is private', () => {
    const wrapper = shallowMount(CompareCard, {
      propsData: {
        profile: {
          ...defaultProfile,
          is_private: true,
        },
        solvedProblemsCount: null,
        contestsCount: null,
      },
    });

    expect(wrapper.find('.alert-info').exists()).toBe(true);
    expect(wrapper.text()).toContain(T.profileIsPrivate);
    expect(wrapper.text()).toContain(T.wordsNotApplicable);
  });
});
