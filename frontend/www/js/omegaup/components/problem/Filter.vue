<template>
  <div class="card mb-3 panel panel-primary">
    <table class="table table-striped">
      <tbody>
        <tr v-for="(element, index) in collection" :key="index">
          <td>
            <input v-model="element.checked" type="checkbox" />
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
        <tr v-for="(tag, index) in anotherTagsDisplayed" :key="index">
          <td>
            <input v-model="tag.checked" type="checkbox" />
          </td>
          <td>
            {{ T[tag.tagname] }}
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
      this.$emit('emit-add-tag', tag, false);
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
