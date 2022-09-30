import Vue from 'vue';

export default {
  open(
    modalComponent,
    modalData,
    modalOptions = { maxWidth: 650, persistent: false },
  ) {
    return new Promise((resolve) => {
      let extendedModalComponent = Vue.extend(modalComponent).extend({
        props: {
          showDialog: Boolean,
          modalData: Object | undefined,
        },
        methods: {
          closeModal(data) {
            this.$emit('update:showDialog', false);
            resolve(data);
          },
        },
      });
      let tempComponent = Vue.component('modal-component', {
        data() {
          return {
            showDialog: true,
            modalData: modalData,
            modalOptions: modalOptions,
            styles: {
              padding: '16px',
              'box-shadow': '0 2px 4px 0 rgba(38,38,38,.08) !important',
              background: 'white',
            },
          };
        },
        components: {
          ModalComponent: extendedModalComponent,
        },
        methods: {
          closeModal(data) {
            this.showDialog = false;
            resolve(data);
          },
        },
        template: modalTemplate,
      });
      let ModalInstance = new tempComponent();
      ModalInstance.$mount();
    });
  },
};
