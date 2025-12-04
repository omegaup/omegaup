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
      <div class="form-group mt-2">
        <vue-typeahead-bootstrap
          :data="publicQualityTagNames"
          :serializer="publicQualityTagsSerializer"
          :placeholder="T.collectionOtherTags"
          @hit="addOtherTag"
        >
        </vue-typeahead-bootstrap>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap';
@Component({
  components: {
    'vue-typeahead-bootstrap': VueTypeaheadBootstrap,
  },
})
export default class FilterTags extends Vue {
  @Prop() publicQualityTags!: types.TagWithProblemCount[];
  @Prop({ default: () => [] }) tags!: types.TagWithProblemCount[];
  @Prop({ default: () => [] }) selectedTags!: string[];

  T = T;
  currentSelectedTags = this.selectedTags;

  get publicQualityTagNames(): string[] {
    return this.publicQualityTags.map((x) => x.name);
  }

  addOtherTag(tag: string): void {
    if (!this.currentSelectedTags.includes(tag)) {
      this.currentSelectedTags.push(tag);
    }
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
