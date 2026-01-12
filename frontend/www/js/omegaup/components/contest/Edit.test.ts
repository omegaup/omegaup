import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import { types } from '../../api_types';

import contest_Edit from './Edit.vue';

describe('Edit.vue', () => {
  const details: types.ContestAdminDetails = {
    admin: true,
    admission_mode: 'private',
    alias: 'test',
    archived: false,
    available_languages: { py2: 'py2' },
    canSetRecommended: false,
    recommended: false,
    contest_for_teams: false,
    default_show_all_contestants_in_scoreboard: false,
    description: 'contest test',
    director: 'test_user',
    feedback: 'yes',
    finish_time: new Date(1),
    has_submissions: false,
    languages: ['py2'],
    needs_basic_information: true,
    opened: false,
    score_mode: 'partial',
    penalty: 0,
    penalty_calc_policy: 'sum',
    penalty_type: 'none',
    points_decay_factor: 0,
    problemset_id: 1,
    requests_user_information: 'no',
    scoreboard: 100,
    show_penalty: true,
    show_scoreboard_after: true,
    start_time: new Date(0),
    submissions_gap: 0,
    title: 'contest test',
  };

  const certificatesDetails: types.ContestCertificatesAdminDetails = {
    certificateCutoff: 3,
    certificatesStatus: 'uninitiated',
    isCertificateGenerator: true,
  };

  const propsData: {
    admins: types.ContestAdmin[];
    details: types.ContestAdminDetails;
    initialTab: string;
    groups: types.ContestGroup[];
    groupAdmins: types.ContestGroupAdmin[];
    problems: types.ProblemsetProblemWithVersions[];
    requests: types.ContestRequest[];
    users: types.ContestUser[];
    originalContestAdmissionMode: null | string;
    certificatesDetails: types.ContestCertificatesAdminDetails;
  } = {
    admins: [],
    details,
    initialTab: 'clone',
    groupAdmins: [],
    groups: [],
    problems: [],
    requests: [],
    users: [],
    originalContestAdmissionMode: null,
    certificatesDetails,
  };
  it('Should handle a normal contest', () => {
    const wrapper = shallowMount(contest_Edit, {
      propsData,
    });

    expect(wrapper.text()).toContain(T.contestDetailsGoToContest);

    expect(wrapper.vm.showTab).toBe('clone');
  });

  it('Should handle a virtual contest', () => {
    propsData.details.rerun_id = 2;
    propsData.initialTab = '';
    const wrapper = shallowMount(contest_Edit, {
      propsData,
    });

    expect(wrapper.vm.showTab).toBe('contestants');
  });

  it('Should handle a virtual contest from an original private contest', () => {
    propsData.details.rerun_id = 2;
    propsData.initialTab = '';
    propsData.originalContestAdmissionMode = 'private';
    const wrapper = shallowMount(contest_Edit, {
      propsData,
    });

    expect(wrapper.vm.showTab).toBe('links');
  });
});
