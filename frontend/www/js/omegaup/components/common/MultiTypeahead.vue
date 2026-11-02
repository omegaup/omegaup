<template>
  <multiselect
    :value="selectedOptions"
    class="omegaup-typeahead omegaup-typeahead--multiple"
    open-direction="bottom"
    :options="visibleOptions"
    :multiple="true"
    :searchable="true"
    :internal-search="false"
    :close-on-select="false"
    :clear-on-select="true"
    :options-limit="maxResults"
    :placeholder="T.typeaheadSearchPlaceholder"
    :show-labels="false"
    :show-no-results="false"
    :show-no-options="false"
    label="value"
    track-by="key"
    @search-change="updateExistingOptions"
    @input="onInput"
  >
    <template slot="tag" slot-scope="{ option, remove }">
      <span class="multiselect__tag">
        <span
          v-for="(part, index) in labelParts(optionLabel(option))"
          :key="index"
        >
          <strong v-if="part.strong">{{ part.text }}</strong>
          <template v-else>{{ part.text }}</template>
        </span>
        <i
          aria-hidden="true"
          tabindex="1"
          class="multiselect__tag-icon"
          @keypress.enter.prevent="remove(option)"
          @mousedown.prevent="remove(option)"
        ></i>
      </span>
    </template>
    <template slot="option" slot-scope="{ option }">
      <span
        v-for="(part, index) in labelParts(optionLabel(option))"
        :key="index"
      >
        <strong v-if="part.strong">{{ part.text }}</strong>
        <template v-else>{{ part.text }}</template>
      </span>
    </template>
  </multiselect>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import T from '../../lang';
import { types } from '../../api_types';
import { typeaheadLabelParts } from './typeahead_label';

function optionLabel(option: { value?: string; label?: string }): string {
  return option.value || option.label || '';
}

@Component({
  components: {
    Multiselect,
  },
})
export default class MultiTypeahead extends Vue {
  @Prop() existingOptions!: types.ListItem[];
  @Prop({ default: 3 }) activationThreshold!: number;
  @Prop({ default: 10 }) maxResults!: number;
  @Prop({ default: () => [] }) value!: null | types.ListItem[];

  T = T;
  optionLabel = optionLabel;
  labelParts = typeaheadLabelParts;
  selectedOptions: types.ListItem[] = this.value ?? [];
  searchQuery = '';

  get visibleOptions(): types.ListItem[] {
    if (this.searchQuery.length < this.activationThreshold) return [];
    return this.existingOptions;
  }

  updateExistingOptions(query: string): void {
    this.searchQuery = query;
    if (query.length < this.activationThreshold) return;
    this.$emit('update-existing-options', query);
  }

  onInput(options: null | types.ListItem[]): void {
    this.selectedOptions = options ?? [];
    this.$emit('update:value', this.selectedOptions);
  }

  @Watch('value')
  onValueChanged(newValue: null | types.ListItem[]): void {
    this.selectedOptions = newValue ?? [];
  }
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';

.omegaup-typeahead {
  .multiselect__tag,
  .multiselect__option--highlight {
    background: $omegaup-primary--darker;
  }

  &.omegaup-typeahead--multiple .multiselect__input {
    position: relative !important;
    width: 100% !important;
    padding: 0 !important;
    margin-bottom: 0;
  }

  .multiselect__placeholder {
    display: none;
  }
}
</style>
