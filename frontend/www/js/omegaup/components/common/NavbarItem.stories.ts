import { Meta, StoryObj } from '@storybook/vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faBook, faComments, faVideo } from '@fortawesome/free-solid-svg-icons';
import NavbarItem from './NavbarItem.vue';

library.add(faBook, faComments, faVideo);

const meta: Meta<typeof NavbarItem> = {
  component: NavbarItem,
  title: 'Components/Common/NavbarItem',
};

export default meta;

type Story = StoryObj<typeof meta>;

export const TupleIcon: Story = {
  args: {
    title: 'Tutorials',
    description: 'Learn with our video tutorials',
    icon: ['fas', 'video'],
    href: '/videos/',
  },
};
TupleIcon.storyName = 'Icon as [prefix, name] tuple';

export const StringIcon: Story = {
  args: {
    title: 'Algorithms book',
    description: 'Introduction to online judges',
    icon: 'book',
    href: '/book/',
  },
};
StringIcon.storyName = 'Icon as plain string';

export const ExternalLink: Story = {
  args: {
    title: 'Discord',
    description: 'Join the community chat',
    icon: ['fas', 'comments'],
    href: 'https://discord.example/invite',
    target: '_blank',
  },
};
ExternalLink.storyName = 'External link marker';

export const PlainTitle: Story = {
  args: {
    title: 'Plain option',
    href: '/plain/',
  },
};
PlainTitle.storyName = 'Title only';
