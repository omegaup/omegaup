import { h } from 'vue';

export default {
  name: 'VueCodemirrorLite',
  props: ['code', 'options'],
  render() {
    return h('div', { class: 'vue-codemirror-lite-mock' });
  },
};
