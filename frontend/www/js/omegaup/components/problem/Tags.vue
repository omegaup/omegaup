<template>
  <div class="card">
    <div class="card-body">
      <div class="form-group">
        <label class="font-weight-bold">{{ T.wordsPublicTags }}</label>
        <vue-typeahead-bootstrap
          v-if="canAddNewTags"
          v-model="newPublicTag"
          data-tags-input
          :data="publicTags"
          :serializer="publicTagsSerializer"
          :auto-close="true"
          :placeholder="T.publicTagsPlaceholder"
          :required="true"
          :input-class="errors.includes('public_tags') ? 'is-invalid' : ''"
          @hit="addPublicTag"
        >
        </vue-typeahead-bootstrap>
      </div>
      <table class="table table-striped">
        <thead>
          <tr>
            <th class="text-center w-50" scope="col">
              {{ T.contestEditTagName }}
            </th>
            <th class="pl-5" scope="col">
              {{ T.contestEditTagDelete }}
              <a
                v-if="!isLecture"
                data-toggle="tooltip"
                rel="tooltip"
                :title="T.problemEditTagPublicRequired"
              >
                <span class="question"></span>
              </a>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="tag in selectedPublicTags" :key="tag">
            <td class="align-middle">
              <a :href="`/problem/?tag[]=${tag}`">
                {{
                  Object.prototype.hasOwnProperty.call(T, tag) ? T[tag] : tag
                }}
              </a>
            </td>
            <td class="text-center">
              <button
                type="button"
                class="btn btn-danger"
                :disabled="selectedPublicTags.length < 2 && !isLecture"
                @click="removeTag(tag, /*public=*/ true)"
              >
                <font-awesome-icon :icon="['fas', 'trash']" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="form-group">
        <label class="font-weight-bold">{{ T.wordsPrivateTags }}</label>
        <div class="input-group">
          <input
            v-model="newPrivateTag"
            type="text"
            class="form-control"
            :placeholder="T.privateTagsPlaceholder"
          />
          <div class="input-group-append">
            <button
              class="btn btn-outline-primary"
              type="button"
              :disabled="newPrivateTag === ''"
              @click.prevent="addPrivateTag"
            >
              {{ T.wordsAddTag }}
            </button>
          </div>
        </div>
      </div>
      <table class="table table-striped">
        <thead>
          <tr>
            <th class="text-center w-50" scope="col">
              {{ T.contestEditTagName }}
            </th>
            <th class="pl-5" scope="col">
              {{ T.contestEditTagDelete }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="tag in selectedPrivateTags" :key="tag">
            <td class="align-middle">
              <a :href="`/problem/?tag[]=${tag}`">
                {{ tag }}
              </a>
            </td>
            <td class="text-center">
              <button
                type="button"
                class="btn btn-danger"
                @click="removeTag(tag, false /* public */)"
              >
                <font-awesome-icon :icon="['fas', 'trash']" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="row mx-1">
        <div class="form-group w-100">
          <label class="font-weight-bold">{{ T.wordsLevel }}</label>
          <select
            v-model="problemLevelTag"
            required
            class="form-control"
            name="problem-level"
            @change="onSelectProblemLevel"
          >
            <option
              v-for="levelTag in levelTags"
              :key="levelTag"
              :value="levelTag"
            >
              {{ T[levelTag] }}
            </option>
          </select>
          <small class="form-text text-muted mb-2">{{ T.levelTagHelp }}</small>
          <template v-if="!isCreate">
            <button
              type="button"
              class="btn btn-primary"
              :disabled="!problemLevelTag || problemLevel === problemLevelTag"
              @click.prevent="onUpdateProblemLevel"
            >
              {{ T.updateProblemLevel }}
            </button>
            <button
              type="button"
              class="btn btn-danger ml-1"
              :disabled="!problemLevel"
              @click.prevent="onDeleteProblemLevel"
            >
              {{ T.deleteProblemLevel }}
            </button>
          </template>
        </div>
      </div>
      <div class="form-group">
        <omegaup-toggle-switch
          :value.sync="allowTags"
          :checked-value="allowTags"
          :text-description="T.problemEditFormAllowUserAddTags"
        ></omegaup-toggle-switch>
      </div>
    </div>
    <input
      v-if="!canAddNewTags"
      type="hidden"
      name="allow_user_add_tags"
      :value="allowTags"
    />
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap';
import omegaup_ToggleSwitch from '../ToggleSwitch.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faTrash } from '@fortawesome/free-solid-svg-icons';
library.add(faTrash);

@Component({
  components: {
    FontAwesomeIcon,
    VueTypeaheadBootstrap,
    'omegaup-toggle-switch': omegaup_ToggleSwitch,
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
  @Prop() isLecture!: boolean;

  T = T;
  allowTags = this.initialAllowTags;
  problemLevelTag: string | null = this.problemLevel;
  newPrivateTag = '';
  newPublicTag = '';

  addPublicTag(tag: string): void {
    if (this.canAddNewTags && !this.selectedPublicTags.includes(tag)) {
      this.$emit('emit-add-tag', this.alias, tag, true);
    }
    this.newPublicTag = '';
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
    if (Object.prototype.hasOwnProperty.call(T, tagname)) {
      let complete = `${T[tagname]} ( ${this.removeSpecialCharacters(
        T[tagname],
      )})`;
      return `${
        this.removeSpecialCharacters(T[tagname]).includes(T[tagname])
          ? T[tagname]
          : complete
      }`;
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

  removeSpecialCharacters(cadena: string): string {
    let de = 'ÁÃÀÄÂÉËÈÊÍÏÌÎÓÖÒÔÚÜÙÛÑÇáãàäâéëèêíïìîóöòôúüùûñç',
      a = 'AAAAAEEEEIIIIOOOOUUUUNCaaaaaeeeeiiiioooouuuunc',
      re = new RegExp('[' + de + ']', 'ug');

    cadena = cadena.replace(re, (match) => a.charAt(de.indexOf(match)));

    return cadena;
  }
}
</script>

<style>
.question {
  width: 20px;
  height: 20px;
  background: url('/media/question.png');
  display: inline-block;
  vertical-align: middle;
}

.question:hover {
  width: 25px;
  height: 25px;
  background: url('/media/question.png');
  background-size: 25px 25px;
  display: inline-block;
  vertical-align: middle;
}
</style>
