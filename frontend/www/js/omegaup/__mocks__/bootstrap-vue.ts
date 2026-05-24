// Mock bootstrap-vue for Jest tests to avoid Vue 3 compat issues.
// In Vue 3 compat MODE:2, unknown tags (<b-button>, <b-card>, etc.)
// are rendered as native custom elements preserving slot content.
// This mock exports proper component objects for findComponent() and
// direct imports while keeping plugins as no-ops.
import { h } from 'vue';

function makeComponent(tag, defaultAttrs) {
  defaultAttrs = defaultAttrs || {};
  return {
    name: tag,
    render() {
      const children = [];
      for (const slotName of Object.keys(this.$slots)) {
        const slotFn = this.$slots[slotName];
        if (typeof slotFn === 'function') {
          children.push(...slotFn());
        }
      }
      return h(tag, { ...defaultAttrs, ...this.$attrs }, children);
    },
  };
}

const noopPlugin = {
  install() {},
};

const BButton = makeComponent('button');
const BCard = makeComponent('div');
const BModal = makeComponent('div');
const BTable = makeComponent('table');
const BFormInput = makeComponent('input');
const BFormSelect = makeComponent('select');
const BFormTextarea = makeComponent('textarea');
const BFormCheckbox = makeComponent('input', { type: 'checkbox' });
const BFormGroup = makeComponent('div');
const BForm = makeComponent('form');
const BDropdown = makeComponent('div');
const BDropdownItem = makeComponent('a');
const BDropdownDivider = makeComponent('hr');
const BDropdownHeader = makeComponent('h6');
const BPopover = makeComponent('div');
const BAlert = makeComponent('div');
const BBadge = makeComponent('span');
const BBreadcrumb = makeComponent('nav');
const BBreadcrumbItem = makeComponent('li');
const BCardBody = makeComponent('div');
const BCardHeader = makeComponent('div');
const BCardText = makeComponent('p');
const BCol = makeComponent('div');
const BCollapse = makeComponent('div');
const BContainer = makeComponent('div');

const BIconPencilFill = makeComponent('i');
const BIconTrashFill = makeComponent('i');
const BIconPlus = makeComponent('i');
const BIconCheck = makeComponent('i');
const BIconX = makeComponent('i');
const BIconArrowLeftCircleFill = makeComponent('i');
const BIconArrowRightCircleFill = makeComponent('i');
const BIconCaretDownSquare = makeComponent('i');
const BIconCaretUpSquare = makeComponent('i');
const BIconCheckCircleFill = makeComponent('i');
const BIconChevronDown = makeComponent('i');
const BIconChevronLeft = makeComponent('i');
const BIconChevronRight = makeComponent('i');
const BIconCloudDownload = makeComponent('i');
const BIconPrinter = makeComponent('i');
const BIconQuestionCircleFill = makeComponent('i');
const BIconXCircleFill = makeComponent('i');

// Additional icon components used in the codebase
const BIconUpload = makeComponent('i');
const BIconDownload = makeComponent('i');
const BIconThreeDotsVertical = makeComponent('i');
const BIconTrash = makeComponent('i');
const BIconBoxArrowDown = makeComponent('i');
const BIconTextLeft = makeComponent('i');
const BIconPencilSquare = makeComponent('i');
const BIconPlusSquare = makeComponent('i');
const BIconLayoutSidebar = makeComponent('i');
const BIconPlusCircle = makeComponent('i');
const BIconBroadcast = makeComponent('i');
const BIconPencil = makeComponent('i');
const BIconArrowLeftRight = makeComponent('i');
const BIconArrowRepeat = makeComponent('i');
const BIconBoxArrowInDown = makeComponent('i');
const BIconFileCode = makeComponent('i');
const BIconCheckCircle = makeComponent('i');
const BIconFileEarmarkCheck = makeComponent('i');

const BNav = makeComponent('nav');
const BNavItem = makeComponent('li');
const BPagination = makeComponent('ul');
const BRow = makeComponent('div');
const BSidebar = makeComponent('div');
const BTab = makeComponent('div');
const BTabs = makeComponent('div');

module.exports = {
  ButtonPlugin: noopPlugin,
  DropdownPlugin: noopPlugin,
  LayoutPlugin: noopPlugin,
  TabsPlugin: noopPlugin,
  CardPlugin: noopPlugin,
  TablePlugin: noopPlugin,
  ModalPlugin: noopPlugin,
  FormInputPlugin: noopPlugin,
  PopoverPlugin: noopPlugin,
  BadgePlugin: noopPlugin,
  PaginationPlugin: noopPlugin,
  CollapsePlugin: noopPlugin,
  IconsPlugin: noopPlugin,
  BootstrapVue: noopPlugin,
  BootstrapVueIcons: noopPlugin,

  BButton,
  BCard,
  BModal,
  BTable,
  BFormInput,
  BFormSelect,
  BFormTextarea,
  BFormCheckbox,
  BFormGroup,
  BForm,
  BDropdown,
  BDropdownItem,
  BDropdownDivider,
  BDropdownHeader,
  BPopover,
  BAlert,
  BBadge,
  BBreadcrumb,
  BBreadcrumbItem,
  BCardBody,
  BCardHeader,
  BCardText,
  BCol,
  BCollapse,
  BContainer,
  BIconPencilFill,
  BIconTrashFill,
  BIconPlus,
  BIconCheck,
  BIconX,
  BIconArrowLeftCircleFill,
  BIconArrowRightCircleFill,
  BIconCaretDownSquare,
  BIconCaretUpSquare,
  BIconCheckCircleFill,
  BIconChevronDown,
  BIconChevronLeft,
  BIconChevronRight,
  BIconCloudDownload,
  BIconPrinter,
  BIconQuestionCircleFill,
  BIconXCircleFill,
  BIconUpload,
  BIconDownload,
  BIconThreeDotsVertical,
  BIconTrash,
  BIconBoxArrowDown,
  BIconTextLeft,
  BIconPencilSquare,
  BIconPlusSquare,
  BIconLayoutSidebar,
  BIconPlusCircle,
  BIconBroadcast,
  BIconPencil,
  BIconArrowLeftRight,
  BIconArrowRepeat,
  BIconBoxArrowInDown,
  BIconFileCode,
  BIconCheckCircle,
  BIconFileEarmarkCheck,
  BNav,
  BNavItem,
  BPagination,
  BRow,
  BSidebar,
  BTab,
  BTabs,
};
