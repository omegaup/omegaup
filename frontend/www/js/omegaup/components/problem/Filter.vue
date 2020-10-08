<template>
  <div class="card mb-3 panel panel-primary">
    <table class="table table-striped">
      <tbody>
        <tr v-for="(element, index) in collection" :key="index">
          <td>
            <input type="checkbox" v-model="element.checked">
          </td>
          <td>{{ T[element.tagname] }}</td>
        </tr>
      </tbody>
    </table>
    <vue-typeahead-bootstrap
      :data="anotherTags"
      :serializer="publicTagsSerializer"
      @hit="addAnotherTag"
    >
    </vue-typeahead-bootstrap>
    <table class="table table-striped">
      <tbody>
        <tr v-for="tag in anotherTagsDisplayed" :key="tag">
          <td>
            <input type="checkbox" />
          </td>
          <td>
            {{ Object.prototype.hasOwnProperty.call(T, tag) ? T[tag] : tag }}
          </td>
        </tr>
      </tbody>
    </table>
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
export default class CollectionFilter extends Vue {
  @Prop() collection!: string[];
  @Prop() anotherTags!: string[];
  @Prop() anotherTagsDisplayed!: string[];
  @Prop() alias!: string;
  T = T;

  addAnotherTag(tag: string): void {
    if (!this.anotherTagsDisplayed.includes(tag)) {
      this.$emit('emit-add-tag', this.alias, tag, true);
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
