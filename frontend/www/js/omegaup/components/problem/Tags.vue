<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="form" v-on:submit.prevent="onAddTag(tagname, public)">
        <div class="form-group">
          <label>{{ T.wordsTags }}</label>
          <input
            name="tag_name"
            v-model="tagname"
            v-if="canAddNewTags"
            type="text"
            size="20"
            class="form-control"
            autocomplete="off"
          />
        </div>
        <div class="form-group">
          <div class="tag-list pull-left">
            <a
              class="tag pull-left"
              href="#tags"
              v-bind:data-key="tag.name"
              v-for="tag in tags"
              v-on:click="onAddTag(tag.name, public)"
            >
              {{ T.hasOwnProperty(tag.name) ? T[tag.name] : tag.name }}
            </a>
          </div>
        </div>
        <div class="form-group">
          <label>{{ T.problemEditTagPublic }}</label>
          <select class="form-control" v-model="public">
            <option v-bind:value="false" selected="selected">
              {{ T.wordsNo }}
            </option>
            <option v-bind:value="true">{{ T.wordsYes }}</option>
          </select>
        </div>
        <button class="btn btn-primary" v-if="canAddNewTags" type="submit">
          {{ T.wordsAddTag }}
        </button>
      </form>
    </div>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>{{ T.contestEditTagName }}</th>
          <th>{{ T.contestEditTagPublic }}</th>
          <th>{{ T.contestEditTagDelete }}</th>
        </tr>
      </thead>
      <tbody class="problem-tags">
        <tr v-for="selectedTag in selectedTags">
          <td class="tag-name">
            <a
              v-bind:data-key="selectedTag.tagname"
              v-bind:href="`/problem/?tag[]=${selectedTag.tagname}`"
            >
              {{
                T.hasOwnProperty(selectedTag.tagname)
                  ? T[selectedTag.tagname]
                  : selectedTag.tagname
              }}
            </a>
          </td>
          <td class="is_public">
            {{ selectedTag.public ? T.wordsYes : T.wordsNo }}
          </td>
          <td>
            <button
              type="button"
              class="close"
              v-on:click="onRemoveTag(selectedTag.tagname)"
            >
              &times;
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    <input
      type="hidden"
      name="selected_tags"
      v-bind:value="selectedTagsList"
      v-if="!canAddNewTags"
    />
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import { types } from '../../api_types';
import * as ui from '../../ui';

@Component
export default class ProblemTags extends Vue {
  @Prop() initialTags!: omegaup.Tag[];
  @Prop() initialSelectedTags!: types.SelectedTag[];
  @Prop() alias!: string;
  @Prop({ default: false }) canAddNewTags!: boolean;

  T = T;
  tags = this.initialTags;
  selectedTags = this.initialSelectedTags || [];
  public = false;
  tagname = '';

  get selectedTagsList(): string {
    return JSON.stringify(this.selectedTags);
  }

  onAddTag(tagname: string, isPublic: boolean): void {
    this.selectedTags.push({ tagname: tagname, public: isPublic });
    this.tags = this.tags.filter((val, index, arr) => val.name !== tagname);
    if (this.canAddNewTags) {
      this.$emit('add-tag', this.alias, tagname, isPublic);
    }
  }

  onRemoveTag(tagname: string): void {
    this.tags.push({ name: tagname });
    this.selectedTags = this.selectedTags.filter(
      (val, index, arr) => val.tagname !== tagname,
    );
    if (this.canAddNewTags) {
      this.$emit('remove-tag', this.alias, tagname);
    }
  }
}
</script>
