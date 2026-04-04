import { mount, shallowMount } from '@vue/test-utils';
import T from '../../lang';
import qualitynomination_List from './List.vue';
import type { types } from '../../api_types';

const nominations: types.NominationListItem[] = [1, 2, 3].map((x) => ({
  author: {
    name: 'nombre',
    username: 'user',
  },
  contents: {
    original: '',
    rationale: 'N/A',
    reason: 'wrong-test-cases',
  },
  nomination: 'demotion',
  nominator: {
    name: 'nominador',
    username: 'user_nominator',
  },
  problem: {
    alias: `Problem-${x}`,
    title: `Problem ${x}`,
  },
  qualitynomination_id: x,
  status: 'open',
  time: new Date(`2021-02-0${x} 00:00:00`),
  votes: [],
}));

describe('List.vue', () => {
  it('Should handle list of nominations', async () => {
    const wrapper = shallowMount(qualitynomination_List, {
      propsData: {
        nominations: [
          {
            author: {
              name: 'nombre',
              username: 'user',
            },
            contents: {
              original: '',
              rationale: 'N/A',
              reason: 'poorly-described',
            },
            nomination: 'demotion',
            nominator: {
              name: 'nominador',
              username: 'user_nominator',
            },
            problem: {
              alias: 'problem',
              title: 'problem',
            },
            qualitynomination_id: 1,
            status: 'open',
            time: new Date('2020-06-03 23:46:10'),
            votes: [],
          } as types.NominationListItem,
        ] as types.NominationListItem[],
        pagerItems: [
          {
            class: 'disabled',
            label: '1',
            page: 1,
          },
        ],
        pages: 1,
        length: 100,
        isAdmin: true,
        myView: false,
      },
    });

    expect(wrapper.find('[data-name="reason"]').text()).toContain(
      T.wordsReason,
    );
  });

  it('Should handle my list of nominations', async () => {
    const wrapper = shallowMount(qualitynomination_List, {
      propsData: {
        nominations: [
          {
            author: {
              name: 'nombre',
              username: 'user',
            },
            contents: {
              original: '',
              rationale: 'N/A',
              reason: 'poorly-described',
            },
            nomination: 'demotion',
            nominator: {
              name: 'nominador',
              username: 'user_nominator',
            },
            problem: {
              alias: 'problem',
              title: 'problem',
            },
            qualitynomination_id: 1,
            status: 'open',
            time: new Date('2020-06-03 23:46:10'),
            votes: [],
          },
        ],
        pagerItems: [
          {
            class: 'disabled',
            label: '1',
            page: 1,
          },
        ],
        pages: 1,
        length: 100,
        isAdmin: false,
        myView: true,
      },
    });

    expect(wrapper.find('[data-name="reason"]').exists()).toBe(false);
  });

  describe('Sort controls', () => {
    it('Should sort by problem title by default', async () => {
      const wrapper = shallowMount(qualitynomination_List, {
        propsData: {
          nominations,
          pagerItems: [
            {
              class: 'disabled',
              label: '1',
              page: 1,
            },
          ],
          pages: 1,
          length: 100,
          isAdmin: true,
          myView: false,
        },
      });

      expect(wrapper.vm.orderedNominations[0].problem.title).toBe('Problem 1');
    });

    it('Should sort by problem title in descending order', async () => {
      const wrapper = mount(qualitynomination_List, {
        propsData: {
          nominations,
          pagerItems: [
            {
              class: 'disabled',
              label: '1',
              page: 1,
            },
          ],
          pages: 1,
          length: 100,
          isAdmin: true,
          myView: false,
        },
      });

      expect(wrapper.vm.orderedNominations[0].problem.title).toBe('Problem 1');

      const sortControlByTitle = wrapper.findComponent({
        ref: 'sortControlByTitle',
      });
      expect(sortControlByTitle.exists()).toBe(true);

      const button = sortControlByTitle.find('a');
      expect(button.exists()).toBe(true);

      button.trigger('click');

      expect(wrapper.vm.orderedNominations[0].problem.title).toBe('Problem 3');
    });

    it('Should sort by time', async () => {
      const wrapper = mount(qualitynomination_List, {
        propsData: {
          nominations,
          pagerItems: [
            {
              class: 'disabled',
              label: '1',
              page: 1,
            },
          ],
          pages: 1,
          length: 100,
          isAdmin: true,
          myView: false,
        },
      });

      expect(wrapper.vm.orderedNominations[0].problem.title).toBe('Problem 1');
      expect(wrapper.vm.orderedNominations[0].time.getTime()).toBe(
        new Date('2021-02-01 00:00:00').getTime(),
      );

      const sortControlByTime = wrapper.findComponent({
        ref: 'sortControlByTime',
      });
      expect(sortControlByTime.exists()).toBe(true);

      const button = sortControlByTime.find('a');
      expect(button.exists()).toBe(true);

      button.trigger('click');

      expect(wrapper.vm.orderedNominations[0].problem.title).toBe('Problem 3');
      expect(wrapper.vm.orderedNominations[0].time.getTime()).toBe(
        new Date('2021-02-03 00:00:00').getTime(),
      );
    });
  });
});
