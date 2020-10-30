<template>
  <div class="card">
    <div class="card-body">
      <div class="form-group">
        <label class="font-weight-bold">{{ T.wordsPublicTags }}</label>
        <vue-typeahead-bootstrap
          v-if="canAddNewTags"
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
            <th class="text-center" scope="col">
              {{ T.contestEditTagName }}
            </th>
            <th class="text-center" scope="col">
              {{ T.contestEditTagDelete }}
              <a
                data-toggle="tooltip"
                rel="tooltip"
                :title="T.problemEditTagPublicRequired"
                ><img src="/media/question.png"
              /></a>
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
                :disabled="selectedPublicTags.length < 2"
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
            <th class="text-center" scope="col">
              {{ T.contestEditTagName }}
            </th>
            <th class="text-center" scope="col">
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
      <div class="row">
        <div class="form-group">
          <label class="font-weight-bold">{{ T.wordsLevel }}</label>
          <select
            v-model="problemLevelTag"
            required
            class="form-control"
            name="problem-level"
            @change="onSelectProblemLevel"
          >
            <option v-for="levelTag in levelTags" :value="levelTag">
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
        <label class="switch-container font-weight-bold">
          <div class="switch">
            <input v-model="allowTags" type="checkbox" />
            <span class="slider round"></span>
          </div>
          <span class="switch-text">
            {{ T.problemEditFormAllowUserAddTags }}
          </span>
        </label>
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

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faTrash } from '@fortawesome/free-solid-svg-icons';
library.add(faTrash);

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
    if (Object.prototype.hasOwnProperty.call(T, tagname)) {
      return `${T[tagname]} (${this.removeSpecialCharacters(T[tagname])})`;
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
