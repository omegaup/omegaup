<template>
  <div class="acl-container">
    <!-- Sidebar: List of Users -->
    <div class="sidebar">
      <b>{{ T.viewAclListUsernameTotalRoles }}</b>
      <ul>
        <li
          v-for="user in aclList"
          :key="user.username"
          :class="{ active: selectedUser === user.username }"
          @click="selectUser(user.username)"
        >
          <span class="username">{{ user.username }}</span>
          <span class="role-count">({{ user.roles.length }})</span>
        </li>
      </ul>
    </div>

    <!-- Main Content: Roles of Selected User -->
    <div class="main-content">
      <h3 v-if="selectedUser" style="text-align: center">
        {{ T.viewAclListRolesForUsername }} {{ selectedUser }}
      </h3>
      <ul v-if="selectedUserRoles.length">
        <li v-for="role in selectedUserRoles" :key="role.acl_id">
          <strong>{{ role.name }}</strong> - {{ role.description }}
          <span v-if="role.alias">({{ role.alias }})</span>
        </li>
      </ul>
      <p v-else>{{ T.viewAclListSelectAUser }}</p>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class UserManageAclList extends Vue {
  @Prop() aclList!: {
    username: string;
    roles: {
      name: string;
      description: string;
      acl_id: number;
      alias?: string;
    }[];
  }[];

  T = T;
  selectedUser: string | null = null;

  mounted() {
    this.$emit('fetch-acl-list', {});
    // Auto-select the first user if available
    if (this.aclList.length > 0) {
      this.selectedUser = this.aclList[0].username;
    }
  }

  selectUser(username: string) {
    this.selectedUser = username;
  }

  get selectedUserRoles() {
    const user = this.aclList.find((u) => u.username === this.selectedUser);
    return user ? user.roles : [];
  }
}
</script>

<style scoped>
.acl-container {
  display: flex;
  height: 100%;
  max-height: 600px; /* Set a max height for the whole container */
}

/* Sidebar Styling */
.sidebar {
  width: 250px;
  background: #f8f9fa;
  border-right: 1px solid #ddd;
  padding: 1rem;
  overflow-y: auto; /* Enable scrolling */
  max-height: 600px; /* Limit height */
}

.sidebar h5 {
  margin-bottom: 1rem;
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
  overflow-y: auto; /* Enable scrolling */
  max-height: 600px; /* Limit height */
}

.main-content h3 {
  margin-bottom: 1rem;
}
</style>
