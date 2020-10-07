<template>
  <div class="card mb-3 panel panel-primary">
    <table class="table">
      <tbody>
        <tr v-for="element in collection">
          <td>
            <input type="checkbox" />
          </td>
          <td>{{ T[element.alias] }}</td>
        </tr>
      </tbody>
    </table>
    <vue-typeahead-bootstrap
      :data="publicTags"
      :serializer="publicTagsSerializer"
      @hit="addPublicTag"
    >
    </vue-typeahead-bootstrap>
    <table class="table table-striped">
      <tbody>
        <tr v-for="tag in selectedPublicTags" :key="tag">
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
  @Prop() publicTags!: string[];
  @Prop() selectedPublicTags!: string[];
  @Prop() alias!: string;

  T = T;

  addPublicTag(tag: string): void {
    if (!this.selectedPublicTags.includes(tag)) {
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
