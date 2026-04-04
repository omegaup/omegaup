<template>
  <table v-if="showRanking" class="mini-ranking">
    <thead>
      <tr>
        <th></th>
        <th>{{ T.contestParticipant }}</th>
        <th class="total" colspan="2">{{ T.wordsTotal }}</th>
        <th></th>
      </tr>
    </thead>
    <tbody class="user-list-template">
      <tr v-for="user in users">
        <td class="position">{{ user.position }}</td>
        <td class="user">
          <omegaup-user-username
            :classname="user.classname"
            :username="user.username"
            :country="user.country"
          >
          </omegaup-user-username>
        </td>
        <td class="points">
          {{ user.points.toFixed(2) }}
        </td>
        <td class="penalty">{{ user.penalty.toFixed(0) }}</td>
      </tr>
    </tbody>
  </table>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import user_Username from '../user/Username.vue';
@Component({
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class ArenaNavbarMiniranking extends Vue {
  @Prop() showRanking!: boolean;
  @Prop() users!: omegaup.UserRank[];
  T = T;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.navbar .mini-ranking {
  width: 18em;
  margin-top: 2em;
}

.navbar .mini-ranking td {
  border: 1px solid var(--arena-navbar-miniranking-td-border-color);
  padding: 0.2em;
}

.navbar .mini-ranking th {
  padding: 0.2em;
}

.navbar .mini-ranking .position,
.navbar .mini-ranking .points,
.navbar .mini-ranking .penalty {
  text-align: center;
}

.navbar .mini-ranking .user,
.navbar .mini-ranking .user div span {
  width: 10em;
  max-width: 10em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.navbar .mini-ranking td.points {
  border-right-style: dotted;
}

.navbar .mini-ranking td.penalty {
  border-left-width: 0;
}
</style>
