import { createLocalVue, shallowMount } from '@vue/test-utils';
import Vuex, { Store } from 'vuex';
import {
  createNotificationsStore,
  MessageType,
  NotificationsState,
} from '../../notificationsStore';
import GlobalNotifications from './GlobalNotifications.vue';

const localVue = createLocalVue();
localVue.use(Vuex);

// Simple stub for markdown component that just renders the text
const MarkdownStub = {
  props: ['markdown'],
  template: '<div>{{ markdown }}</div>',
};

// Variable to hold the mock store that will be injected
let mockStore: Store<NotificationsState>;

// Shared object to hold dispatch mock - accessible from both mock factory and tests
const mockState = {
  dispatch: jest.fn(),
};

// Mock the notificationsStore module to return our controlled store instance
jest.mock('../../notificationsStore', () => {
  const actual = jest.requireActual('../../notificationsStore');
  return {
    __esModule: true,
    ...actual,
    // Override default export to return getters/dispatch that delegate to mockStore
    get default() {
      return {
        get getters() {
          // Access mockStore via closure from outer scope
          const { createNotificationsStore } = jest.requireActual(
            '../../notificationsStore',
          );
          if (!mockStore) {
            mockStore = createNotificationsStore();
          }
          return mockStore?.getters ?? {};
        },
        dispatch: (...args: [string, any?]) => {
          mockState.dispatch(...args);
          return mockStore?.dispatch(args[0], args[1]);
        },
      };
    },
  };
});

describe('GlobalNotifications.vue', () => {
  beforeEach(() => {
    // Create a fresh store instance for each test
    mockStore = createNotificationsStore();
    mockState.dispatch.mockClear();
  });

  it('should not render alert when not visible', () => {
    // Store starts with visible: false by default
    const wrapper = shallowMount(GlobalNotifications, {
      localVue,
      stubs: {
        'omegaup-markdown': MarkdownStub,
      },
    });

    expect(wrapper.find('.alert').exists()).toBe(false);
  });

  it('should render alert with correct message and danger styling when visible', async () => {
    // Dispatch to show a danger notification
    mockStore.dispatch('displayStatus', {
      message: 'Test error message',
      type: MessageType.Danger,
    });

    const wrapper = shallowMount(GlobalNotifications, {
      localVue,
      stubs: {
        'omegaup-markdown': MarkdownStub,
      },
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
    mockStore.dispatch('displayStatus', {
      message: 'Success!',
      type: MessageType.Success,
    });

    const wrapper = shallowMount(GlobalNotifications, {
      localVue,
      stubs: {
        'omegaup-markdown': MarkdownStub,
      },
    });

    const alert = wrapper.find('.alert');
    expect(alert.exists()).toBe(true);
    expect(alert.classes()).toContain('alert-success');
    expect(wrapper.find('.message').text()).toBe('Success!');
  });

  it('should call dismissNotifications when close button is clicked', async () => {
    mockStore.dispatch('displayStatus', {
      message: 'Test message',
      type: MessageType.Info,
    });

    const wrapper = shallowMount(GlobalNotifications, {
      localVue,
      stubs: {
        'omegaup-markdown': MarkdownStub,
      },
    });

    const closeButton = wrapper.find('.close');
    expect(closeButton.exists()).toBe(true);

    await closeButton.trigger('click');

    expect(mockState.dispatch).toHaveBeenCalledWith('dismissNotifications');
  });

  it('should have dismiss method that dispatches to store', () => {
    const wrapper = shallowMount(GlobalNotifications, {
      localVue,
      stubs: {
        'omegaup-markdown': MarkdownStub,
      },
    });

    // Call dismiss method directly
    (wrapper.vm as any).dismiss();

    expect(mockState.dispatch).toHaveBeenCalledWith('dismissNotifications');
  });
});
