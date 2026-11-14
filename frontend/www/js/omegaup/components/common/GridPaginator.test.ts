import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import { LinkableResource, Problem } from '../../linkable_resource';
import * as ui from '../../ui';
import common_GridPaginator from './GridPaginator.vue';

function pageRange(
  low: number,
  high: number,
  total: number,
  page: number,
  pages: number,
): string {
  return ui.formatString(T.gridPaginatorPageRange, {
    low,
    high,
    total,
    page,
    pages,
  });
}

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
    expect(wrapper.find('[data-page-range]').text()).toBe(
      pageRange(1, 3, 3, 1, 1),
    );
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
    expect(wrapper.find('[data-page-range]').text()).toBe(
      pageRange(1, 2, 3, 1, 2),
    );

    await wrapper.find('button[data-button-next]').trigger('click');
    expect(wrapper.find('[data-page-range]').text()).toBe(
      pageRange(3, 3, 3, 2, 2),
    );
  });

  it('Should reset to the first page when a filter is applied from a later page', async () => {
    const wrapper = shallowMount(common_GridPaginator, {
      propsData: {
        items,
        itemsPerPage,
        columns,
        title,
        shouldShowFilterInput: true,
      },
    });

    await wrapper.find('button[data-button-next]').trigger('click');
    expect(wrapper.text()).toContain('Divisiones');
    expect(wrapper.text()).not.toContain('Colas');

    await wrapper.setData({ filter: 'Colas' });

    expect(wrapper.text()).toContain('Colas');
    expect(wrapper.text()).not.toContain('Divisiones');
    expect(
      wrapper.find('button[data-button-previous]').attributes('disabled'),
    ).toBe('disabled');
    expect(wrapper.find('[data-page-range]').text()).toBe(
      pageRange(1, 1, 1, 1, 1),
    );
  });

  it('Should render the filter input above the grid', () => {
    const wrapper = shallowMount(common_GridPaginator, {
      propsData: {
        items,
        itemsPerPage,
        columns,
        title,
        shouldShowFilterInput: true,
      },
    });

    const html = wrapper.html();
    expect(html.indexOf('<input')).toBeLessThan(html.indexOf('<table'));
  });

  it('Should keep the filter focused when nothing matches', async () => {
    const wrapper = shallowMount(common_GridPaginator, {
      attachTo: document.body,
      propsData: {
        items,
        itemsPerPage,
        columns,
        title,
        shouldShowFilterInput: true,
      },
    });

    const input = wrapper.find('input[type="search"]')
      .element as HTMLInputElement;
    input.focus();
    expect(input).toHaveFocus();

    await wrapper.setData({ filter: 'no-such-problem' });

    expect(wrapper.find('table').exists()).toBeFalsy();
    expect(wrapper.find('[data-page-range]').exists()).toBeFalsy();
    expect(input).toHaveFocus();
    wrapper.destroy();
  });
});
