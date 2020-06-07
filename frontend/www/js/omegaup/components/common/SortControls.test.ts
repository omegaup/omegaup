import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import * as ui from '../../ui';

import common_SortControls from './SortControls.vue';

describe('SortControls.vue', () => {
  it('Should handle the correct sort control', async () => {
    const wrapper = shallowMount(common_SortControls, {
      propsData: {
        column: 'title',
        columnType: 'number',
        sortOrder: 'desc',
        columnName: 'ratio',
      },
    });

    expect(wrapper.vm.selected).toBe(false);
    expect(wrapper.vm.toggleSort).toBe('asc');
    expect(wrapper.vm.iconDisplayed).toBe('sort-amount-down');
  });

  it('Should handle the correct sort icons', async () => {
    const wrapper = shallowMount(common_SortControls, {
      propsData: {
        column: 'title',
        columnType: 'string',
        sortOrder: 'desc',
        columnName: 'title',
      },
    });

    await wrapper.find('a').trigger('click');

    expect(wrapper.emitted('emit-apply-filter')).toEqual([['title', 'asc']]);
  });
});
