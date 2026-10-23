import { shallowMount } from '@vue/test-utils';

import common_TablePaginator from './TablePaginator.vue';
import { LinkableResource, Problem } from '../../linkable_resource';

describe('TablePaginator.vue', () => {
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
  const columnNames = [{ name: 'username', style: '' }];

  it('Should handle an empty table', () => {
    const wrapper = shallowMount(common_TablePaginator, {
      propsData: {
        items: [],
        itemsPerPage: 5,
        columnNames,
        title,
      },
    });

    expect(wrapper.find('h5.card-header').text()).toContain(title);
    expect(wrapper.find('table').exists()).toBeFalsy();
  });

  it('Should handle a table with rows', async () => {
    const title = 'Coder of the month';
    const wrapper = shallowMount(common_TablePaginator, {
      propsData: {
        items,
        itemsPerPage: 5,
        columnNames,
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

  it('Should handle a table with rows and several pages', async () => {
    const title = 'Coder of the month';
    const wrapper = shallowMount(common_TablePaginator, {
      propsData: {
        items,
        itemsPerPage: 1,
        columnNames,
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

  it('Should reset to the first page when items prop updates from a later page', async () => {
    const wrapper = shallowMount(common_TablePaginator, {
      propsData: {
        items,
        itemsPerPage: 1,
        columnNames,
        title,
      },
    });

    await wrapper.find('button[data-button-next]').trigger('click');
    expect(wrapper.text()).toContain('Filas');
    expect(wrapper.text()).not.toContain('Colas');
    expect(
      wrapper.find('button[data-button-previous]').attributes('disabled'),
    ).toBeUndefined();

    await wrapper.setProps({ items: [items[0]] });

    expect(wrapper.text()).toContain('Colas');
    expect(wrapper.text()).not.toContain('Filas');
    expect(
      wrapper.find('button[data-button-previous]').attributes('disabled'),
    ).toBe('disabled');
    expect(
      wrapper.find('button[data-button-next]').attributes('disabled'),
    ).toBe('disabled');
  });

  it('Should reset to the first page when sort option changes', async () => {
    const sortOptions = [
      { value: 'asc', title: 'Ascending' },
      { value: 'desc', title: 'Descending' },
    ];
    const wrapper = shallowMount(common_TablePaginator, {
      propsData: {
        items,
        itemsPerPage: 1,
        columnNames,
        title,
        sortOptions,
      },
    });

    await wrapper.find('button[data-button-next]').trigger('click');
    expect(wrapper.text()).toContain('Filas');

    const radioInputs = wrapper.findAll('input[name="sort-selector"]');
    await radioInputs.at(1).setChecked();

    expect(wrapper.emitted('sort-option-change')).toEqual([['desc']]);
    expect(wrapper.text()).toContain('Colas');
    expect(
      wrapper.find('button[data-button-previous]').attributes('disabled'),
    ).toBe('disabled');
  });

  it('Should handle non-positive itemsPerPage without division by zero or empty table', () => {
    const wrapperZero = shallowMount(common_TablePaginator, {
      propsData: {
        items,
        itemsPerPage: 0,
        columnNames,
        title,
      },
    });

    expect(wrapperZero.find('table').exists()).toBeTruthy();
    expect(wrapperZero.text()).toContain('Colas');

    const wrapperNegative = shallowMount(common_TablePaginator, {
      propsData: {
        items,
        itemsPerPage: -5,
        columnNames,
        title,
      },
    });

    expect(wrapperNegative.find('table').exists()).toBeTruthy();
    expect(wrapperNegative.text()).toContain('Colas');
  });

  it('Should clamp navigation methods to valid page bounds', async () => {
    const wrapper = shallowMount(common_TablePaginator, {
      propsData: {
        items,
        itemsPerPage: 1,
        columnNames,
        title,
      },
    });

    const vm = wrapper.vm as any;
    expect(vm.currentPageNumber).toBe(0);

    // Should not decrement below 0
    vm.previousPage();
    expect(vm.currentPageNumber).toBe(0);

    // Navigate to page 1
    vm.nextPage();
    expect(vm.currentPageNumber).toBe(1);

    // Navigate to page 2 (last page)
    vm.nextPage();
    expect(vm.currentPageNumber).toBe(2);

    // Should not increment beyond last page
    vm.nextPage();
    expect(vm.currentPageNumber).toBe(2);
  });

  it('Should reset to the first page when itemsPerPage changes', async () => {
    const wrapper = shallowMount(common_TablePaginator, {
      propsData: {
        items,
        itemsPerPage: 1,
        columnNames,
        title,
      },
    });

    await wrapper.find('button[data-button-next]').trigger('click');
    const vm = wrapper.vm as any;
    expect(vm.currentPageNumber).toBe(1);

    await wrapper.setProps({ itemsPerPage: 2 });
    expect(vm.currentPageNumber).toBe(0);
  });
});
