import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import contest_NewForm from './NewForm.vue';

import { Multiselect } from 'vue-multiselect';

import { types } from '../../api_types';
import { BButton } from 'bootstrap-vue';

describe('NewForm.vue', () => {
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
    const wrapper = shallowMount(contest_NewForm, {
      propsData: {
        update: false,
        allLanguages: [{ py2: 'Python 2' }, { py3: 'Python 3' }],
        initialLanguages: [],
        initialFinishTime: new Date(),
        initialStartTime: new Date(),
        initialSubmissionsGap: 1,
      },
      stubs: {
        BButton,
      },
    });

    await wrapper.vm.$nextTick();

    expect(wrapper.find('div.card .card-header').text()).toBe(T.contestNew);

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
    const wrapper = shallowMount(contest_NewForm, {
      attachTo: '#root',
      propsData: {
        update: true,
        allLanguages: [{ py2: 'Python 2' }, { py3: 'Python 3' }],
        initialLanguages: ['py2'],
        initialFinishTime: new Date(),
        initialStartTime: new Date(),
        initialSubmissionsGap: 1,
        initialAlias: 'contestAlias',
        initialTitle: 'Contest Title',
        initialDescription: 'Contest description.',
      },
      stubs: {
        BButton,
      },
    });

    await wrapper.vm.$nextTick();

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
    const wrapper = shallowMount(contest_NewForm, {
      propsData: {
        update: true,
        allLanguages: [
          { py2: 'Python 2' },
          { py3: 'Python 3' },
          { cat: 'cat' },
        ],
        initialLanguages: ['py2', 'cat'],
        initialFinishTime: new Date(),
        initialStartTime: new Date(),
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

  it('Should format the number to be greater than 0', () => {
    const wrapper = shallowMount(contest_NewForm, {
      propsData: {
        update: false,
        allLanguages: [{ py2: 'Python 2' }, { py3: 'Python 3' }],
        initialLanguages: [],
        initialFinishTime: new Date(),
        initialStartTime: new Date(),
        initialSubmissionsGap: 1,
      },
    });

    // We use as any since wrapper.vm doesn't detect all the methods inside NewForm.vue component
    expect((wrapper.vm as any).numberFormatter(2)).toBe(2);
    expect((wrapper.vm as any).numberFormatter(-2)).toBe(0);
    expect((wrapper.vm as any).numberFormatter(0)).toBe(0);
    expect((wrapper.vm as any).numberFormatter(-10)).toBe(0);
    expect((wrapper.vm as any).numberFormatter(200)).toBe(200);
    expect((wrapper.vm as any).numberFormatter(null)).toBe(0);
  });

  it('Should open the collapsed component if required', () => {
    const wrapper = shallowMount(contest_NewForm, {
      propsData: {
        update: false,
        allLanguages: [{ py2: 'Python 2' }, { py3: 'Python 3' }],
        initialLanguages: [],
        initialFinishTime: new Date(),
        initialStartTime: new Date(),
        initialSubmissionsGap: 1,
      },
    });

    // We use as any since wrapper.vm doesn't detect all the methods and data inside NewForm.vue component
    (wrapper.vm as any).openCollapsedIfRequired();
    expect((wrapper.vm as any).basicInfoVisible).toBe(true);
  });
});
