import { shallowMount } from '@vue/test-utils';
import T from '../../lang';
import * as ui from '../../ui';
import each from 'jest-each';
import user_ObjectivesQuestions from './ObjectivesQuestions.vue';

describe('ObjectivesQuestions.vue', () => {
  it('Should only display next modal page button', async () => {
    const wrapper = shallowMount(user_ObjectivesQuestions);
    const buttonNext = wrapper.find('button.btn-next-previous');
    expect(buttonNext.exists()).toBe(true);
    expect(buttonNext.text()).toBe(T.userObjectivesModalButtonNext);
    expect(wrapper.find('button.btn-primary').exists()).toBe(false);
  });

  it('Should only display submit button', async () => {
    const wrapper = shallowMount(user_ObjectivesQuestions);
    await wrapper.find('input[type="radio"][value="none"]').setChecked();
    expect(wrapper.find('button.btn-primary').exists()).toBe(true);
    expect(wrapper.find('button.btn-next-previous').exists()).toBe(false);
  });

  it('Should display previous modal page and submit buttons', async () => {
    const wrapper = shallowMount(user_ObjectivesQuestions);
    await wrapper.find('input[type="radio"][value="learning"]').setChecked();

    const buttonNext = wrapper.find('button.btn-next-previous');
    expect(buttonNext.exists()).toBe(true);
    await buttonNext.trigger('click');

    const buttonPrevious = wrapper.find('button.btn-next-previous');
    expect(buttonPrevious.exists()).toBe(true);
    expect(buttonPrevious.text()).toBe(T.userObjectivesModalButtonPrevious);
    expect(wrapper.find('button.btn-primary').exists()).toBe(true);
  });

  it('Should display 2 as the last modal page counter', () => {
    const wrapper = shallowMount(user_ObjectivesQuestions);
    expect(wrapper.text()).toContain(
      ui.formatString(T.userObjectivesModalPageCounter, {
        current: 1,
        last: 2,
      }),
    );
  });

  it('Should display 1 as the last modal page counter', async () => {
    const wrapper = shallowMount(user_ObjectivesQuestions);
    await wrapper.find('input[type="radio"][value="none"]').setChecked();
    expect(wrapper.text()).toContain(
      ui.formatString(T.userObjectivesModalPageCounter, {
        current: 1,
        last: 1,
      }),
    );
  });

  each([
    {
      objective: 'learning',
      description: T.userObjectivesModalDescriptionLearning,
    },
    {
      objective: 'teaching',
      description: T.userObjectivesModalDescriptionTeaching,
    },
    {
      objective: 'learningAndTeaching',
      description: T.userObjectivesModalDescriptionLearningAndTeaching,
    },
  ]).test(
    'Should display correct description when "$objective" radio button is checked',
    async ({ objective, description }) => {
      const wrapper = shallowMount(user_ObjectivesQuestions);
      expect(wrapper.text()).toContain(T.userObjectivesModalDescriptionUsage);
      await wrapper
        .find(`input[type="radio"][value="${objective}"]`)
        .setChecked();
      await wrapper.find('button.btn-next-previous').trigger('click');
      expect(wrapper.text()).toContain(description);
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
    'Should emit correct objectives values when "$objectiveA" and "$objectiveB" radio buttons are checked',
    async ({
      objectiveA,
      objectiveB,
      valueCompetitive,
      valueLearning,
      valueScholar,
      valueTeaching,
    }) => {
      const wrapper = shallowMount(user_ObjectivesQuestions);
      await wrapper
        .find(`input[type="radio"][value="${objectiveA}"]`)
        .setChecked();
      await wrapper.find('button.btn-next-previous').trigger('click');
      await wrapper
        .find(`input[type="radio"][value="${objectiveB}"]`)
        .setChecked();
      await wrapper.find('button.btn-primary').trigger('click');
      expect(wrapper.emitted('submit')).toBeDefined();
      expect(wrapper.emitted('submit')?.[0]).toEqual([
        {
          hasCompetitiveObjective: valueCompetitive,
          hasLearningObjective: valueLearning,
          hasScholarObjective: valueScholar,
          hasTeachingObjective: valueTeaching,
        },
      ]);
    },
  );

  it('Should emit correct objectives values when "none" radio button is checked', async () => {
    const wrapper = shallowMount(user_ObjectivesQuestions);
    await wrapper.find(`input[type="radio"][value="none"]`).setChecked();
    await wrapper.find('button.btn-primary').trigger('click');
    expect(wrapper.emitted('submit')).toBeDefined();
    expect(wrapper.emitted('submit')?.[0]).toEqual([
      {
        hasCompetitiveObjective: false,
        hasLearningObjective: false,
        hasScholarObjective: false,
        hasTeachingObjective: false,
      },
    ]);
  });
});
