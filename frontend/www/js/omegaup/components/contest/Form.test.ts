import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import contest_Form from './Form.vue';

import { Multiselect } from 'vue-multiselect';

import { types } from '../../api_types';

describe('Form.vue', () => {
  beforeAll(() => {
    const div = document.createElement('div');
    div.id = 'root';
    document.body.appendChild(div);
  });

  afterAll(() => {
    const rootDiv = document.getElementById('root');
    if (rootDiv) {
      document.removeChild(rootDiv);
    }
  });

  it('Should handle add contest form', async () => {
    const startTime = new Date();
    const finishTime = new Date(startTime.getTime() + 60 * 60 * 1000);
    const wrapper = shallowMount(contest_Form, {
      propsData: {
        update: false,
        allLanguages: [{ py2: 'Python 2' }, { py3: 'Python 3' }],
        initialLanguages: [],
        initialFinishTime: finishTime,
        initialStartTime: startTime,
        initialSubmissionsGap: 1,
      },
    });

    expect(wrapper.find('div.card .card-header').text()).toContain(
      T.contestNew,
    );

    const contest = {
      alias: 'contestAlias',
      title: 'Contest Title',
      description: 'Contest description.',
    };
    await wrapper.setData(contest);

    expect(wrapper.find('form button[type="submit"]').text()).toBe(
      T.contestNewFormScheduleContest,
    );
  });

  it('Should handle edit contest form', async () => {
    const startTime = new Date();
    const finishTime = new Date(startTime.getTime() + 60 * 60 * 1000);
    const wrapper = shallowMount(contest_Form, {
      attachTo: '#root',
      propsData: {
        update: true,
        allLanguages: [{ py2: 'Python 2' }, { py3: 'Python 3' }],
        initialLanguages: ['py2'],
        initialFinishTime: finishTime,
        initialStartTime: startTime,
        initialSubmissionsGap: 1,
        initialAlias: 'contestAlias',
        initialTitle: 'Contest Title',
        initialDescription: 'Contest description.',
      },
    });

    expect(wrapper.find('form button[type="submit"]').text()).toBe(
      T.contestNewFormUpdateContest,
    );
    await wrapper.find('form button[type="submit"]').trigger('click');
    expect(wrapper.emitted('update-contest')).toBeDefined();
    wrapper.destroy();
  });

  const problems: types.ProblemsetProblemWithVersions[] = [
    {
      accepted: 0,
      accepts_submissions: true,
      alias: 'problemaSoloSalida',
      commit: 'commit',
      difficulty: 0,
      has_submissions: false,
      input_limit: 1024,
      languages: 'cat',
      order: 1,
      points: 100,
      quality_seal: false,
      submissions: 0,
      title: 'Problema solo salida',
      version: 'version',
      versions: { log: [], published: '' },
      visibility: 2,
      visits: 0,
    },
  ];

  it('Should block language removal', async () => {
    const startTime = new Date();
    const finishTime = new Date(startTime.getTime() + 60 * 60 * 1000);
    const wrapper = shallowMount(contest_Form, {
      propsData: {
        update: true,
        allLanguages: [
          { py2: 'Python 2' },
          { py3: 'Python 3' },
          { cat: 'cat' },
        ],
        initialLanguages: ['py2', 'cat'],
        initialFinishTime: finishTime,
        initialStartTime: startTime,
        initialSubmissionsGap: 1,
        initialAlias: 'contestAlias',
        initialTitle: 'Contest Title',
        initialDescription: 'Contest description.',
        problems,
      },
    });
    await wrapper.findComponent(Multiselect).vm.$emit('remove', 'cat');
    expect(wrapper.emitted('language-remove-blocked')).toBeDefined();
    wrapper.destroy();
  });
  it('Should update score mode when', async () => {
    const startTime = new Date();
    const finishTime = new Date(startTime.getTime() + 60 * 60 * 1000);
    const wrapper = shallowMount(contest_Form, {
      propsData: {
        update: true,
        allLanguages: [
          { py2: 'Python 2' },
          { py3: 'Python 3' },
          { cat: 'cat' },
        ],
        initialLanguages: ['py2', 'cat'],
        initialStartTime: startTime,
        initialFinishTime: finishTime,
        initialSubmissionsGap: 1,
        initialAlias: 'contestAlias',
        initialTitle: 'Contest Title',
        initialDescription: 'Contest description.',
        problems,
      },
    });

    expect(wrapper.vm.currentScoreMode).toBe('partial');
    await wrapper.find('[data-contest-icpc]').trigger('click');
    expect(wrapper.vm.currentScoreMode).toBe('all_or_nothing');
    await wrapper.find('[data-contest-preioi]').trigger('click');
    expect(wrapper.vm.currentScoreMode).toBe('partial');
    await wrapper.find('[data-contest-omi]').trigger('click');
    expect(wrapper.vm.currentScoreMode).toBe('partial');
    await wrapper.find('[data-contest-conacup]').trigger('click');
    expect(wrapper.vm.currentScoreMode).toBe('partial');
  });

  it('Should not allow basic information in contests for teams', async () => {
    const startTime = new Date();
    const finishTime = new Date(startTime.getTime() + 60 * 60 * 1000);
    const wrapper = shallowMount(contest_Form, {
      propsData: {
        update: false,
        allLanguages: [{ py3: 'Python 3' }],
        initialLanguages: ['py3'],
        initialFinishTime: finishTime,
        initialStartTime: startTime,
        initialSubmissionsGap: 1,
        initialNeedsBasicInformation: true,
      },
    });

    const basicInformation = wrapper.find('[data-basic-information-required]');
    expect(basicInformation.attributes('disabled')).toBeUndefined();

    await wrapper.find('[data-contest-for-teams]').setChecked(true);
    expect(wrapper.vm.needsBasicInformation).toBe(false);
    expect(basicInformation.attributes('disabled')).toBe('disabled');
    expect(wrapper.find('[data-basic-information-teams-warning]').text()).toBe(
      T.contestNewFormBasicInformationNotForTeams,
    );

    await wrapper.find('[data-contest-for-teams]').setChecked(false);
    expect(basicInformation.attributes('disabled')).toBeUndefined();
    expect(
      wrapper.find('[data-basic-information-teams-warning]').exists(),
    ).toBe(false);
    wrapper.destroy();
  });

  it('Should reject an existing contest for teams that requires basic information', async () => {
    const startTime = new Date();
    const finishTime = new Date(startTime.getTime() + 60 * 60 * 1000);
    const wrapper = shallowMount(contest_Form, {
      propsData: {
        update: true,
        allLanguages: [{ py3: 'Python 3' }],
        initialLanguages: ['py3'],
        initialFinishTime: finishTime,
        initialStartTime: startTime,
        initialSubmissionsGap: 1,
        initialAlias: 'contestAlias',
        initialTitle: 'Contest Title',
        initialDescription: 'Contest description.',
        contestForTeams: true,
        teamsGroupAlias: { key: 'teams', value: 'Teams' },
        initialNeedsBasicInformation: true,
        problems,
      },
    });

    // The editor must still be able to uncheck it to fix the contest.
    const basicInformation = wrapper.find('[data-basic-information-required]');
    expect(basicInformation.attributes('disabled')).toBeUndefined();
    expect(wrapper.vm.validateForm()).toBe(false);
    expect(wrapper.vm.localErrors.needs_basic_information).toBe(
      T.contestNewFormBasicInformationNotForTeams,
    );
    await wrapper.vm.$nextTick();
    expect(wrapper.find('[data-basic-information-error]').text()).toBe(
      T.contestNewFormBasicInformationNotForTeams,
    );
    expect(basicInformation.classes()).toContain('is-invalid');

    await basicInformation.setChecked(false);
    expect(wrapper.find('[data-basic-information-error]').exists()).toBe(false);
    expect(wrapper.vm.validateForm()).toBe(true);
    wrapper.destroy();
  });
});
