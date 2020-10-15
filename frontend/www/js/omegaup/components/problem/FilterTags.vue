<template>
  <div class="card">
    <div class="card-body">
      <h3>{{ T.problemEditAddTags }}</h3>
      <div v-for="(tag, index) in tags" :key="index" class="form-check">
        <label class="form-check-label">
          <input
            v-model="selectedTags"
            :value="tag"
            class="form-check-input"
            type="checkbox"
          />{{ T[tag] }}
        </label>
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
import { Vue, Component, Prop } from 'vue-property-decorator';
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

  T = T;
  selectedTags: string[] = [];

  addOtherTag(tag: string): void {
    if (!this.tags.includes(tag)) {
      this.selectedTags.push(tag);
      this.tags.push(tag);
    }
  }

  publicTagsSerializer(tagname: string): string {
    if (Object.prototype.hasOwnProperty.call(T, tagname)) {
      return T[tagname];
    }
    return tagname;
  }
}
</script>
