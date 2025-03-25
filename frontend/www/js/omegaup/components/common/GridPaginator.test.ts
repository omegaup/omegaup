import { shallowMount } from '@vue/test-utils';

import common_GridPaginator from './GridPaginator.vue';
import { LinkableResource, Problem } from '../../linkable_resource';

describe('GridPaginator.vue', () => {
  const title = 'Coder of the month';
  const items: LinkableResource[] = [
    new Problem({
      accepted: 1,
      difficulty: 2,
      submissions: 3,
      alias: 'colas',
      quality_seal: true,
      title: 'Colas',
    }),
    new Problem({
      accepted: 1,
      difficulty: 2,
      submissions: 4,
      alias: 'filas',
      quality_seal: true,
      title: 'Filas',
    }),
    new Problem({
      accepted: 1,
      difficulty: 2,
      submissions: 5,
      alias: 'divisiones',
      quality_seal: false,
      title: 'Divisiones',
    }),
  ];
  const columns = 2;
  const itemsPerPage = 2;

  it('Should handle an empty grid', () => {
    const wrapper = shallowMount(common_GridPaginator, {
      propsData: {
        items: [],
        itemsPerPage,
        columns,
        title,
      },
    });

    expect(wrapper.find('h6.card-header').text()).toContain(title);
    expect(wrapper.find('table').exists()).toBeFalsy();
  });

  it('Should handle a grid with information', async () => {
    const title = 'Coder of the month';
    const wrapper = shallowMount(common_GridPaginator, {
      propsData: {
        items,
        itemsPerPage: 4,
        columns,
        title,
      },
    });

    expect(wrapper.find('table').exists()).toBeTruthy();
    // Only one page is shown
    expect(
      wrapper.find('button[data-button-previous]').attributes('disabled'),
    ).toBe('disabled');
    expect(
      wrapper.find('button[data-button-next]').attributes('disabled'),
    ).toBe('disabled');
  });

  it('Should handle a grid with information divided in multiple pages', async () => {
    const title = 'Coder of the month';
    const wrapper = shallowMount(common_GridPaginator, {
      propsData: {
        items,
        itemsPerPage,
        columns,
        title,
      },
    });

    expect(wrapper.find('table').exists()).toBeTruthy();

    // There are more than one page, so the button "Next" in the paginator
    // should be enabled
    expect(
      wrapper.find('button[data-button-previous]').attributes('disabled'),
    ).toBe('disabled');
    expect(
      wrapper.find('button[data-button-next]').attributes('disabled'),
    ).toBeUndefined();
  });
});
