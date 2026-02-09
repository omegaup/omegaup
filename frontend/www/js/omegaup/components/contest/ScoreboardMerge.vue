<template>
  <div class="card">
    <div class="card-header panel-heading">
      <h3 class="card-title mb-0">{{ T.omegaupTitleScoreboardmerge }}</h3>
    </div>
    <div class="card-body">
      <div class="row align-items-end">
        <div class="form-group col-md-8" data-merge-contest-name>
          <multiselect
            :value="selectedContests"
            :options="contestAliases"
            :multiple="true"
            :close-on-select="false"
            :allow-empty="true"
            :placeholder="T.contestScoreboardMergeChoseContests"
            @remove="onRemove"
            @select="onSelect"
          ></multiselect>
        </div>
        <div class="form-group col-md-4 text-right w-100">
          <button
            data-merge-contest-button
            class="btn btn-primary"
            type="button"
            :disabled="!selectedContests.length"
            @click.prevent="onDisplayTable"
          >
            {{ T.showTotalScoreboard }}
          </button>
        </div>
      </div>
      <div v-if="scoreboard.length" class="row post">
        <table class="table table-striped text-center">
          <thead>
            <tr>
              <th scope="col"></th>
              <th scope="col">{{ T.username }}</th>
              <th v-for="alias in aliases" :key="alias" colspan="2" scope="col">
                {{ alias }}
              </th>
              <th colspan="2" scope="col">{{ T.wordsTotal }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="rank in scoreboard"
              :key="rank.username"
              :class="rank.username"
            >
              <th>{{ rank.place }}</th>
              <th>
                <div class="username" data-test-merged-username>
                  {{ rank.username }}
                </div>
                <div class="name">
                  {{ rank.username != rank.name ? rank.name : ' ' }}
                </div>
              </th>
              <th
                v-for="alias in aliases"
                :key="alias"
                class="numeric"
                colspan="2"
              >
                {{ rank.contests[alias].points }}
                <span v-if="showPenalty" class="scoreboard-penalty">
                  ({{ rank.contests[alias].penalty }})
                </span>
              </th>
              <th class="numeric" colspan="2" data-total-merged-score>
                {{ rank.total.points }}
                <span v-if="showPenalty" class="scoreboard-penalty">
                  ({{ rank.total.penalty }})
                </span>
              </th>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else>
        <table class="table table-striped text-center">
          <thead>
            <tr>
              <th scope="col"></th>
              <th scope="col">
                {{ T.username }}
              </th>
              <th colspan="2" scope="col">
                {{ T.wordsTotal }}
              </th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import { types } from '../../api_types';
import Multiselect from 'vue-multiselect';

@Component({
  components: {
    Multiselect,
  },
})
export default class ScoreboardMerge extends Vue {
  @Prop() availableContests!: types.ContestListItem[];
  @Prop() scoreboard!: types.MergedScoreboardEntry[];
  @Prop() showPenalty!: boolean;
  @Prop() aliases!: string[];

  T = T;
  ui = ui;
  selectedContests: string[] = [];

  get contestAliases(): string[] {
    return this.availableContests.map((contest) => contest.alias);
  }

  onRemove(contest: string) {
    const index = this.selectedContests.indexOf(contest);
    this.selectedContests.splice(index, 1);
  }

  onSelect(contest: string) {
    this.selectedContests.push(contest);
  }

  @Emit('get-scoreboard')
  onDisplayTable(): string[] {
    return this.selectedContests;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
@import '../../../../../../node_modules/vue-multiselect/dist/vue-multiselect.min.css';

.scoreboard-penalty {
  padding-left: 0.5em;
  opacity: 0.7;
  color: var(--arena-scoreboard-penalty-color);
}

.post {
  overflow-x: scroll;
}

.multiselect__tag {
  background: var(--multiselect-tag-background-color);
}
</style>
