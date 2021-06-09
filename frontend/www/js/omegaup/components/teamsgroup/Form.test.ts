import { shallowMount } from '@vue/test-utils';
import T from '../../lang';

import teamsgroup_Form from './Form.vue';

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

  it('Should handle new form', async () => {
    const wrapper = shallowMount(teamsgroup_Form, {
      attachTo: '#root',
      propsData: {
        teamsGroupAlias: 'Hello',
        teamsGroupName: 'Hello omegaUp',
        teamsGroupDescription: 'Hello omegaUp Description',
        isUpdate: false,
      },
    });

    expect(wrapper.find('div[class="card-header"]').text()).toBe(
      T.omegaupTitleTeamsGroupNew,
    );

    await wrapper.find('button[type="submit"]').trigger('click');
    expect(wrapper.emitted('create-teams-group')).toEqual([
      [
        {
          alias: 'Hello',
          description: 'Hello omegaUp Description',
          name: 'Hello omegaUp',
        },
      ],
    ]);

    wrapper.destroy();
  });
});
