<template>
  <div class="card">
    <div class="card-body">
      <h3 class="section-font-size text-center">{{ T.problemEditAddTags }}</h3>
      <div v-for="(tag, index) in tags" :key="index" class="form-check">
        <label class="form-check-label">
          <input
            v-model="currentSelectedTags"
            :value="tag.name"
            class="form-check-input"
            type="checkbox"
          />{{ `${T[tag.name]}  (${tag.problemCount})` }}
        </label>
      </div>
      <div class="mb-3 mt-2">
        <omegaup-common-typeahead
          :existing-options="publicQualityTagOptions"
          :value.sync="selectedOtherTag"
          :placeholder="T.collecionOtherTags"
          :activation-threshold="0"
          :max-results="publicQualityTagNames.length || 10"
        ></omegaup-common-typeahead>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import common_Typeahead from '../common/Typeahead.vue';
@Component({
  components: {
    'omegaup-common-typeahead': common_Typeahead,
  },
})
export default class FilterTags extends Vue {
  @Prop() publicQualityTags!: types.TagWithProblemCount[];
  @Prop({ default: () => [] }) tags!: types.TagWithProblemCount[];
  @Prop({ default: () => [] }) selectedTags!: string[];

  T = T;
  currentSelectedTags = this.selectedTags;
  selectedOtherTag: types.ListItem | null = null;

  get publicQualityTagNames(): string[] {
    return this.publicQualityTags.map((x) => x.name);
  }

  get publicQualityTagOptions(): types.ListItem[] {
    return this.publicQualityTags.map((tag) => ({
      key: tag.name,
      value: this.publicQualityTagsSerializer(tag.name),
    }));
  }

  addOtherTag(tag: string): void {
    if (!this.currentSelectedTags.includes(tag)) {
      this.currentSelectedTags.push(tag);
    }
  }

  @Watch('selectedOtherTag')
  onSelectedOtherTagChanged(tag: types.ListItem | null): void {
    if (!tag) {
      return;
    }
    this.addOtherTag(tag.key);
    this.selectedOtherTag = null;
  }

  publicQualityTagsSerializer(name: string): string {
    if (Object.prototype.hasOwnProperty.call(T, name)) {
      return T[name];
    }
    return name;
  }

  @Watch('currentSelectedTags')
  onNewTagSelected(): void {
    this.$emit('new-selected-tag', this.currentSelectedTags);
  }
}
</script>

<style scoped>
.section-font-size {
  font-size: 1.44rem;
}
</style>
