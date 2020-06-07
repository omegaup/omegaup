import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import T from '../../lang';
import * as ui from '../../ui';
import qualitynomination_List from './List.vue';
import { types } from '../../api_types';

describe('List.vue', () => {
  it('Should handle list of nominations', async () => {
    const wrapper = shallowMount(qualitynomination_List, {
      propsData: {
        nominations: <types.NominationListItem[]>[
          <types.NominationListItem>{
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
        isAdmin: true,
        myView: false,
      },
    });

    expect(wrapper.find('[name="title"]').text()).toContain(
      ui.formatString(T.nominationsRangeHeader, {
        lowCount: 1,
        highCount: 100,
      }),
    );

    expect(wrapper.find('[name="table_head"]').text()).toContain(
      T.wordsAlias +
        T.wordsNominator +
        T.wordsAuthor +
        T.wordsSubmissionDate +
        T.wordsReason +
        T.wordsStatus,
    );

    expect(wrapper.find('[name="table_body"]').text()).toContain(
      'problemuser_nominatoruser3/6/2020poorly-describedopen' + T.wordsDetails,
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

    expect(wrapper.find('[name="title"]').text()).toContain(
      ui.formatString(T.nominationsRangeHeader, {
        lowCount: 1,
        highCount: 100,
      }),
    );

    expect(wrapper.find('[name="table_head"]').text()).toContain(
      T.wordsAlias + T.wordsAuthor + T.wordsSubmissionDate + T.wordsStatus,
    );

    expect(wrapper.find('[name="table_body"]').text()).toContain(
      'problemuser3/6/2020open' + T.wordsDetails,
    );
  });
});
