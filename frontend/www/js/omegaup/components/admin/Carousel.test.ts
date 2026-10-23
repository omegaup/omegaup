import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import Vue from 'vue';
import { ModalPlugin } from 'bootstrap-vue';
import type { types } from '../../api_types';

import AdminCarousel from './Carousel.vue';

Vue.use(ModalPlugin);

describe('Carousel.vue', () => {
  const activeItem: types.CarouselItem = {
    carousel_item_id: 1,
    title: JSON.stringify({ en: 'Active Title EN', es: 'Active Title ES' }),
    excerpt: JSON.stringify({
      en: 'Active Excerpt EN',
      es: 'Active Excerpt ES',
    }),
    image_url: 'https://example.com/image.png',
    link: 'https://example.com',
    button_title: JSON.stringify({ en: 'Click Here', es: 'Clic Aquí' }),
    is_active: true,
  };

  const archivedItem: types.CarouselItem = {
    carousel_item_id: 2,
    title: JSON.stringify({ en: 'Archived Title EN', es: 'Archived Title ES' }),
    excerpt: JSON.stringify({ en: 'Archived Excerpt EN' }),
    image_url: '',
    link: 'https://example.com/archived',
    button_title: JSON.stringify({ en: 'Archived Button' }),
    is_active: false,
  };

  const expiredItem: types.CarouselItem = {
    carousel_item_id: 3,
    title: JSON.stringify({ en: 'Expired Title EN' }),
    excerpt: JSON.stringify({ en: 'Expired Excerpt' }),
    image_url: 'https://example.com/expired.png',
    link: 'https://example.com/expired',
    button_title: JSON.stringify({ en: 'Expired' }),
    is_active: true,
    expiration_date: new Date('2020-01-01T00:00:00Z'),
  };

  it('Should render carousel items in table', () => {
    const wrapper = shallowMount(AdminCarousel as ComponentOptions<Vue>, {
      propsData: {
        carouselItems: [activeItem, archivedItem],
      },
    });

    expect(wrapper.text()).toContain('Active Title EN');
    // By default currentStatusFilter is 'active', so archived item is hidden
    expect(wrapper.text()).not.toContain('Archived Title EN');
  });

  it('Should filter items based on currentStatusFilter', async () => {
    const wrapper = shallowMount(AdminCarousel as ComponentOptions<Vue>, {
      propsData: {
        carouselItems: [activeItem, archivedItem, expiredItem],
      },
    });

    // Default 'active': only activeItem should show
    expect(wrapper.text()).toContain('Active Title EN');
    expect(wrapper.text()).not.toContain('Archived Title EN');
    expect(wrapper.text()).not.toContain('Expired Title EN');

    // Change filter to 'archived': archivedItem and expiredItem should show
    await wrapper.setData({ currentStatusFilter: 'archived' });
    expect(wrapper.text()).not.toContain('Active Title EN');
    expect(wrapper.text()).toContain('Archived Title EN');
    expect(wrapper.text()).toContain('Expired Title EN');

    // Change filter to 'all': all items should show
    await wrapper.setData({ currentStatusFilter: 'all' });
    expect(wrapper.text()).toContain('Active Title EN');
    expect(wrapper.text()).toContain('Archived Title EN');
    expect(wrapper.text()).toContain('Expired Title EN');
  });

  it('Should change displayed language when currentLanguage changes', async () => {
    const wrapper = shallowMount(AdminCarousel as ComponentOptions<Vue>, {
      propsData: {
        carouselItems: [activeItem],
      },
    });

    expect(wrapper.text()).toContain('Active Title EN');
    expect(wrapper.text()).not.toContain('Active Title ES');

    await wrapper.setData({ currentLanguage: 'es' });
    expect(wrapper.text()).toContain('Active Title ES');
    expect(wrapper.text()).not.toContain('Active Title EN');
  });

  it('Should emit delete-item event when archive is confirmed', async () => {
    const wrapper = shallowMount(AdminCarousel as ComponentOptions<Vue>, {
      propsData: {
        carouselItems: [activeItem],
      },
    });

    const vm = (wrapper.vm as unknown) as {
      confirmDelete: (item: types.CarouselItem) => void;
      archiveSelectedItem: () => void;
    };

    vm.confirmDelete(activeItem);
    vm.archiveSelectedItem();

    expect(wrapper.emitted('delete-item')).toBeTruthy();
    expect(wrapper.emitted('delete-item')?.[0]?.[0]).toBe(
      activeItem.carousel_item_id,
    );
  });

  it('Should populate edit modal state correctly when editItem is called', () => {
    const wrapper = shallowMount(AdminCarousel as ComponentOptions<Vue>, {
      propsData: {
        carouselItems: [activeItem],
      },
    });

    const vm = (wrapper.vm as unknown) as {
      editItem: (item: types.CarouselItem) => void;
      showItemModal: () => void;
      isEditing: boolean;
      currentItem: types.CarouselItem;
      multilingualData: { title: { [k: string]: string } };
    };

    // Mock showItemModal to avoid DOM refs in shallowMount
    jest.spyOn(vm, 'showItemModal').mockImplementation(() => {});

    vm.editItem(activeItem);
    expect(vm.isEditing).toBe(true);
    expect(vm.currentItem.carousel_item_id).toBe(activeItem.carousel_item_id);
    expect(vm.multilingualData.title.en).toBe('Active Title EN');
    expect(vm.multilingualData.title.es).toBe('Active Title ES');
  });
});
