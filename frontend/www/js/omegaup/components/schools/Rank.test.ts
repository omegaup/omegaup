import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import schools_Rank from './Rank.vue';

describe('SchoolRank.vue', () => {
    const propsData = {
        page: 1,
        length: 10,
        showHeader: false,
        totalRows: 100,
        rank: [],
        pagerItems: [
            {
                class: 'disabled',
                label: '1',
                page: 1,
            },
        ],
        searchResultSchools: [],
    };

    it('Should handle an empty rank', () => {
        const wrapper = shallowMount(schools_Rank, {
            propsData,
        });

        expect(wrapper.find('table').exists()).toBe(true);
        expect(wrapper.find('tbody').text()).toBe('');
    });

    it('Should handle a rank with data', () => {
        const wrapper = shallowMount(schools_Rank, {
            propsData: {
                ...propsData,
                ...{
                    rank: [
                        {
                            ranking: 1,
                            country_id: 'MX',
                            school_id: 123,
                            name: 'Test School',
                            score: 234.56,
                        },
                    ],
                },
            },
        });

        expect(wrapper.find('table tbody').text()).toContain('Test School');
        expect(wrapper.find('table tbody').text()).toContain('234.56');
    });

    it('Should show search bar when showHeader is false', () => {
        const wrapper = shallowMount(schools_Rank, {
            propsData,
        });

        expect(wrapper.find('.form-row').exists()).toBe(true);
        expect(wrapper.find('button.btn-primary').text()).toBe(T.searchSchool);
    });

    it('Should hide search bar when showHeader is true', () => {
        const wrapper = shallowMount(schools_Rank, {
            propsData: {
                ...propsData,
                showHeader: true,
            },
        });

        expect(wrapper.find('.form-row').exists()).toBe(false);
    });
});
