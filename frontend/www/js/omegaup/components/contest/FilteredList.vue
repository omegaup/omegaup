<template>
  <div class="panel panel-primary">
    <div class="panel">
      <h5 v-if="recommended">{{ T.arenaPageRecommendedContestsText }}</h5>
      <div class="panel-body">
        <table class="contest-list table">
          <thead>
            <tr>
              <th class="col-md-6">{{ T.wordsContest }}</th>
              <th class="col-md-2" v-if="showTimes">{{ T.wordsStartTime }}</th>
              <th class="col-md-2" v-if="showTimes">{{ T.wordsEndTime }}</th>
              <th class="col-md-2" v-if="showTimes">{{ T.wordsDuration }}</th>
              <th class="col-md-2" colspan="2" v-if="showPractice"></th>
              <th class="col-md-2" v-if="showVirtual"></th>
              <th class="col-md-2" v-if="showPublicUpdated">
                {{ T.wordsPublicUpdated }}
              </th>
            </tr>
          </thead>
          <tbody v-for="contest in page" class="contest-list row">
            <tr>
              <td class="col-md-6">
                <a v-bind:href="`/arena/${contest.alias}/`">
                  <span>{{ UI.contestTitle(contest) }}</span>
                  <span
                    class="glyphicon glyphicon-ok"
                    aria-hidden="true"
                    v-if="contest.recommended"
                  ></span>
                </a>
              </td>
              <td class="no-wrap col-md-2" v-if="showTimes">
                <a v-bind:href="getTimeLink(contest.start_time.iso())">{{
                  contest.start_time.long()
                }}</a>
              </td>
              <td class="no-wrap col-md-2" v-if="showTimes">
                <a v-bind:href="getTimeLink(contest.finish_time.iso())">{{
                  contest.finish_time.long()
                }}</a>
              </td>
              <td class="no-wrap col-md-2" v-if="showTimes">
                {{ UI.toDDHHMM(contest.duration) }}
              </td>
              <td class="col-md-2" v-if="showPractice">
                <a v-bind:href="`/arena/${contest.alias}/practice/`">
                  <span>{{ T.wordsPractice }}</span>
                </a>
              </td>
              <td class="col-md-2" v-if="showPractice">
                <a v-bind:href="`/arena/${contest.alias}/#ranking`">
                  <span>{{ T.wordsContestsResults }}</span>
                </a>
              </td>
              <td class="col-md-2" v-if="!UI.isVirtual(contest) && showVirtual">
                <a v-bind:href="`/arena/${contest.alias}/virtual/`">
                  <span>{{ T.virtualContest }}</span>
                </a>
              </td>
              <td class="no-wrap col-md-2" v-if="$parent.showPublicUpdated">
                {{ contest.last_updated.long() }}
              </td>
            </tr>
            <tr>
              <td colspan="5" class="forcebreaks forcebreaks-arena">
                {{ contest.description }}
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr v-if="hasNext || hasPrevious" align="center">
              <td class="no-wrap" v-bind:colspan="pagerColumns">
                <a v-if="hasPrevious" v-on:click="previous" href="#">{{
                  T.wordsPrevPage
                }}</a>
                <span class="page-num">{{ pageNumber }}</span>
                <a v-if="hasNext" v-on:click="next" href="#">{{
                  T.wordsNextPage
                }}</a>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';

@Component({})
export default class List extends Vue {
  @Prop() contests!: omegaup.Contest[];
  @Prop() showTimes!: boolean;
  @Prop() showPractice!: boolean;
  @Prop() showVirtual!: boolean;
  @Prop() showPublicUpdated!: boolean;
  @Prop() recommended!: boolean;

  T = T;
  UI = UI;
  pageNumber = 1;
  pageSize = 10;

  get totalPages(): number {
    return Math.ceil(this.contests.length / this.pageSize);
  }

  get page(): omegaup.Contest[] {
    let first = (this.pageNumber - 1) * this.pageSize;
    return this.contests.slice(first, first + this.pageSize);
  }

  get hasPrevious(): boolean {
    return this.pageNumber > 1;
  }

  get hasNext(): boolean {
    return this.pageNumber < this.totalPages;
  }

  get pagerColumns(): number {
    let cols = 2;
    if (this.showPractice) cols += 1;
    if (this.showVirtual) cols += 1;
    if (this.showTimes) cols += 3;
    if (this.showPublicUpdated) cols += 1;
    return cols;
  }

  next() {
    // TODO: Update history so the back button works correctly.
    if (this.pageNumber >= this.totalPages) {
      return;
    }
    this.pageNumber++;
    document.querySelectorAll('li.nav-item.active')[0].scrollIntoView();
  }

  previous() {
    // TODO: Update history so the back button works correctly.
    if (this.pageNumber === 0) {
      return;
    }
    this.pageNumber--;
    document.querySelectorAll('li.nav-item.active')[0].scrollIntoView();
  }

  getTimeLink(time: string): string {
    return `http://timeanddate.com/worldclock/fixedtime.html?iso=${time}`;
  }
}
</script>
