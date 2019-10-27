<template>
  <div class="ranks-section">
    <section class="rank">
      <CoderOfTheMonth />
    </section>

    <section class="rank">
      <RanksSchools />
    </section>

    <section class="rank">
      <RanksUsers />
    </section>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import OmegaupCoderOfTheMonth from './UserOfTheMonthCard.vue';
import OmegaupRankSchoolsTable from './RankSchoolsTable.vue';
import OmegaupRankUsersTable from './RankUsersTable.vue';

@Component({
  components: {
    CoderOfTheMonth: OmegaupCoderOfTheMonth,
    RanksSchools: OmegaupRankSchoolsTable,
    RanksUsers: OmegaupRankUsersTable,
  },
})
export default class RanksSection extends Vue {
}
</script>

<style lang="scss">
$omegaup-primary--darker: #466aaf;
.ranks-section {
  display: flex;
  margin: 0 auto;
  width: 80%;
  margin-top: 40px;
  margin-bottom: 40px;

  @media only screen and (max-width: 991px) {
    display: block;
  }

  .rank {
    width: 30%;
    margin: 0 auto;
    text-align: center;

    @media only screen and (max-width: 991px) {
      width: 100%;
    }

    .rank-title {
      color: $omegaup-primary--darker;
    }

    .ranks-table {
      table {
        width: 100%;

        th {
          padding: 5px;
        }

        .item-name {
          overflow: hidden;
          text-overflow: ellipsis;
          max-width: 0;
          white-space: nowrap;
        }
      }
    }

    .btn {
      margin: 0 auto;
      margin-top: 20px;
    }
  }
}
</style>