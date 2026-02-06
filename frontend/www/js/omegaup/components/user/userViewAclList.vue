<template>
  <div class="acl-container">
    <!-- Sidebar: List of ACLs -->
    <div class="sidebar">
      <ul v-if="aclList.length">
        <li v-for="acl in aclList" :key="acl.acl_id" @click="selectAcl(acl)">
          <span class="acl-alias">
            {{ getAclAlias(acl) }} ({{ acl.users.length }})
          </span>
        </li>
      </ul>
    </div>

    <!-- Main Content: Users in the Selected ACL -->
    <div class="main-content">
      <h3 v-if="selectedAcl" style="text-align: center">
        {{ getAclAlias(selectedAcl) }} ({{ selectedAcl.type }})
      </h3>
      <ul v-if="selectedAcl && selectedAcl.users.length">
        <li v-for="user in selectedAcl.users" :key="user.user_id">
          <strong>
            <omegaup-username :username="user.username" :linkify="true" />
          </strong>
          - {{ user.role_name }}
          <span v-if="user.role_description">
            ({{ user.role_description }})
          </span>
        </li>
      </ul>
      <p v-else>{{ T.viewAclListNoUsers }}</p>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import user_Username from '../user/Username.vue';
import { Acl } from '../../user/profile';

@Component({
  components: {
    'omegaup-username': user_Username,
  },
})
export default class UserManageAclList extends Vue {
  @Prop() aclList!: Acl[];
  T = T;

  selectedAcl: Acl | null = null;

  mounted() {
    this.$emit('fetch-acl-list', {});
    setTimeout(() => {
      if (this.aclList?.length > 0) {
        this.selectedAcl = this.aclList[0];
      }
    }, 1000);
  }

  selectAcl(acl: Acl) {
    this.selectedAcl = acl;
  }

  getAclAlias(acl: Acl) {
    return acl.alias || `ACL ${acl.acl_id}`;
  }
}
</script>

<style scoped>
.acl-container {
  display: flex;
  height: 100%;
  max-height: 600px;
}

.sidebar {
  width: 250px;
  background: #f8f9fa;
  border-right: 1px solid #ddd;
  padding: 1rem;
  overflow-y: auto;
  max-height: 600px;
}

.sidebar ul {
  list-style: none;
  padding: 0;
}

.sidebar li {
  padding: 10px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  border-radius: 5px;
}

.sidebar li:hover,
.sidebar li.active {
  background: #007bff;
  color: white;
}

.main-content {
  flex-grow: 1;
  padding: 1rem;
  overflow-y: auto;
  max-height: 600px;
}

.main-content h3 {
  margin-bottom: 1rem;
}
</style>
