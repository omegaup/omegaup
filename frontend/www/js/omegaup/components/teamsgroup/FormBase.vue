<template>
  <div class="card">
    <slot name="teams-group-title"></slot>
    <div class="card-body">
      <form
        class="needs-validation"
        data-teams-group
        @submit.prevent="
          $emit('submit', {
            name: currentName,
            description: currentDescription,
            numberOfContestants: currentNumberOfContestants,
          })
        "
      >
        <div class="row">
          <div class="form-group col-md-6">
            <label class="control-label w-100">
              {{ T.wordsName }}
              <input
                v-model="currentName"
                name="title"
                required
                type="text"
                class="form-control"
              />
            </label>
          </div>

          <div class="form-group col-md-6">
            <label class="control-label w-100">
              {{ T.contestNewFormShortTitleAlias }}
              <input
                v-model="alias"
                name="alias"
                required
                type="text"
                class="form-control"
                disabled="true"
              />
            </label>
            <p class="help-block">{{ T.contestNewFormShortTitleAliasDesc }}</p>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label class="control-label w-100">
              {{ T.teamsGroupNewFormDescription }}
              <textarea
                v-model="currentDescription"
                required
                name="description"
                cols="30"
                rows="5"
                class="form-control"
              ></textarea>
            </label>
          </div>

          <div class="form-group col-md-6">
            <label class="control-label w-100">
              {{ T.contestNewFormNumberOfContestants }}
              <input
                v-model="currentNumberOfContestants"
                name="number-of-contestants"
                required
                :max="maxNumberOfContestants"
                :min="minNumberOfContestants"
                type="number"
                class="form-control"
              />
            </label>
            <p class="help-block">
              {{ T.contestNewFormNumberOfContestantsDesc }}
            </p>
          </div>
        </div>

        <div class="form-group">
          <button type="submit" class="btn btn-primary" data-create-teams-group>
            <slot name="teams-group-submit-button">
              {{ T.teamsGroupFormCreate }}
            </slot>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import T from '../../lang';

export default defineComponent({
  name: 'TeamsGroupFormBase',
  props: {
    alias: {
      type: String as PropType<null | string>,
      default: null,
    },
    description: {
      type: String as PropType<null | string>,
      default: null,
    },
    name: {
      type: String as PropType<null | string>,
      default: null,
    },
    numberOfContestants: {
      type: Number,
      default: 3,
    },
    maxNumberOfContestants: {
      type: Number,
      default: 10,
    },
    minNumberOfContestants: {
      type: Number,
      default: 1,
    },
  },
  emits: [
    'submit',
    'update:alias',
    'update:description',
    'update:name',
    'update:numberOfContestants',
  ],
  data() {
    return {
      T,
      currentAlias: this.alias as null | string,
      currentDescription: this.description as null | string,
      currentName: this.name as null | string,
      currentNumberOfContestants: this.numberOfContestants,
    };
  },
  watch: {
    currentAlias(newValue: string): void {
      this.$emit('update:alias', newValue);
    },
    currentDescription(newValue: string): void {
      this.$emit('update:description', newValue);
    },
    currentName(newValue: string): void {
      this.$emit('update:name', newValue);
    },
    currentNumberOfContestants(newValue: number): void {
      this.$emit('update:numberOfContestants', newValue);
    },
  },
});
</script>
