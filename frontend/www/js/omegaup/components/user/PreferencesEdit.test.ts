import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import { ObjectivesAnswers } from './ObjectivesQuestions.vue';
import user_Preferences_Edit from './PreferencesEdit.vue';
import T from '../../lang';
import each from 'jest-each';

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
  has_competitive_objective: false,
  has_learning_objective: true,
  has_scholar_objective: true,
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
    await wrapper
      .find('select[data-firstObjective]')
      .find(`option[value="${ObjectivesAnswers.Teaching}"]`)
      .setSelected();
    await wrapper
      .find('select[data-secondObjective]')
      .find(`option[value="${ObjectivesAnswers.Competitive}"]`)
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
            has_competitive_objective: true,
            has_learning_objective: false,
            has_scholar_objective: false,
            has_teaching_objective: true,
          },
          localeChanged: true,
        },
      ],
    ]);
  });

  it('Should disable second objective select when "none" option is selected', async () => {
    const wrapper = shallowMount(user_Preferences_Edit, {
      propsData: { profile },
    });
    await wrapper
      .find('select[data-firstObjective]')
      .find(`option[value="${ObjectivesAnswers.None}"]`)
      .setSelected();
    expect(wrapper.find('select[data-secondObjective]').element).toBeDisabled();
  });

  each([
    {
      objective: 'learning',
      question: T.userObjectivesModalDescriptionLearning,
    },
    {
      objective: 'teaching',
      question: T.userObjectivesModalDescriptionTeaching,
    },
    {
      objective: 'learningAndTeaching',
      question: T.userObjectivesModalDescriptionLearningAndTeaching,
    },
    {
      objective: 'none',
      question: T.userObjectivesModalDescriptionUsage,
    },
  ]).test(
    'Should display the correct second objective question when "$objective" option is selected',
    async ({ objective, question }) => {
      const wrapper = shallowMount(user_Preferences_Edit, {
        propsData: { profile },
      });
      await wrapper
        .find('select[data-firstObjective]')
        .find(`option[value="${objective}"]`)
        .setSelected();
      expect(wrapper.text()).toContain(question);
    },
  );

  each([
    {
      objectiveA: 'learning',
      objectiveB: 'scholar',
      valueCompetitive: false,
      valueLearning: true,
      valueScholar: true,
      valueTeaching: false,
    },
    {
      objectiveA: 'learning',
      objectiveB: 'competitive',
      valueCompetitive: true,
      valueLearning: true,
      valueScholar: false,
      valueTeaching: false,
    },
    {
      objectiveA: 'learning',
      objectiveB: 'scholarAndcompetitive',
      valueCompetitive: true,
      valueLearning: true,
      valueScholar: true,
      valueTeaching: false,
    },
    {
      objectiveA: 'learning',
      objectiveB: 'other',
      valueCompetitive: false,
      valueLearning: true,
      valueScholar: false,
      valueTeaching: false,
    },

    {
      objectiveA: 'teaching',
      objectiveB: 'scholar',
      valueCompetitive: false,
      valueLearning: false,
      valueScholar: true,
      valueTeaching: true,
    },
    {
      objectiveA: 'teaching',
      objectiveB: 'competitive',
      valueCompetitive: true,
      valueLearning: false,
      valueScholar: false,
      valueTeaching: true,
    },
    {
      objectiveA: 'teaching',
      objectiveB: 'scholarAndcompetitive',
      valueCompetitive: true,
      valueLearning: false,
      valueScholar: true,
      valueTeaching: true,
    },
    {
      objectiveA: 'teaching',
      objectiveB: 'other',
      valueCompetitive: false,
      valueLearning: false,
      valueScholar: false,
      valueTeaching: true,
    },

    {
      objectiveA: 'learningAndTeaching',
      objectiveB: 'scholar',
      valueCompetitive: false,
      valueLearning: true,
      valueScholar: true,
      valueTeaching: true,
    },
    {
      objectiveA: 'learningAndTeaching',
      objectiveB: 'competitive',
      valueCompetitive: true,
      valueLearning: true,
      valueScholar: false,
      valueTeaching: true,
    },
    {
      objectiveA: 'learningAndTeaching',
      objectiveB: 'scholarAndcompetitive',
      valueCompetitive: true,
      valueLearning: true,
      valueScholar: true,
      valueTeaching: true,
    },
    {
      objectiveA: 'learningAndTeaching',
      objectiveB: 'other',
      valueCompetitive: false,
      valueLearning: true,
      valueScholar: false,
      valueTeaching: true,
    },
  ]).test(
    'Should emit correct objectives values when "$objectiveA" and "$objectiveB" options are selected',
    async ({
      objectiveA,
      objectiveB,
      valueCompetitive,
      valueLearning,
      valueScholar,
      valueTeaching,
    }) => {
      const wrapper = shallowMount(user_Preferences_Edit, {
        propsData: { profile },
      });
      await wrapper
        .find('select[data-firstObjective]')
        .find(`option[value="${objectiveA}"]`)
        .setSelected();
      await wrapper
        .find('select[data-secondObjective]')
        .find(`option[value="${objectiveB}"]`)
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
      .find('select[data-firstObjective]')
      .find(`option[value="${ObjectivesAnswers.None}"]`)
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
