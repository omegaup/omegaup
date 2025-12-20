import { shallowMount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import GlobalNotifications from './GlobalNotifications.vue';
import { MessageType } from '../../notificationsStore';

// Mock the notificationsStore module
jest.mock('../../notificationsStore', () => {
    const mockStore = {
        getters: {
            isVisible: false,
            message: null,
            type: null,
            alertClass: '',
        },
        dispatch: jest.fn(),
    };
    return {
        __esModule: true,
        default: mockStore,
        MessageType: {
            Danger: 'alert-danger',
            Info: 'alert-info',
            Success: 'alert-success',
            Warning: 'alert-warning',
        },
    };
});

// Import after mocking
import notificationsStore from '../../notificationsStore';

const localVue = createLocalVue();
localVue.use(Vuex);

describe('GlobalNotifications.vue', () => {
    beforeEach(() => {
        // Reset mock state before each test
        (notificationsStore.getters as any).isVisible = false;
        (notificationsStore.getters as any).message = null;
        (notificationsStore.getters as any).type = null;
        (notificationsStore.getters as any).alertClass = '';
        (notificationsStore.dispatch as jest.Mock).mockClear();
    });

    it('should not render alert when not visible', () => {
        (notificationsStore.getters as any).isVisible = false;

        const wrapper = shallowMount(GlobalNotifications, {
            localVue,
        });

        expect(wrapper.find('.alert').exists()).toBe(false);
    });

    it('should render alert with correct message and danger styling when visible', () => {
        // Set mock store state to visible with danger type
        (notificationsStore.getters as any).isVisible = true;
        (notificationsStore.getters as any).message = 'Test error message';
        (notificationsStore.getters as any).type = MessageType.Danger;
        (notificationsStore.getters as any).alertClass = MessageType.Danger;

        const wrapper = shallowMount(GlobalNotifications, {
            localVue,
        });

        // Assert alert is rendered
        const alert = wrapper.find('.alert');
        expect(alert.exists()).toBe(true);

        // Assert correct danger styling class is applied
        expect(alert.classes()).toContain('alert-danger');

        // Assert message text is rendered correctly
        const messageSpan = wrapper.find('.message');
        expect(messageSpan.exists()).toBe(true);
        expect(messageSpan.text()).toBe('Test error message');
    });

    it('should render alert with success styling', () => {
        (notificationsStore.getters as any).isVisible = true;
        (notificationsStore.getters as any).message = 'Success!';
        (notificationsStore.getters as any).type = MessageType.Success;
        (notificationsStore.getters as any).alertClass = MessageType.Success;

        const wrapper = shallowMount(GlobalNotifications, {
            localVue,
        });

        const alert = wrapper.find('.alert');
        expect(alert.exists()).toBe(true);
        expect(alert.classes()).toContain('alert-success');
        expect(wrapper.find('.message').text()).toBe('Success!');
    });

    it('should call dismissNotifications when close button is clicked', async () => {
        (notificationsStore.getters as any).isVisible = true;
        (notificationsStore.getters as any).message = 'Test message';
        (notificationsStore.getters as any).alertClass = MessageType.Info;

        const wrapper = shallowMount(GlobalNotifications, {
            localVue,
        });

        const closeButton = wrapper.find('.close');
        expect(closeButton.exists()).toBe(true);

        await closeButton.trigger('click');

        expect(notificationsStore.dispatch).toHaveBeenCalledWith(
            'dismissNotifications',
        );
    });

    it('should have dismiss method that dispatches to store', () => {
        const wrapper = shallowMount(GlobalNotifications, {
            localVue,
        });

        // Call dismiss method directly
        (wrapper.vm as any).dismiss();

        expect(notificationsStore.dispatch).toHaveBeenCalledWith(
            'dismissNotifications',
        );
    });
});
