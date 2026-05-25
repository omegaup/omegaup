// Mock vue-typeahead-bootstrap for Jest tests
export default {
  name: 'VueTypeaheadBootstrap',
  props: {
    value: [String, Number],
    data: Array,
    serializer: Function,
    screenReaderTextSerializer: Function,
    showOnFocus: Boolean,
    showAllResults: Boolean,
    maxMatches: Number,
    minMatchingChars: Number,
    appendToBody: Boolean,
    highlightClass: String,
    disabled: Boolean,
    placeholder: String,
    prepend: String,
    append: String,
    inputClass: String,
    maxlength: Number,
    autofocus: Boolean,
  },
  render() {
    return null;
  },
};
