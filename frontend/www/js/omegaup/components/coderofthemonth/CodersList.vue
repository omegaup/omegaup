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
          <img :src="coder.gravatar_32" class="coder-profile-image" />
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
  background-color: rgba(0, 0, 0, 0.05);
  transform: translateX(5px);
}

.coder-date {
  color: #666;
  font-size: 0.9em;
}

.table {
  margin-bottom: 0;
  background-color: transparent;
}

.table th {
  border-top: none;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
}

.table td {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

/* Coder of the Month section styles */
.coder-of-month-section {
  transition: background-color 0.3s ease;
  border-radius: 8px;
  overflow: hidden;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.coder-of-month-section:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
  transition: all 0.3s ease;
}

/* Profile image styles */
.coder-profile-image {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  margin: 0 auto 15px;
  display: block;
  border: 3px solid rgba(255, 255, 255, 0.2);
  transition: border-color 0.3s ease;
}

.coder-of-month-section:hover .coder-profile-image {
  border-color: rgba(255, 255, 255, 0.4);
}

/* Text styles */
.coder-name {
  color: white;
  font-size: 1.2em;
  font-weight: bold;
  text-align: center;
  margin-bottom: 5px;
}
</style>
