<template>
<div class="multi-user-add-area">
    <textarea
        v-if="isFocused || !usersList.length"
        v-model="bulkContestants"
        class="form-control contestants"
        @input.prevent="onBulkContestantsChanged"
        @blur.prevent="isFocused = false"
    >
    </textarea>
    <div
        v-else
        class="form-control contestants"
    >
        <span href="#" class="edit-icon" @click.prevent="isFocused = true">Editar</span>
        <div class="users-list">
            <div v-for="user in usersList" :key="user" class="users-list__item">
                <span class="tags-input-badge tags-input-badge-pill tags-input-badge-selected-default">{{ user }}</span>
                <a href="#" class="tags-input-remove" @click.prevent="removeUser(user)"></a>
            </div>
        </div>
    </div> 
</div>
</template>

<script lang="ts">
const debounce =  (fn: (event: Event) => void, waitTime: number) => {
    let timer: any = null;

    return (...args: any) => {
        if (timer) {
            clearTimeout(timer);
        }
        
        // eslint-disable-next-line @typescript-eslint/no-this-alias
        const context = this;

        timer = setTimeout(() => {
            fn.apply(context, args);
        }, waitTime);
    }
}

const WAIT_TIME = 1000;
import { Vue, Component, Watch, Prop } from 'vue-property-decorator';

@Component
export default class MultiUserAddArea extends Vue {
    @Prop() users!: string[];
    isFocused: boolean = true;
    bulkContestants: string | null = '';
    usersList: string[] = [];

    onBulkContestantsChanged = debounce(this.onTextAreaChange, WAIT_TIME);

    onTextAreaChange(event: Event) {
        const target = event.target as HTMLTextAreaElement;
        const { value } = target;

        // 1. Separate original string by new lines
        const users = value.split('\n').reduce((acc, line) => {
            // 2. Separate each line by commas
            const lineUsers = line.split(',').reduce((acc, token) => {
                // 3. Separate each token by spaces
                const tokenUsers = token.split(' ').reduce((acc, token) => {
                    if (token.trim() !== '') {
                        acc.push(token.trim());
                    }
                    return acc;
                }, [] as string[]);
                return acc.concat(tokenUsers);
            }, [] as string[]);
            return acc.concat(lineUsers);
        }, [] as string[]);

        console.log({ users })

        this.usersList = Array.from(new Set([...users])); // Removes duplicates
        this.bulkContestants = this.usersList.join(',');
    
        this.isFocused = false;
    }

    removeUser(user: string) {
        this.usersList = this.usersList.filter(u => u !== user);
        this.bulkContestants = this.usersList.join(',');
        this.isFocused = false;
    }

    @Watch('users')
    onUsersChange() {
        // We need to keep the usersList without any user that is part of the users prop
        this.usersList = this.usersList.filter(user => !this.users.includes(user));
        this.bulkContestants = this.usersList.join(',');
    }

    // When the usersList changes, emit the new value to the parent
    @Watch('usersList')
    onUsersListChange() {
        this.$emit('update-users', this.usersList);
    }
}
</script>

<style scoped>
.multi-user-add-area {
    position: relative;
}

.form-control {
    min-height: 4rem;
    overflow: auto;
}

.users-list {
    display: flex;
    flex-wrap: wrap;

    max-width: 95%;
}

.edit-icon {
    position: absolute;
    top: 0;
    right: 1.5rem;

    color: #8bb0d3;
}

.edit-icon:hover {
    color: #666;
    cursor: pointer;
}

.users-list__item {
    position: relative;
}

.users-list > div {
    margin: 0.25rem;
}

.tags-input-badge-pill {
    padding-right: 1.2rem;
}

.tags-input-remove {
    cursor: pointer;
    right: 0.1rem;
    top: 0.1rem;
}

.tags-input-remove:before,
.tags-input-remove:after {
    width: 10px;
}
</style>