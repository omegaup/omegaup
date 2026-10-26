<template>
  <multiselect
    :value="selectedOption"
    class="omegaup-typeahead"
    :class="{
      'is-invalid': isInvalid,
      'omegaup-typeahead--filled': !!selectedOption,
    }"
    open-direction="bottom"
    :tag-placeholder="''"
    :options="existingOptions"
    :multiple="false"
    :searchable="true"
    :internal-search="false"
    :close-on-select="true"
    :clear-on-select="true"
    :options-limit="maxResults"
    :placeholder="placeholder"
    :disabled="isDisabled"
    :taggable="allowNewTags"
    :show-labels="false"
    :show-no-results="false"
    :show-no-options="false"
    :allow-empty="true"
    label="value"
    track-by="key"
    @search-change="updateExistingOptions"
    @input="onInput"
    @tag="onTag"
  >
    <template slot="singleLabel" slot-scope="{ option }">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <span class="omegaup-typeahead-label" v-html="optionLabel(option)"></span>
      <button
        type="button"
        class="omegaup-typeahead-remove"
        @mousedown.prevent.stop="clearSelection"
      ></button>
    </template>
    <template slot="option" slot-scope="{ option }">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <span v-html="optionLabel(option)"></span>
    </template>
  </multiselect>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import T from '../../lang';
import { types } from '../../api_types';

function optionLabel(option: { value?: string; label?: string }): string {
  return option.value || option.label || '';
}

function hasContent(item: null | types.ListItem): item is types.ListItem {
  if (!item) return false;
  return String(item.key) !== '' || item.value !== '';
}

@Component({
  components: {
    Multiselect,
  },
})
export default class Typeahead extends Vue {
  @Prop({ default: () => [] }) existingOptions!: types.ListItem[];
  @Prop({ default: () => [] }) options!: types.ListItem[];
  @Prop({ default: 3 }) activationThreshold!: number;
  @Prop({ default: 5 }) maxResults!: number;
  @Prop({ default: null }) value!: null | types.ListItem;
  @Prop({ default: true }) onlyExistingTags!: boolean;
  @Prop({ default: false }) readonly!: boolean;
  @Prop({ default: false }) disabled!: boolean;
  @Prop({ default: true }) typeaheadHideDiscard!: boolean;
  @Prop({ default: T.typeaheadSearchPlaceholder }) placeholder!: string;
  @Prop({ default: 300 }) debounceDelay!: number;
  @Prop({ default: false }) isInvalid!: boolean;

  T = T;
  optionLabel = optionLabel;
  selectedOption: null | types.ListItem = hasContent(this.value)
    ? this.value
    : null;
  private debounceTimer: ReturnType<typeof setTimeout> | null = null;

  get isDisabled(): boolean {
    return this.readonly || this.disabled;
  }

  // Callers that allow new tags (the problem keyword search) keep the typed
  // text on Enter. Everyone else can only pick a server result.
  get allowNewTags(): boolean {
    return !this.onlyExistingTags;
  }

  updateExistingOptions(query: string): void {
    if (query.length < this.activationThreshold) return;

    if (this.debounceTimer) {
      clearTimeout(this.debounceTimer);
    }

    this.debounceTimer = setTimeout(() => {
      this.$emit('update-existing-options', query);
    }, this.debounceDelay);
  }

  onInput(option: null | types.ListItem): void {
    this.selectedOption = option;
    this.$emit('update:value', option);
  }

  onTag(query: string): void {
    const item: types.ListItem = { key: query, value: query };
    this.selectedOption = item;
    this.$emit('update:value', item);
  }

  clearSelection(): void {
    this.selectedOption = null;
    this.$emit('update:value', null);
  }

  beforeDestroy(): void {
    if (this.debounceTimer) {
      clearTimeout(this.debounceTimer);
    }
  }

  @Watch('value')
  onValueChanged(newValue: null | types.ListItem): void {
    this.selectedOption = hasContent(newValue) ? newValue : null;
  }
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';

.omegaup-typeahead {
  min-height: 38px;

  .multiselect__tags {
    min-height: 38px;
    padding: 0.375rem 2rem 0.375rem 0.75rem;
  }

  // The library collapses the search box to 0px until the menu opens. Keep it
  // typeable while nothing is selected, which is how the old tags input worked.
  &:not(.omegaup-typeahead--filled) .multiselect__input {
    position: relative !important;
    width: 100% !important;
    padding: 0 !important;
    margin-bottom: 0;
  }

  .multiselect__single {
    display: flex;
    align-items: center;
    margin-bottom: 0;
    background: transparent;
  }

  .multiselect__option--highlight {
    background: $omegaup-primary--darker;
  }

  .multiselect__content-wrapper {
    z-index: 1060;
  }

  .multiselect__option {
    white-space: normal;
    overflow-wrap: anywhere;
  }

  &.multiselect--disabled {
    background-color: var(--typeahead-disabled);
    opacity: 1;
  }

  &.is-invalid .multiselect__tags {
    border-color: var(--form-input-error-color);
  }
}

.omegaup-typeahead-label {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.omegaup-typeahead-remove {
  position: relative;
  flex: 0 0 auto;
  width: 0.8rem;
  height: 0.8rem;
  margin-left: 0.35rem;
  padding: 0;
  border: 0;
  background: transparent;
}

.omegaup-typeahead-remove::before,
.omegaup-typeahead-remove::after {
  content: '';
  position: absolute;
  left: 50%;
  top: 0;
  width: 1px;
  height: 100%;
  background: $omegaup-primary--darker;
}

.omegaup-typeahead-remove::before {
  transform: rotate(45deg);
}

.omegaup-typeahead-remove::after {
  transform: rotate(-45deg);
}
</style>
