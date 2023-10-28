import { StoryObj, Meta } from "@storybook/vue";
import badge_Details from "./Details.vue";

const meta: Meta<typeof badge_Details> = {
    component: badge_Details,
    title: "Components/Badge/Details",
    argTypes: {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore FIXME: vue-property-decorator is deprecated, so we can't get prop types from the component
        badge_alias: {
            control: "text",
        },
        unlocked: {
            control: "boolean",
        },
        first_assignation: {
            control: "date",
        },
        assignation_time: {
            control: "date",
        },
        total_users: {
            control: "number",
        },
        owners_count: {
            control: "number",
        }
    },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        badge_alias: '100solvedProblems',
        unlocked: true,
        first_assignation: new Date(),
        assignation_time: new Date(),
        total_users: 100,
        owners_count: 10,
    },
    render: (args, {argTypes }) => ({
        components: { badge_Details },
        props: Object.keys(argTypes),
        template: '<badge_Details :badge="$props" />',
    }),
};

Default.storyName = "Details";