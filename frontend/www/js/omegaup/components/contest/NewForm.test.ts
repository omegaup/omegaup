import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import contest_NewForm from './NewForm.vue';

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
    });

    expect(wrapper.find('div.card .card-header .panel-title').text()).toBe(
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
    });

    expect(wrapper.find('form button[type="submit"]').text()).toBe(
      T.contestNewFormUpdateContest,
    );
    await wrapper.find('form button[type="submit"]').trigger('click');
    expect(wrapper.emitted('update-contest')).toBeDefined();

    wrapper.destroy();
  });
});
