<template>
  <div class="acl-container">
    <!-- Sidebar: List of ACLs Owned by the User -->
    <div class="sidebar">
      <b>{{ T.viewAclListOwnedAcls }}</b>
      <ul>
        <li
          v-for="acl in allAcls"
          :key="acl.acl_id"
          :class="{ active: selectedAcl === acl.acl_id }"
          @click="selectAcl(acl.acl_id)"
        >
          <span class="acl-alias">
            {{ getAclAlias(acl.acl_id) }} ({{ getUserCount(acl.acl_id) }})
          </span>
        </li>
      </ul>
    </div>

    <!-- Main Content: Users in the Selected ACL -->
    <div class="main-content">
      <h3 v-if="selectedAcl" style="text-align: center">
        {{ T.viewAclListUsersForAcl }} {{ getAclAlias(selectedAcl) }}
      </h3>
      <ul v-if="selectedAclUsers.length">
        <li v-for="user in selectedAclUsers" :key="user.user_id">
          <strong>{{ user.username }}</strong> - {{ user.role_name }}
          <span v-if="user.role_description">({{ user.role_description }})</span>
        </li>
      </ul>
      <p v-else>{{ T.viewAclListSelectAnAcl }}</p>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from "vue-property-decorator";
import T from "../../lang";

@Component
export default class UserManageAclList extends Vue {
  @Prop() aclList!: {
    acls: {
      acl_id: number;
      type: string;
      alias: string | null;
    }[];
    roles: {
      acl_id: number;
      user_id: number;
      username: string;
      role_id: number;
      role_name: string;
      role_description: string;
    }[];
  };

  T = T;
  selectedAcl: number | null = null;

  mounted() {
    this.$emit("fetch-acl-list", {});
    // Auto-select the first ACL if available
    setTimeout(() => {
      if (this.allAcls.length > 0) {
        this.selectedAcl = this.allAcls[0].acl_id;
      }
    }, 1000);
  }

  // Show all ACLs instead of just owned ones
  get allAcls() {
    return this.aclList?.acls || [];
  }

  // Get users in the selected ACL
  get selectedAclUsers() {
    if (!this.selectedAcl) return [];
    return this.aclList?.roles?.filter((role) => role.acl_id === this.selectedAcl);
  }

  // Select an ACL when clicked
  selectAcl(aclId: number) {
    this.selectedAcl = aclId;
  }

  // Get ACL alias or default to "ACL <id>"
  getAclAlias(aclId: number) {
    const acl = this.aclList.acls.find((a) => a.acl_id === aclId);
    return acl ? acl.alias || `ACL ${acl.acl_id}` : `ACL ${aclId}`;
  }

  // Get number of users in an ACL
  getUserCount(aclId: number) {
    return this.aclList?.roles?.filter((role) => role.acl_id === aclId).length || 0;
  }
}
</script>

<style scoped>
.acl-container {
  display: flex;
  height: 100%;
  max-height: 600px;
}

/* Sidebar Styling */
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

/* Main Content */
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
