import { mount } from '@vue/test-utils';

import admin_AdminLayout from './AdminLayout.vue';
import { AccessLevel, AvailableModules } from '../../admin/operations_modules';

describe('AdminLayout.vue', () => {
    beforeEach(() => {
        window.matchMedia = jest.fn().mockReturnValue({ matches: true });
    });

    const getHrefs = (wrapper: ReturnType<typeof mount>): string[] =>
        wrapper
            .findAll('a.admin-dashboard-sidebar__item')
            .wrappers.map((link) => link.attributes('href') as string);

    it('Should show every section to admins', () => {
        const wrapper = mount(admin_AdminLayout, {
            propsData: { accessLevel: AccessLevel.Admin },
        });

        expect(getHrefs(wrapper)).toEqual([
            '#home',
            '#settings',
            '/admin/user/',
            '/admin/crons/',
            '/admin/support/',
        ]);
    });

    it('Should hide admin-only sections from support users', () => {
        const wrapper = mount(admin_AdminLayout, {
            propsData: { accessLevel: AccessLevel.Support },
        });

        expect(getHrefs(wrapper)).toEqual([
            '#home',
            '/admin/user/',
            '/admin/support/',
        ]);
    });

    it('Should highlight only the selected section', () => {
        const wrapper = mount(admin_AdminLayout, {
            propsData: {
                accessLevel: AccessLevel.Admin,
                selectedModule: AvailableModules.Settings,
            },
        });

        const settingsLink = wrapper.find('a[href="#settings"]');
        expect(settingsLink.classes()).toContain(
            'admin-dashboard-sidebar__item--active',
        );
        expect(settingsLink.attributes('aria-current')).toBe('page');
        expect(wrapper.find('a[href="#home"]').classes()).not.toContain(
            'admin-dashboard-sidebar__item--active',
        );
    });
});