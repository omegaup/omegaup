import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';

import contest_AddContestant from './AddContestant.vue';

describe('AddContestant.vue', () => {
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

  it('Should handle empty user list', async () => {
    const wrapper = shallowMount(contest_AddContestant, {
      propsData: {
        contest: {
          admission_mode: 'public',
          alias: 'Test contest',
          window_length: 20,
        },
        users: [],
      },
    });

    expect(wrapper.text()).toContain(T.addUsersMultipleOrSingleUser);
  });

  it('Should handle user list in bulk and in typeahead', async () => {
    const wrapper = shallowMount(contest_AddContestant, {
      attachTo: '#root',
      propsData: {
        contest: {
          admission_mode: 'public',
          alias: 'Test contest',
          window_length: 20,
        },
        users: [],
      },
    });

    await wrapper.setData({
      bulkContestants: 'user, test_user_1',
      typeaheadContestants: [
        {
          key: 'user_1',
          value: 'User omegaUp',
        },
        {
          key: 'test_user_2',
          value: 'Test user omegaUp',
        },
      ] as types.ListItem[],
    });
    await wrapper
      .find('form button[type="submit"].user-add-bulk')
      .trigger('click');
    expect(wrapper.emitted()['add-user']).toStrictEqual([
      [['user', 'test_user_1', 'user_1', 'test_user_2']],
    ]);

    wrapper.destroy();
  });
});
