<template>
  <div class="card">
    <div class="card-body">
      <h3>{{ T.problemEditAddTags }}</h3>
      <table class="table table-borderless marginFix">
        <tbody>
          <tr v-for="(tag, index) in collection" :key="index">
            <td>
              <input
                :id="tag.tagname"
                v-model="tag.checked"
                class="collection-tags"
                type="checkbox"
              />
            </td>
            <td>{{ T[tag.tagname] }}</td>
          </tr>
        </tbody>
      </table>
      <table class="table table-borderless">
        <tbody>
          <tr v-for="(tag, index) in anotherTagsDisplayed" :key="index">
            <td class="fix">
              <input
                :id="tag.tagname"
                v-model="tag.checked"
                class="another-tags"
                type="checkbox"
              />
            </td>
            <td>
              {{ T[tag.tagname] }}
            </td>
          </tr>
        </tbody>
      </table>
      <div class="form-group">
        <vue-typeahead-bootstrap
          :data="anotherTags"
          :serializer="publicTagsSerializer"
          :placeholder="T.collecionAnotherTags"
          @hit="addAnotherTag"
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
  @Prop() collection!: string[];
  @Prop() anotherTags!: string[];
  @Prop() anotherTagsDisplayed!: string[];

  T = T;

  addAnotherTag(tag: string): void {
    if (!this.anotherTagsDisplayed.includes(tag)) {
      this.$emit('emit-add-tag', tag, true);
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

<style>
.fix {
  width: 40px;
}

.marginFix {
  margin-bottom: 0px;
}
</style>
