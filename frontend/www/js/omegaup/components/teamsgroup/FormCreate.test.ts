import { mount } from '@vue/test-utils';
import T from '../../lang';

import teamsgroup_FormCreate from './FormCreate.vue';
import teamsgroup_FormBase from './FormBase.vue';

describe('FormCreate.vue', () => {
  beforeEach(() => {
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

  it('Should handle form create', async () => {
    const wrapper = mount(teamsgroup_FormCreate, {
      attachTo: '#root',
    });

    expect(wrapper.find('div[class="card-header"]').text()).toBe(
      T.omegaupTitleTeamsGroupNew,
    );

    const formBase = wrapper.findComponent(teamsgroup_FormBase);
    formBase.setData({
      name: 'Hello omegaUp',
      description: 'Hello omegaUp Description',
    });
    await formBase.find('button[type="submit"]').trigger('click');
    expect(wrapper.emitted('create-teams-group')).toEqual([
      [
        {
          alias: null,
          description: 'Hello omegaUp Description',
          name: 'Hello omegaUp',
        },
      ],
    ]);

    wrapper.destroy();
  });

  it('Should handle changes in name field', async () => {
    const wrapper = mount(teamsgroup_FormCreate);

    const formBase = wrapper.findComponent(teamsgroup_FormBase);
    formBase.setData({
      name: 'some new name',
    });

    await formBase.find('button[type="submit"]').trigger('click');

    expect(wrapper.emitted('validate-unused-alias')).toEqual([
      ['some-new-name'],
    ]);
    expect(wrapper.vm.$data.alias).toBe('some-new-name');
  });
});
