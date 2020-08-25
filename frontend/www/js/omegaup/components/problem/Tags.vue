<template>
  <div class="card">
    <div class="card-body">
      <div class="form-group">
        <label class="font-weight-bold">{{ T.wordsPublicTags }}</label>
        <vue-typeahead-bootstrap
          v-if="canAddNewTags"
          v-bind:data="publicTags"
          v-bind:serializer="publicTagsSerializer"
          v-on:hit="addPublicTag"
          v-bind:auto-close="true"
          v-bind:placeholder="T.publicTagsPlaceholder"
          v-bind:required="true"
          v-bind:input-class="
            errors.includes('public_tags') ? 'is-invalid' : ''
          "
        >
        </vue-typeahead-bootstrap>
      </div>
      <table class="table table-striped">
        <thead>
          <tr>
            <th class="text-center" scope="col">
              {{ T.contestEditTagName }}
            </th>
            <th class="text-center" scope="col">
              {{ T.contestEditTagDelete }}
              <a
                data-toggle="tooltip"
                rel="tooltip"
                v-bind:title="T.problemEditTagPublicRequired"
                ><img src="/media/question.png"
              /></a>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="tag in selectedPublicTags" v-bind:key="tag">
            <td class="align-middle">
              <a v-bind:href="`/problem/?tag[]=${tag}`">
                {{ T.hasOwnProperty(tag) ? T[tag] : tag }}
              </a>
            </td>
            <td class="text-center">
              <button
                type="button"
                class="btn btn-danger"
                v-on:click="removeTag(tag, /*public=*/ true)"
                v-bind:disabled="selectedPublicTags.length < 2"
              >
                <font-awesome-icon v-bind:icon="['fas', 'trash']" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="form-group">
        <label class="font-weight-bold">{{ T.wordsPrivateTags }}</label>
        <div class="input-group">
          <input
            type="text"
            class="form-control"
            v-model="newPrivateTag"
            v-bind:placeholder="T.privateTagsPlaceholder"
          />
          <div class="input-group-append">
            <button
              class="btn btn-outline-primary"
              type="button"
              v-bind:disabled="newPrivateTag === ''"
              v-on:click.prevent="addPrivateTag"
            >
              {{ T.wordsAddTag }}
            </button>
          </div>
        </div>
      </div>
      <table class="table table-striped">
        <thead>
          <tr>
            <th class="text-center" scope="col">
              {{ T.contestEditTagName }}
            </th>
            <th class="text-center" scope="col">
              {{ T.contestEditTagDelete }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="tag in selectedPrivateTags" v-bind:key="tag">
            <td class="align-middle">
              <a v-bind:href="`/problem/?tag[]=${tag}`">
                {{ tag }}
              </a>
            </td>
            <td class="text-center">
              <button
                type="button"
                class="btn btn-danger"
                v-on:click="removeTag(tag, false /* public */)"
              >
                <font-awesome-icon v-bind:icon="['fas', 'trash']" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="row">
        <div class="form-group">
          <label class="font-weight-bold">{{ T.wordsLevel }}</label>
          <select
            required
            class="form-control"
            name="problem-level"
            v-model="problemLevelTag"
            v-on:change="onSelectProblemLevel"
          >
            <option v-for="levelTag in levelTags" v-bind:value="levelTag">
              {{ T[levelTag] }}
            </option>
          </select>
          <small class="form-text text-muted mb-2">{{ T.levelTagHelp }}</small>
          <template v-if="!isCreate">
            <button
              type="button"
              class="btn btn-primary"
              v-bind:disabled="
                !problemLevelTag || problemLevel === problemLevelTag
              "
              v-on:click.prevent="onUpdateProblemLevel"
            >
              {{ T.updateProblemLevel }}
            </button>
            <button
              type="button"
              class="btn btn-danger ml-1"
              v-bind:disabled="!problemLevel"
              v-on:click.prevent="onDeleteProblemLevel"
            >
              {{ T.deleteProblemLevel }}
            </button>
          </template>
        </div>
      </div>
      <div class="form-group">
        <label class="switch-container font-weight-bold">
          <div class="switch">
            <input type="checkbox" v-model="allowTags" />
            <span class="slider round"></span>
          </div>
          <span class="switch-text">
            {{ T.problemEditFormAllowUserAddTags }}
          </span>
        </label>
      </div>
    </div>
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
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faTrash } from '@fortawesome/free-solid-svg-icons';
library.add(faTrash);

import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';

@Component({
  components: {
    FontAwesomeIcon,
    VueTypeaheadBootstrap,
  },
})
export default class ProblemTags extends Vue {
  @Prop({ default: null }) problemLevel!: string | null;
  @Prop() publicTags!: string[];
  @Prop() selectedPublicTags!: string[];
  @Prop() selectedPrivateTags!: string[];
  @Prop() levelTags!: string[];
  @Prop() alias!: string;
  @Prop({ default: '' }) title!: string;
  @Prop({ default: true }) initialAllowTags!: boolean;
  @Prop({ default: false }) canAddNewTags!: boolean;
  @Prop({ default: false }) isCreate!: boolean;
  @Prop({ default: () => [] }) errors!: string[];

  T = T;
  allowTags = this.initialAllowTags;
  problemLevelTag: string | null = this.problemLevel;
  newPrivateTag = '';

  addPublicTag(tag: string): void {
    if (this.canAddNewTags && !this.selectedPublicTags.includes(tag)) {
      this.$emit('emit-add-tag', this.alias, tag, true);
    }
  }

  addPrivateTag(): void {
    if (
      this.canAddNewTags &&
      this.newPrivateTag !== '' &&
      !this.selectedPrivateTags.includes(this.newPrivateTag)
    ) {
      this.$emit('emit-add-tag', this.alias, this.newPrivateTag, false);
      this.newPrivateTag = '';
    }
  }

  removeTag(tag: string, isPublic: boolean): void {
    if (this.canAddNewTags) {
      this.$emit('emit-remove-tag', this.alias, tag, isPublic);
    }
  }

  onSelectProblemLevel(): void {
    if (this.problemLevelTag) {
      this.$emit('select-problem-level', this.problemLevelTag);
    }
  }

  onUpdateProblemLevel(): void {
    if (this.problemLevelTag) {
      this.$emit('emit-update-problem-level', this.problemLevelTag);
    }
  }

  onDeleteProblemLevel(): void {
    this.$emit('emit-update-problem-level');
    this.problemLevelTag = null;
  }

  publicTagsSerializer(tagname: string): string {
    if (T.hasOwnProperty(tagname)) {
      return T[tagname];
    }
    return tagname;
  }

  @Watch('allowTags')
  onPropertyChanged(newValue: boolean): void {
    if (!this.canAddNewTags) {
      return;
    }
    this.$emit(
      'emit-change-allow-user-add-tag',
      this.alias,
      this.title,
      newValue,
    );
  }
}
</script>
