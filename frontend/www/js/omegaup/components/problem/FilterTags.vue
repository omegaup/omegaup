<template>
  <div class="card">
    <div class="card-body">
      <h3>{{ T.problemEditAddTags }}</h3>
      <div v-for="(tag, index) in tags" :key="index" class="form-check">
        <input
          :id="tag.tagname"
          v-model="localValue"
          class="form-check-input"
          type="checkbox"
        />
        <label class="form-check-label" :for="tag">{{ T[tag] }}</label>
      </div>
      <div class="form-group">
        <vue-typeahead-bootstrap
          :data="publicTags"
          :serializer="publicTagsSerializer"
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
export default class CollectionFilterTags extends Vue {
  @Prop() tags!: string[];
  @Prop() publicTags!: string[];
  @Prop({ default: () => [] }) selectedTags!: string[];

  T = T;
  localValue = this.selectedTags;

  addOtherTag(tag: string): void {
    if (!this.tags.includes(tag)) {
      this.$emit('emit-add-tag', tag);
    }
  }

  @Watch('localValue')
  onChecked(newValue: string): void {
    this.$emit('emit-check', newValue);
  }

  publicTagsSerializer(tagname: string): string {
    if (Object.prototype.hasOwnProperty.call(T, tagname)) {
      return T[tagname];
    }
    return tagname;
  }
}
</script>
