<template>
  <div v-if="contests.length === 0">
    <div class="empty-category">{{ T.contestListEmpty }}</div>
  </div>
  <div v-else>
    <h5 v-if="recommended">{{ T.arenaPageRecommendedContestsText }}</h5>
    <div class="card-body">
      <table class="contest-list table">
        <thead>
          <tr>
            <th>{{ T.wordsContest }}</th>
            <th v-if="showTimes">{{ T.wordsStartTime }}</th>
            <th v-if="showTimes">{{ T.wordsEndTime }}</th>
            <th v-if="showTimes">{{ T.wordsDuration }}</th>
            <th v-if="showPractice" colspan="2"></th>
            <th v-if="showVirtual"></th>
            <th v-if="showPublicUpdated">
              {{ T.wordsPublicUpdated }}
            </th>
          </tr>
        </thead>
        <tbody class="contest-list">
          <template v-for="contest in page">
            <tr :key="contest.alias">
              <td class="">
                <a :href="ui.contestURL(contest)">
                  <span>{{ ui.contestTitle(contest) }}</span>
                  <span
                    v-if="contest.recommended"
                    class="glyphicon glyphicon-ok"
                    aria-hidden="true"
                  ></span>
                </a>
              </td>
              <td v-if="showTimes">
                <a :href="getTimeLink(contest.start_time.iso())">{{
                  contest.start_time.long()
                }}</a>
              </td>
              <td v-if="showTimes">
                <a :href="getTimeLink(contest.finish_time.iso())">{{
                  contest.finish_time.long()
                }}</a>
              </td>
              <td v-if="showTimes">
                {{ time.toDDHHMM(contest.duration) }}
              </td>
              <td v-if="showPractice">
                <a :href="`/arena/${contest.alias}/practice/`">
                  <span>{{ T.wordsPractice }}</span>
                </a>
              </td>
              <td v-if="showPractice">
                <a :href="`/arena/${contest.alias}/#ranking`">
                  <span>{{ T.wordsContestsResults }}</span>
                </a>
              </td>
              <td v-if="!ui.isVirtual(contest) && showVirtual">
                <a :href="`/contest/${contest.alias}/virtual/`">
                  <span>{{ T.virtualContest }}</span>
                </a>
              </td>
              <td v-if="showPublicUpdated">
                {{ contest.last_updated.long() }}
              </td>
            </tr>
            <tr :key="`${contest.alias}-description`">
              <td colspan="5">
                {{ contest.description }}
              </td>
            </tr>
          </template>
        </tbody>
        <tfoot>
          <tr v-if="hasNext || hasPrevious" align="center">
            <td class="no-wrap" :colspan="pagerColumns">
              <a v-if="hasPrevious" href="#" class="mr-2" @click="previous">{{
                T.wordsPrevPage
              }}</a>
              <span class="page-num">{{ pageNumber }}</span>
              <a v-if="hasNext" href="#" class="ml-2" @click="next">{{
                T.wordsNextPage
              }}</a>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';
import { types } from '../../api_types';

@Component
export default class FilteredList extends Vue {
  @Prop() contests!: types.ContestAdminDetails[];
  @Prop() showTimes!: boolean;
  @Prop() showPractice!: boolean;
  @Prop() showVirtual!: boolean;
  @Prop() showPublicUpdated!: boolean;
  @Prop() recommended!: boolean;

  T = T;
  ui = ui;
  time = time;
  pageNumber = 1;
  pageSize = 10;

  get totalPages(): number {
    return Math.ceil(this.contests.length / this.pageSize);
  }

  get page(): types.ContestAdminDetails[] {
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
    return `https://timeanddate.com/worldclock/fixedtime.html?iso=${time}`;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.empty-category {
  text-align: center;
  font-size: 200%;
  margin: 1em;
  color: var(--arena-contest-list-empty-category-font-color);
}
</style>
