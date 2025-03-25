<template>
  <div class="card">
    <h3 class="card-header">
      {{
        ui.formatString(T.groupEditScoreboardsEdit, { scoreboard: scoreboard })
      }}
    </h3>
    <div class="card-body">
      <form class="form" @submit.prevent="onAddContest">
        <div class="row">
          <div class="form-group col-md-6">
            <label class="w-100"
              >{{ T.wordsContests }}
              <select v-model="selectedContest" class="form-control" required>
                <option
                  v-for="contest in availableContests"
                  :key="contest.alias"
                  :value="contest.alias"
                >
                  {{ contest.title }}
                </option>
              </select>
            </label>
          </div>

          <div class="form-group col-md-3">
            <label class="w-100"
              >{{ T.groupNewFormOnlyAC }}
              <omegaup-radio-switch
                :value.sync="onlyAc"
                :selected-value="onlyAc"
              ></omegaup-radio-switch>
            </label>
          </div>

          <div class="form-group col-md-3">
            <label class="w-100"
              >{{ T.groupNewFormWeight }}
              <input
                v-model="weight"
                name="weight"
                type="number"
                size="4"
                class="form-control"
                required
              />
            </label>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <button class="btn btn-primary" type="submit">
              {{ T.groupEditScoreboardsAddContest }}
            </button>
          </div>
        </div>
      </form>
    </div>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>{{ T.wordsContests }}</th>
          <th>{{ T.groupNewFormOnlyAC }}</th>
          <th>{{ T.groupNewFormWeight }}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="contest in contests" :key="contest.alias">
          <td data-contest-alias>
            <a :href="ui.contestURL(contest)">{{ ui.contestTitle(contest) }}</a>
          </td>
          <td data-contest-only-ac>
            {{ contest.only_ac ? T.wordsYes : T.wordsNo }}
          </td>
          <td data-contest-weight>{{ contest.weight }}</td>
          <td data-contest-actions>
            <button
              class="btn btn-link"
              @click="$emit('remove-contest', contest.alias)"
            >
              <font-awesome-icon
                :icon="['fas', 'trash']"
                :title="T.wordsDelete"
              />
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import { types } from '../../api_types';
import omegaup_RadioSwitch from '../RadioSwitch.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faTrash } from '@fortawesome/free-solid-svg-icons';
library.add(faTrash);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-radio-switch': omegaup_RadioSwitch,
  },
})
export default class GroupScoreboardContests extends Vue {
  @Prop() scoreboard!: string;
  @Prop() availableContests!: types.ContestListItem[];
  @Prop() contests!: types.ScoreboardContest[];

  T = T;
  ui = ui;
  selectedContest: null | string = null;
  onlyAc = false;
  weight = 1.0;

  onAddContest(): void {
    this.$emit(
      'add-contest',
      this,
      this.selectedContest,
      this.onlyAc,
      this.weight,
    );
  }

  reset(): void {
    this.selectedContest = null;
    this.onlyAc = false;
    this.weight = 1.0;
  }
}
</script>
