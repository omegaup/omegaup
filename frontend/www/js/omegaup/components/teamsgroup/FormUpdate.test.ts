import { mount } from '@vue/test-utils';
import T from '../../lang';

import teamsgroup_FormUpdate from './FormUpdate.vue';
import teamsgroup_FormBase from './FormBase.vue';

describe('FormUpdate.vue', () => {
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

  it('Should handle form update', async () => {
    const wrapper = mount(teamsgroup_FormUpdate, {
      attachTo: '#root',
      propsData: {
        teamsGroupAlias: 'Hello',
        teamsGroupName: 'Hello omegaUp',
        teamsGroupDescription: 'Hello omegaUp Description',
      },
    });

    expect(wrapper.find('div[class="card"]').text()).not.toContain(
      T.omegaupTitleTeamsGroupNew,
    );

    const formBase = wrapper.findComponent(teamsgroup_FormBase);
    formBase.setData({
      name: 'some updated name',
    });

    await formBase.find('button[type="submit"]').trigger('click');

    // In edit mode, alias can not change its value
    expect(wrapper.vm.$props.teamsGroupAlias).toBe('Hello');
    expect(wrapper.emitted('validate-unused-alias')).toBeFalsy();
    expect(wrapper.emitted('update-teams-group')).toEqual([
      [
        {
          description: 'Hello omegaUp Description',
          name: 'some updated name',
        },
      ],
    ]);

    wrapper.destroy();
  });
});
