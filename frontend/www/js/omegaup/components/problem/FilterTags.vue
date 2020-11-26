<template>
  <div class="card">
    <div class="card-body">
      <h3>{{ T.problemEditAddTags }}</h3>
      <div v-for="(tag, index) in tags" :key="index" class="form-check">
        <label class="form-check-label">
          <input
            v-model="currentSelectedTags"
            :value="tag.alias"
            class="form-check-input"
            type="checkbox"
          />{{ T[tag.alias] }} {{ tag.total }}
        </label>
      </div>
      <div class="form-group">
        <vue-typeahead-bootstrap
          :data="publicQualityTags"
          :serializer="publicQualityTagsSerializer"
          :placeholder="T.collecionOtherTags"
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
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap';
@Component({
  components: {
    'vue-typeahead-bootstrap': VueTypeaheadBootstrap,
  },
})
export default class FilterTags extends Vue {
  @Prop() publicQualityTags!: string[];
  @Prop({ default: () => [] }) tags!: string[];
  @Prop({ default: () => [] }) selectedTags!: string[];

  T = T;
  currentSelectedTags = this.selectedTags;

  addOtherTag(tag: string): void {
    if (!this.currentSelectedTags.includes(tag)) {
      this.currentSelectedTags.push(tag);
    }
  }

  publicQualityTagsSerializer(alias: string, total: number): string {
    if (Object.prototype.hasOwnProperty.call(T, alias)) {
      return T[alias].concat(' (', total.toString(), ')');
    }
    return alias.concat(' (', total.toString(), ')');
  }

  @Watch('currentSelectedTags')
  onNewTagSelected(): void {
    this.$emit('new-selected-tag', this.currentSelectedTags);
  }
}
</script>
