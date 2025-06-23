import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import user_Preferences_Edit from './PreferencesEdit.vue';
import each from 'jest-each';
import T from '../../lang';

const profile: types.UserProfileInfo = {
  name: 'omegaUp admin',
  classname: 'user-rank-unranked',
  email: 'admin@omegaup.com',
  username: 'omegaup',
  verified: true,
  hide_problem_tags: false,
  is_private: false,
  preferred_language: 'py2',
  programming_languages: {
    py2: 'python2',
    rb: 'ruby',
  },
  rankinfo: {
    name: 'Test',
    problems_solved: 2,
    rank: 1,
  },
  is_own_profile: true,
  birth_date: new Date('1999-09-09'),
  locale: 'es',
  gender: 'decline',
  has_competitive_objective: true,
  has_learning_objective: true,
  has_scholar_objective: false,
  has_teaching_objective: false,
};

describe('PreferencesEdit.vue', () => {
  it('Should display user email', () => {
    const wrapper = shallowMount(user_Preferences_Edit, {
      propsData: { profile },
    });
    expect(wrapper.find('[data-email]').text()).toContain('admin@omegaup.com');
  });

  it('Should emit user update preferences', async () => {
    const wrapper = shallowMount(user_Preferences_Edit, {
      propsData: { profile },
    });

    await wrapper
      .find('select[data-locale]')
      .find('option[value="en"]')
      .setSelected();
    await wrapper
      .find('select[data-preferred-language]')
      .find('option[value="rb"]')
      .setSelected();
    await wrapper.find('input[data-is-private]').setChecked();
    await wrapper.find('input[data-hide-problem-tags]').setChecked();

    await wrapper.find('button[type="submit"]').trigger('submit');
    expect(wrapper.emitted('update-user-preferences')).toBeDefined();
    expect(wrapper.emitted('update-user-preferences')).toEqual([
      [
        {
          userPreferences: {
            locale: 'en',
            preferred_language: 'rb',
            is_private: true,
            hide_problem_tags: true,
            has_competitive_objective: profile.has_competitive_objective,
            has_learning_objective: profile.has_learning_objective,
            has_scholar_objective: profile.has_scholar_objective,
            has_teaching_objective: profile.has_teaching_objective,
          },
          localeChanged: true,
        },
      ],
    ]);
  });

  it('Should disable ScholarCompetitive objective select when "none" option is selected', async () => {
    const wrapper = shallowMount(user_Preferences_Edit, {
      propsData: { profile },
    });
    await wrapper
      .find('select[data-learning-teaching-objective]')
      .find('option[value="none"]')
      .setSelected();
    expect(
      wrapper.find('select[data-scholar-competitive-objective]').element,
    ).toBeDisabled();
  });

  each([
    {
      objectiveLearningTeaching: 'learning',
      objectiveScholarCompetitiveQuestion:
        T.userObjectivesModalDescriptionLearning,
    },
    {
      objectiveLearningTeaching: 'teaching',
      objectiveScholarCompetitiveQuestion:
        T.userObjectivesModalDescriptionTeaching,
    },
    {
      objectiveLearningTeaching: 'learningAndTeaching',
      objectiveScholarCompetitiveQuestion:
        T.userObjectivesModalDescriptionLearningAndTeaching,
    },
    {
      objectiveLearningTeaching: 'none',
      objectiveScholarCompetitiveQuestion:
        T.userObjectivesModalDescriptionUsage,
    },
  ]).test(
    'Should display the correct ScholarCompetitive objective question when "$objectiveLearningTeaching" option is selected',
    async ({
      objectiveLearningTeaching,
      objectiveScholarCompetitiveQuestion,
    }) => {
      const wrapper = shallowMount(user_Preferences_Edit, {
        propsData: { profile },
      });
      await wrapper
        .find('select[data-learning-teaching-objective]')
        .find(`option[value="${objectiveLearningTeaching}"]`)
        .setSelected();
      expect(wrapper.text()).toContain(objectiveScholarCompetitiveQuestion);
    },
  );

  each([
    {
      objectiveLearningTeaching: 'learning',
      objectiveScholarCompetitive: 'scholar',
      valueCompetitive: false,
      valueLearning: true,
      valueScholar: true,
      valueTeaching: false,
    },
    {
      objectiveLearningTeaching: 'learning',
      objectiveScholarCompetitive: 'scholarAndcompetitive',
      valueCompetitive: true,
      valueLearning: true,
      valueScholar: true,
      valueTeaching: false,
    },
    {
      objectiveLearningTeaching: 'learning',
      objectiveScholarCompetitive: 'other',
      valueCompetitive: false,
      valueLearning: true,
      valueScholar: false,
      valueTeaching: false,
    },

    {
      objectiveLearningTeaching: 'teaching',
      objectiveScholarCompetitive: 'scholar',
      valueCompetitive: false,
      valueLearning: false,
      valueScholar: true,
      valueTeaching: true,
    },
    {
      objectiveLearningTeaching: 'teaching',
      objectiveScholarCompetitive: 'competitive',
      valueCompetitive: true,
      valueLearning: false,
      valueScholar: false,
      valueTeaching: true,
    },
    {
      objectiveLearningTeaching: 'teaching',
      objectiveScholarCompetitive: 'scholarAndcompetitive',
      valueCompetitive: true,
      valueLearning: false,
      valueScholar: true,
      valueTeaching: true,
    },
    {
      objectiveLearningTeaching: 'teaching',
      objectiveScholarCompetitive: 'other',
      valueCompetitive: false,
      valueLearning: false,
      valueScholar: false,
      valueTeaching: true,
    },

    {
      objectiveLearningTeaching: 'learningAndTeaching',
      objectiveScholarCompetitive: 'scholar',
      valueCompetitive: false,
      valueLearning: true,
      valueScholar: true,
      valueTeaching: true,
    },
    {
      objectiveLearningTeaching: 'learningAndTeaching',
      objectiveScholarCompetitive: 'competitive',
      valueCompetitive: true,
      valueLearning: true,
      valueScholar: false,
      valueTeaching: true,
    },
    {
      objectiveLearningTeaching: 'learningAndTeaching',
      objectiveScholarCompetitive: 'scholarAndcompetitive',
      valueCompetitive: true,
      valueLearning: true,
      valueScholar: true,
      valueTeaching: true,
    },
    {
      objectiveLearningTeaching: 'learningAndTeaching',
      objectiveScholarCompetitive: 'other',
      valueCompetitive: false,
      valueLearning: true,
      valueScholar: false,
      valueTeaching: true,
    },
  ]).test(
    'Should emit correct objectives values when "$objectiveLearningTeaching" and "$objectiveScholarCompetitive" options are selected',
    async ({
      objectiveLearningTeaching,
      objectiveScholarCompetitive,
      valueCompetitive,
      valueLearning,
      valueScholar,
      valueTeaching,
    }) => {
      const wrapper = shallowMount(user_Preferences_Edit, {
        propsData: { profile },
      });
      await wrapper
        .find('select[data-learning-teaching-objective]')
        .find(`option[value="${objectiveLearningTeaching}"]`)
        .setSelected();
      await wrapper
        .find('select[data-scholar-competitive-objective]')
        .find(`option[value="${objectiveScholarCompetitive}"]`)
        .setSelected();
      await wrapper.find('button[type="submit"]').trigger('submit');
      expect(wrapper.emitted('update-user-preferences')).toBeDefined();
      expect(wrapper.emitted('update-user-preferences')).toEqual([
        [
          {
            userPreferences: {
              locale: profile.locale,
              preferred_language: profile.preferred_language,
              is_private: profile.is_private,
              hide_problem_tags: profile.hide_problem_tags,
              has_competitive_objective: valueCompetitive,
              has_learning_objective: valueLearning,
              has_scholar_objective: valueScholar,
              has_teaching_objective: valueTeaching,
            },
            localeChanged: false,
          },
        ],
      ]);
    },
  );

  it('Should emit correct objectives values when "none" option is selected', async () => {
    const wrapper = shallowMount(user_Preferences_Edit, {
      propsData: { profile },
    });
    await wrapper
      .find('select[data-learning-teaching-objective]')
      .find('option[value="none"]')
      .setSelected();
    await wrapper.find('button[type="submit"]').trigger('submit');
    expect(wrapper.emitted('update-user-preferences')).toBeDefined();
    expect(wrapper.emitted('update-user-preferences')).toEqual([
      [
        {
          userPreferences: {
            locale: profile.locale,
            preferred_language: profile.preferred_language,
            is_private: profile.is_private,
            hide_problem_tags: profile.hide_problem_tags,
            has_competitive_objective: false,
            has_learning_objective: false,
            has_scholar_objective: false,
            has_teaching_objective: false,
          },
          localeChanged: false,
        },
      ],
    ]);
  });
});
