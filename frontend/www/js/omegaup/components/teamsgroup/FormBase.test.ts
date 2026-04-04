import { shallowMount } from '@vue/test-utils';
import T from '../../lang';

import teamsgroup_FormBase from './FormBase.vue';

describe('FormBase.vue', () => {
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
    alias: 'Hello',
    name: 'Hello omegaUp',
    description: 'Hello omegaUp Description',
  };

  it('Should handle form base', async () => {
    const wrapper = shallowMount(teamsgroup_FormBase, {
      attachTo: '#root',
      propsData,
    });

    expect(wrapper.find('div[class="card"]').text()).not.toContain(
      T.omegaupTitleTeamsGroupNew,
    );

    await wrapper.find('button[type="submit"]').trigger('click');
    expect(wrapper.emitted('submit')).toEqual([
      [
        {
          description: 'Hello omegaUp Description',
          name: 'Hello omegaUp',
          numberOfContestants: 3,
        },
      ],
    ]);

    wrapper.destroy();
  });
});
