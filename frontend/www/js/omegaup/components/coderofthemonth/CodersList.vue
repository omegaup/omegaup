<template>
  <table class="table table-hover table-responsive-sm coder-of-month-section">
    <thead>
      <tr>
        <th scope="col" class="text-center"></th>
        <th scope="col" class="text-center">
          {{ T.codersOfTheMonthUser }}
        </th>
        <th scope="col" class="text-center">
          {{ T.codersOfTheMonthCountry }}
        </th>
        <th scope="col" class="text-center">
          {{ T.codersOfTheMonthDate }}
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(coder, index) in coders" :key="index" class="coder-row">
        <td class="text-center">
          <img :src="coder.gravatar_100" class="coder-profile-image rounded-circle" />
        </td>
        <td class="text-center align-middle">
          <omegaup-user-username
            :classname="coder.classname"
            :linkify="true"
            :username="coder.username"
            class="coder-name"
          ></omegaup-user-username>
        </td>
        <td class="text-center align-middle">
          <omegaup-countryflag
            :country="coder.country_id"
          ></omegaup-countryflag>
        </td>
        <td class="text-center align-middle coder-date">
          {{ coder.date }}
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import user_Username from '../user/Username.vue';
import country_Flag from '../CountryFlag.vue';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-countryflag': country_Flag,
  },
})
export default class CoderOfTheMonthList extends Vue {
  @Prop() coders!: types.CoderOfTheMonthList[];

  T = T;
}
</script>

<style scoped>
.coder-row {
  transition: all 0.3s ease;
}

.coder-row:hover {
  background-color: var(--coder-row-hover-bg);
  transform: translateX(5px);
}

.coder-date {
  color: var(--coder-date-color);
  font-size: 0.9em;
}

.table {
  margin-bottom: 0;
  background-color: transparent;
}

.table th {
  border-top: none;
  color: var(--table-header-color);
  font-weight: 500;
}

.table td {
  border-top: 1px solid var(--table-border-color);
}

/* Coder of the Month section styles */
.coder-of-month-section {
  transition: background-color 0.3s ease;
  border-radius: 8px;
  overflow: hidden;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: var(--coder-section-shadow);
}

.coder-of-month-section:hover {
  box-shadow: var(--coder-section-hover-shadow);
  transform: translateY(-2px);
  transition: all 0.3s ease;
}

/* Profile image styles */
.coder-profile-image {
  margin: 0 auto 15px;
  display: block;
  border: 3px solid var(--profile-image-border);
  transition: border-color 0.3s ease;
}

.coder-of-month-section:hover .coder-profile-image {
  border-color: var(--profile-image-hover-border);
}

/* Text styles */
.coder-name {
  color: var(--coder-name-color);
  font-size: 1.2em;
  font-weight: bold;
  text-align: center;
  margin-bottom: 5px;
}
</style>
