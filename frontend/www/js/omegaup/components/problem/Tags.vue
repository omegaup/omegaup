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
            <button
              type="button"
              class="btn btn-outline-primary m-1"
              v-bind:data-key="tag.name"
              v-for="tag in tags"
              v-on:click="onAddTag(tag.name, public)"
            >
              {{ T.hasOwnProperty(tag.name) ? T[tag.name] : tag.name }}
            </button>
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
        <div class="form-group">
          <button class="btn btn-primary" v-if="canAddNewTags" type="submit">
            {{ T.wordsAddTag }}
          </button>
        </div>
        <div class="form-group">
          <label>{{ T.wordsLevel }}</label>
          <select class="form-control" v-model="problemLevelTag">
            <option v-for="levelTag in levelTags" v-bind:value="levelTag">
              {{ T[levelTag] }}
            </option>
          </select>
          <span class="help-block">{{ T.levelTagHelp }}</span>
          <button
            type="button"
            class="btn btn-primary m-1"
            v-bind:disabled="
              !problemLevelTag || problemLevel === problemLevelTag
            "
            v-on:click.prevent="onUpdateProblemLevel"
          >
            {{ T.updateProblemLevel }}
          </button>
          <button
            type="button"
            class="btn btn-danger m-1"
            v-bind:disabled="!problemLevel"
            v-on:click.prevent="onDeleteProblemLevel"
          >
            {{ T.deleteProblemLevel }}
          </button>
        </div>
        <div class="form-group">
          <label class="switch-container">
            <div class="switch">
              <input type="checkbox" v-model="allowTags" />
              <span class="slider round"></span>
            </div>
            <span class="switch-text">
              {{ T.problemEditFormAllowUserAddTags }}
            </span>
          </label>
        </div>
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
    <input
      type="hidden"
      name="allow_user_add_tags"
      v-bind:value="allowTags"
      v-if="!canAddNewTags"
    />
  </div>
</template>

<style>
/* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

/* Hide default HTML checkbox */
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

.slider:before {
  position: absolute;
  content: '';
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

input:checked + .slider {
  background-color: #2196f3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196f3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

.switch-container {
  width: 100%;
  position: relative;
}

.switch-container span.switch-text {
  margin: 0;
  position: absolute;
  top: 50%;
  margin-left: 5px;
  -ms-transform: translateY(-50%);
  transform: translateY(-50%);
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import { types } from '../../api_types';

@Component
export default class ProblemTags extends Vue {
  @Prop() initialTags!: omegaup.Tag[];
  @Prop({ default: null }) problemLevel!: string | null;
  @Prop() publicTags!: string[];
  @Prop() levelTags!: string[];
  @Prop({ default: [] }) initialSelectedTags!: types.SelectedTag[];
  @Prop() alias!: string;
  @Prop({ default: '' }) title!: string;
  @Prop({ default: true }) initialAllowTags!: boolean;
  @Prop({ default: false }) canAddNewTags!: boolean;

  T = T;
  tags = this.initialTags;
  selectedTags = this.initialSelectedTags;
  allowTags = this.initialAllowTags;
  public = false;
  tagname = '';
  problemLevelTag: string | null = this.problemLevel;

  get selectedTagsList(): string {
    return JSON.stringify(this.selectedTags);
  }

  onAddTag(tagname: string, isPublic: boolean): void {
    this.selectedTags.push({ tagname: tagname, public: isPublic });
    this.tags = this.tags.filter(val => val.name !== tagname);
    if (this.canAddNewTags) {
      this.$emit('add-tag', this.alias, tagname, isPublic);
    }
  }

  onUpdateProblemLevel(): void {
    if (this.problemLevelTag) {
      this.$emit('update-problem-level', this.problemLevelTag);
    }
  }

  onDeleteProblemLevel(): void {
    this.$emit('update-problem-level');
    this.problemLevelTag = null;
  }

  onRemoveTag(tagname: string): void {
    this.tags.push({ name: tagname });
    this.selectedTags = this.selectedTags.filter(
      val => val.tagname !== tagname,
    );
    if (this.canAddNewTags) {
      this.$emit('remove-tag', this.alias, tagname);
    }
  }

  @Watch('allowTags')
  onPropertyChanged(newValue: boolean): void {
    if (!this.canAddNewTags) {
      return;
    }
    this.$emit('change-allow-user-add-tag', this.alias, this.title, newValue);
  }
}
</script>
