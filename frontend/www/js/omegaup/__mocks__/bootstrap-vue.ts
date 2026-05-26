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
      // Return a fragment (array) so that ref on this component points to
      // the component instance rather than the native element (VTU v2 behavior).
      return [h(tag, { ...defaultAttrs, ...this.$attrs }, children)];
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
const BFormRadio = makeComponent('input', { type: 'radio' });
const BFormRadioGroup = makeComponent('div');
const BImg = makeComponent('img');
const BInputGroup = makeComponent('div');
const BInputGroupAppend = makeComponent('div');
const BInputGroupPrepend = makeComponent('div');
const BListGroup = makeComponent('ul');
const BListGroupItem = makeComponent('li');
const BNav = makeComponent('nav');
const BNavItem = makeComponent('a');
const BNavbar = makeComponent('nav');
const BNavbarBrand = makeComponent('a');
const BNavbarNav = makeComponent('div');
const BNavbarToggle = makeComponent('button');
const BPagination = makeComponent('div');
const BProgress = makeComponent('div');
const BProgressBar = makeComponent('div');
const BRow = makeComponent('div');
const BSpinner = makeComponent('div');
const BTab = makeComponent('div');
const BTabs = makeComponent('div');
const BToast = makeComponent('div');
const BToaster = makeComponent('div');
const BTooltip = makeComponent('div');
const BTr = makeComponent('tr');
const BTd = makeComponent('td');
const BTh = makeComponent('th');
const BThead = makeComponent('thead');
const BTbody = makeComponent('tbody');
const BFormFile = makeComponent('input', { type: 'file' });
const BFormDatepicker = makeComponent('input', { type: 'date' });
const BTime = makeComponent('time');
const BLink = makeComponent('a');
const BAvatar = makeComponent('div');
const BButtonGroup = makeComponent('div');
const BButtonToolbar = makeComponent('div');
const BCardFooter = makeComponent('div');
const BCardGroup = makeComponent('div');
const BCardImg = makeComponent('img');
const BCardSubTitle = makeComponent('h6');
const BCardTitle = makeComponent('h5');
const BCarousel = makeComponent('div');
const BCarouselSlide = makeComponent('div');
const BFormDatalist = makeComponent('datalist');
const BFormInvalidFeedback = makeComponent('div');
const BFormValidFeedback = makeComponent('div');
const BFormRow = makeComponent('div');
const BJumbotron = makeComponent('div');
const BMedia = makeComponent('div');
const BMediaAside = makeComponent('div');
const BMediaBody = makeComponent('div');
const BSkeleton = makeComponent('div');
const BSkeletonIcon = makeComponent('div');
const BSkeletonImg = makeComponent('div');
const BSkeletonTable = makeComponent('div');
const BSkeletonWrapper = makeComponent('div');

export {
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
  BFormRadio,
  BFormRadioGroup,
  BImg,
  BInputGroup,
  BInputGroupAppend,
  BInputGroupPrepend,
  BListGroup,
  BListGroupItem,
  BNav,
  BNavItem,
  BNavbar,
  BNavbarBrand,
  BNavbarNav,
  BNavbarToggle,
  BPagination,
  BProgress,
  BProgressBar,
  BRow,
  BSpinner,
  BTab,
  BTabs,
  BToast,
  BToaster,
  BTooltip,
  BTr,
  BTd,
  BTh,
  BThead,
  BTbody,
  BFormFile,
  BFormDatepicker,
  BTime,
  BLink,
  BAvatar,
  BButtonGroup,
  BButtonToolbar,
  BCardFooter,
  BCardGroup,
  BCardImg,
  BCardSubTitle,
  BCardTitle,
  BCarousel,
  BCarouselSlide,
  BFormDatalist,
  BFormInvalidFeedback,
  BFormValidFeedback,
  BFormRow,
  BJumbotron,
  BMedia,
  BMediaAside,
  BMediaBody,
  BSkeleton,
  BSkeletonIcon,
  BSkeletonImg,
  BSkeletonTable,
  BSkeletonWrapper,
};

export default noopPlugin;
