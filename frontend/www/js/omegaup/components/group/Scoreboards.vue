<template>
  <div class="card">
    <div class="card-body">
      <form class="form" @submit.prevent="onSubmit">
        <div class="form-group">
          <div class="row">
            <div class="form-group col-md-6">
              <label class="d-block">
                {{ T.wordsName }}
                <input
                  v-model="title"
                  class="form-control"
                  autocomplete="off"
                />
              </label>
            </div>

            <div class="form-group col-md-6">
              <label class="d-block">
                {{ T.contestNewFormShortTitleAlias }}
                <input :value="alias" class="form-control" disabled="true" />
              </label>
              <p class="help-block">
                {{ T.contestNewFormShortTitleAliasDesc }}
              </p>
            </div>
          </div>
          <div class="row">
            <div class="form-group col-12">
              <label class="d-block">
                {{ T.groupNewFormDescription }}
                <textarea
                  v-model="description"
                  rows="5"
                  class="form-control"
                ></textarea>
              </label>
            </div>
          </div>

          <button class="btn btn-primary" type="submit">
            {{ T.groupEditScoreboardsAdd }}
          </button>
        </div>
      </form>
    </div>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>{{ T.groupEditScoreboards }}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="scoreboard in scoreboards" :key="scoreboard.alias">
          <td>
            <a :href="`/group/${groupAlias}/scoreboard/${scoreboard.alias}/`">{{
              scoreboard.name
            }}</a>
          </td>
          <td>
            <a
              :href="`/group/${groupAlias}/scoreboard/${scoreboard.alias}/edit/`"
              :title="T.wordsEdit"
            >
              <font-awesome-icon :icon="['fas', 'edit']" />
            </a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import T from '../../lang';
import latinize from 'latinize';
import { types } from '../../api_types';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEdit } from '@fortawesome/free-solid-svg-icons';
library.add(faEdit);

export default defineComponent({
  name: 'GroupScoreboards',
  components: {
    FontAwesomeIcon,
  },
  props: {
    scoreboards: {
      type: Array as PropType<types.GroupScoreboard[]>,
      required: true,
    },
    groupAlias: {
      type: String,
      required: true,
    },
  },
  emits: ['create-scoreboard'],
  data() {
    return {
      T,
      title: null as null | string,
      alias: null as null | string,
      description: null as null | string,
    };
  },
  watch: {
    title(newValue: string, oldValue: string): void {
      if (newValue === null || newValue === oldValue) {
        return;
      }

      this.alias = latinize(newValue) // Remove accents
        .replace(/\s+/g, '-') // Replace whitespace
        .replace(/[^a-zA-Z0-9_-]/g, '') // Remove invalid characters
        .substring(0, 32);
    },
  },
  methods: {
    onSubmit(): void {
      this.$emit(
        'create-scoreboard',
        this,
        this.title,
        this.alias,
        this.description,
      );
    },

    reset(): void {
      this.title = null;
      this.alias = null;
      this.description = null;
    },
  },
});
</script>
