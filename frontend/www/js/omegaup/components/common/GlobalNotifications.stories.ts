import { Meta, StoryObj } from '@storybook/vue';
import notificationsStore, {
  MessageType,
  NotificationPosition,
} from '../../notificationsStore';
import GlobalNotifications from './GlobalNotifications.vue';

/**
 * GlobalNotifications displays platform-wide notification banners.
 * It supports different message types (danger, success, warning, info)
 * and positions (top, bottom, top-right, bottom-right).
 */
const meta: Meta<typeof GlobalNotifications> = {
  component: GlobalNotifications,
  title: 'Components/Common/GlobalNotifications',
  argTypes: {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore FIXME: vue-property-decorator is deprecated
    message: {
      control: 'text',
      description: 'The notification message to display',
    },
    type: {
      control: 'select',
      options: Object.values(MessageType),
      description: 'The type of notification (affects styling)',
    },
    position: {
      control: 'select',
      options: Object.values(NotificationPosition),
      description: 'Position of the notification on screen',
    },
  },
  parameters: {
    layout: 'fullscreen',
  },
  // Decorator to dispatch notifications before each story renders
  decorators: [
    (story, context) => {
      // Clear any previous notifications first to prevent accumulation
      notificationsStore.dispatch('dismissNotifications');
      // Dispatch the notification to the store before rendering
      notificationsStore.dispatch('displayStatus', {
        message: context.args.message,
        type: context.args.type,
        position: context.args.position,
        autoHide: false, // Disable auto-hide in stories
      });
      return story();
    },
  ],
};

export default meta;

type Story = StoryObj<typeof meta>;

// Template for all stories
const Template = (minHeight = '200px') => ({
  components: { GlobalNotifications },
  template: `
    <div style="min-height: ${minHeight}; position: relative; background: #f5f5f5;">
      <global-notifications />
      <div style="padding: 20px; color: #666;">
        <p>Page content goes here...</p>
      </div>
    </div>
  `,
});

export const DangerNotification: Story = {
  args: {
    message: 'An error occurred while processing your request.',
    type: MessageType.Danger,
    position: NotificationPosition.Top,
  },
  render: () => Template(),
};
DangerNotification.storyName = 'Danger (Error)';

export const SuccessNotification: Story = {
  args: {
    message: 'Your changes have been saved successfully!',
    type: MessageType.Success,
    position: NotificationPosition.Top,
  },
  render: () => Template(),
};
SuccessNotification.storyName = 'Success';

export const WarningNotification: Story = {
  args: {
    message: 'Please review your submission before proceeding.',
    type: MessageType.Warning,
    position: NotificationPosition.Top,
  },
  render: () => Template(),
};
WarningNotification.storyName = 'Warning';

export const InfoNotification: Story = {
  args: {
    message: 'A new version of the platform is available.',
    type: MessageType.Info,
    position: NotificationPosition.Top,
  },
  render: () => Template(),
};
InfoNotification.storyName = 'Info';

export const BottomPosition: Story = {
  args: {
    message: 'This notification appears at the bottom of the page.',
    type: MessageType.Info,
    position: NotificationPosition.Bottom,
  },
  render: () => Template('400px'),
};
BottomPosition.storyName = 'Bottom Position';

export const TopRightToast: Story = {
  args: {
    message: 'Toast notification in the top-right corner.',
    type: MessageType.Success,
    position: NotificationPosition.TopRight,
  },
  render: () => Template(),
};
TopRightToast.storyName = 'Top-Right Toast';

export const BottomRightToast: Story = {
  args: {
    message: 'Toast notification in the bottom-right corner.',
    type: MessageType.Warning,
    position: NotificationPosition.BottomRight,
  },
  render: () => Template('400px'),
};
BottomRightToast.storyName = 'Bottom-Right Toast';
