<template>
  <div class="coder-of-month-section">
    <table class="table table-hover table-responsive-sm">
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
  </div>
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

/* Import the coder-of-month styles */
@import url('/css/coder-of-month.css');
</style>
