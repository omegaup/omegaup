<template>
  <div class="container-lg p-5">
    <div class="card">
      <h5 class="card-header">
        {{
          UI.formatString(T.authorRankRangeHeader, {
            lowCount: (page - 1) * length + 1,
            highCount: page * length,
          })
        }}
      </h5>
      <div class="card-body" v-if="showControls">
        <template v-if="page > 1">
          <a v-bind:href="`/rank/authors/?page=${page - 1}`">
            {{ T.wordsPrevPage }}</a
          >
          <span v-show="showNextPage">|</span>
        </template>
        <a
          v-show="showNextPage"
          v-bind:href="`/rank/authors/?page=${page + 1}`"
          >{{ T.wordsNextPage }}</a
        >
      </div>
      <table class="table mb-0">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">{{ T.wordsAuthor }}</th>
            <th scope="col" class="text-right">{{ T.rankScore }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-bind:key="index" v-for="(author, index) in rankingData.ranking">
            <th scope="row">{{ author.author_ranking || index }}</th>
            <td>
              <omegaup-countryflag
                v-bind:country="author.country_id"
              ></omegaup-countryflag>
              <omegaup-user-username
                v-bind:classname="author.classname"
                v-bind:linkify="true"
                v-bind:username="author.username"
              ></omegaup-user-username>
              <span v-if="author.name">
                <br />
                {{ author.name }}
              </span>
            </td>
            <td class="text-right">
              {{ author.author_score.toFixed(2) }}
            </td>
          </tr>
        </tbody>
      </table>
      <div class="card-footer" v-if="showControls">
        <template v-if="page > 1">
          <a v-bind:href="`/rank/authors/?page=${page - 1}`">
            {{ T.wordsPrevPage }}</a
          >
          <span v-show="showNextPage">|</span>
        </template>
        <a
          v-show="showNextPage"
          v-bind:href="`/rank/authors/?page=${page + 1}`"
          >{{ T.wordsNextPage }}</a
        >
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { OmegaUp } from '../../omegaup';
import T from '../../lang';
import * as UI from '../../ui';
import user_Username from '../user/Username.vue';
import { types } from '../../api_types';
import CountryFlag from '../CountryFlag.vue';

@Component({
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-countryflag': CountryFlag,
  },
})
export default class AuthorsRank extends Vue {
  @Prop() page!: number;
  @Prop() length!: number;
  @Prop() rankingData!: types.AuthorsRank;

  T = T;
  UI = UI;

  get showNextPage(): boolean {
    return this.length * this.page < this.rankingData.total;
  }

  get showControls(): boolean {
    return this.showNextPage || this.page > 1;
  }
}
</script>
