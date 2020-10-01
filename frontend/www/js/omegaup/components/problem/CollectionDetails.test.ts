import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import collection_Details from './CollectionDetails.vue';

describe('CollectionDetails.vue', () => {
  it('Should handle empty details of problem collection', async () => {
    const wrapper = shallowMount(collection_Details, {});

    expect(wrapper.text()).toContain(T.collectionTitle);
  });
});
