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
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import latinize from 'latinize';
import { types } from '../../api_types';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEdit } from '@fortawesome/free-solid-svg-icons';
library.add(faEdit);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class GroupScoreboards extends Vue {
  @Prop() scoreboards!: types.GroupScoreboard[];
  @Prop() groupAlias!: string;

  T = T;
  title: null | string = null;
  alias: null | string = null;
  description: null | string = null;

  @Watch('title')
  onTitleChanged(newValue: string, oldValue: string): void {
    if (newValue === null || newValue === oldValue) {
      return;
    }

    this.alias = latinize(newValue) // Remove accents
      .replace(/\s+/g, '-') // Replace whitespace
      .replace(/[^a-zA-Z0-9_-]/g, '') // Remove invalid characters
      .substring(0, 32);
  }

  onSubmit(): void {
    this.$emit(
      'create-scoreboard',
      this,
      this.title,
      this.alias,
      this.description,
    );
  }

  reset(): void {
    this.title = null;
    this.alias = null;
    this.description = null;
  }
}
</script>
