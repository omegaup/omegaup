<template>
  <tags-input
    v-model="selectedOptions"
    :existing-tags="existingOptions"
    :typeahead="true"
    :typeahead-style="'dropdown'"
    :typeahead-max-results="maxResults"
    :typeahead-activation-threshold="activationThreshold"
    :placeholder="T.typeaheadSearchPlaceholder"
    :limit="0"
    :hide-input-on-limit="true"
    :only-existing-tags="true"
    :typeahead-hide-discard="true"
    @change="updateExistingOptions"
    @tag-added="$emit('update:value', selectedOptions)"
    @tag-removed="$emit('update:value', selectedOptions)"
  >
  </tags-input>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import VoerroTagsInput from '@voerro/vue-tagsinput';
import '@voerro/vue-tagsinput/dist/style.css';
import T from '../../lang';
import { types } from '../../api_types';

@Component({
  components: {
    'tags-input': VoerroTagsInput,
  },
})
export default class MultiTypeahead extends Vue {
  @Prop() existingOptions!: types.ListItem[];
  @Prop({ default: 1 }) activationThreshold!: number;
  @Prop({ default: 10 }) maxResults!: number;
  @Prop({ default: () => [] }) value!: types.ListItem[];

  T = T;
  selectedOptions: types.ListItem[] = [];

  updateExistingOptions(query: string): void {
    if (query.length < this.activationThreshold) return;
    this.$emit('update-existing-options', query);
  }

  @Watch('value')
  onValueChanged(newValue: types.ListItem[]): void {
    this.selectedOptions = this.existingOptions.filter((option) =>
      newValue?.some((opt) => option.key === opt['key']),
    );
  }
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';

.tags-input-remove::before,
.tags-input-remove::after,
.tags-input-typeahead-item-highlighted-default {
  background-color: $omegaup-primary--darker;
}
</style>
