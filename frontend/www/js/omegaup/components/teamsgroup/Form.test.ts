import { shallowMount } from '@vue/test-utils';
import T from '../../lang';

import teamsgroup_Form from './Form.vue';

describe('Form.vue', () => {
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

  const propsData = {
    teamsGroupAlias: 'Hello',
    teamsGroupName: 'Hello omegaUp',
    teamsGroupDescription: 'Hello omegaUp Description',
    isUpdate: false,
  };

  it('Should handle new form', async () => {
    const wrapper = shallowMount(teamsgroup_Form, {
      attachTo: '#root',
      propsData,
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

  it('Should handle changes in name field', async () => {
    const wrapper = shallowMount(teamsgroup_Form, {
      propsData,
    });

    wrapper.setData({
      name: 'some new name',
    });

    await wrapper.find('button[type="submit"]').trigger('click');

    expect(wrapper.emitted('validate-unused-alias')).toEqual([
      ['some-new-name'],
    ]);
    expect(wrapper.vm.$data.alias).toBe('some-new-name');
  });

  it('Should handle edit form', async () => {
    const wrapper = shallowMount(teamsgroup_Form, {
      attachTo: '#root',
      propsData: { ...propsData, ...{ isUpdate: true } },
    });

    expect(wrapper.find('div[class="card"]').text()).not.toContain(
      T.omegaupTitleTeamsGroupNew,
    );

    wrapper.setData({
      name: 'some updated name',
    });

    await wrapper.find('button[type="submit"]').trigger('click');

    // In edit mode, alias can not change its value
    expect(wrapper.vm.$data.alias).toBe('Hello');
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
