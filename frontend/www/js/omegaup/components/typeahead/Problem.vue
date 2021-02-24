<template>
  <tags-input
    v-model="selectedProblems"
    :existing-tags="existingProblems"
    :typeahead="true"
    :typeahead-style="'dropdown'"
    :typeahead-max-results="maxResults"
    :typeahead-activation-threshold="activationThreshold"
    :placeholder="T.searchProblemTypeahead"
    :limit="1"
    :hide-input-on-limit="true"
    :only-existing-tags="true"
    :typeahead-hide-discard="true"
    @change="updateExistingProblems"
    @tag-added="onTagAdded"
    @tag-removed="onTagRemoved"
    @keydown="onKeyDown"
  >
  </tags-input>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import VoerroTagsInput from '@voerro/vue-tagsinput';
import '@voerro/vue-tagsinput/dist/style.css';
import T from '../../lang';

@Component({
  components: {
    'tags-input': VoerroTagsInput,
  },
})
export default class ProblemTypeahead extends Vue {
  @Prop() existingProblems!: { key: string; value: string }[];
  @Prop({ default: 3 }) activationThreshold!: number;
  @Prop({ default: 5 }) maxResults!: number;

  T = T;
  selectedProblems: { key: string; value: string }[] = [];

  updateExistingProblems(query: string): void {
    if (query.length < this.activationThreshold) return;

    this.$emit('update-existing-problems', query);
  }

  onTagAdded(): void {
    if (this.selectedProblems.length < 1) return;
    this.$emit('update-selected-problem', this.selectedProblems[0].key);
  }

  onTagRemoved(): void {
    this.$emit('update-selected-problem', '');
  }
}
</script>

<style lang="scss">
.tags-input-remove:before,
.tags-input-remove:after,
.tags-input-typeahead-item-highlighted-default {
  background-color: #678dd7;
}
</style>
