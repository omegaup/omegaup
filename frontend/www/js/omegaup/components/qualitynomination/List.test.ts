import { shallowMount } from '@vue/test-utils';
import T from '../../lang';
import qualitynomination_List from './List.vue';
import type { types } from '../../api_types';

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
      const nominations: types.NominationListItem[] = [];

      for (let i = 6; i >= 1; --i) {
        nominations.push({
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
            alias: `Problem-${i}`,
            title: `Problem ${i}`,
          },
          qualitynomination_id: 1,
          status: 'open',
          time: new Date('2021-02-03 00:00:00'),
          votes: [],
        } as types.NominationListItem);
      }

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
      const nominations: types.NominationListItem[] = [];

      for (let i = 6; i >= 1; --i) {
        nominations.push({
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
            alias: `Problem-${i}`,
            title: `Problem ${i}`,
          },
          qualitynomination_id: 1,
          status: 'open',
          time: new Date('2021-02-03 00:00:00'),
          votes: [],
        } as types.NominationListItem);
      }

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

      const sortControlByTime = wrapper.findComponent({
        ref: 'sortControlByTitle',
      });
      expect(sortControlByTime.exists()).toBe(true);

      sortControlByTime.vm.$emit('apply-filter', 'title', 'desc');
      expect(wrapper.vm.orderedNominations[0].problem.title).toBe('Problem 6');
    });

    it('Should sort by time', async () => {
      const nominations: types.NominationListItem[] = [];

      for (let i = 6; i >= 1; --i) {
        nominations.push({
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
            alias: `Problem-${7 - i}`,
            title: `Problem ${7 - i}`,
          },
          qualitynomination_id: 1,
          status: 'open',
          time: new Date(`2021-02-0${i} 00:00:00`),
          votes: [],
        } as types.NominationListItem);
      }

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
      expect(wrapper.vm.orderedNominations[0].time.getTime()).toBe(
        new Date('2021-02-06 00:00:00').getTime(),
      );

      const sortControlByTime = wrapper.findComponent({
        ref: 'sortControlByTime',
      });
      expect(sortControlByTime.exists()).toBe(true);

      sortControlByTime.vm.$emit('apply-filter', 'time', 'asc');
      expect(wrapper.vm.orderedNominations[0].problem.title).toBe('Problem 6');
      expect(wrapper.vm.orderedNominations[0].time.getTime()).toBe(
        new Date('2021-02-01 00:00:00').getTime(),
      );
    });
  });
});
