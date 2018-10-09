<template>
    <div class="panel panel-default no-bottom-margin">
        <div class="panel-heading">
            <h3 class="panel-title">My submissions</h3>
        </div>
        <div class="panel-body">
            <omegaup-run-table
                :data="runs"
                :showPager="true"
                :showContest="true"
                :showPoints="false"
                :showRejudge="false"
                :showDetails="false"
                :showDisqualify="false"
                :showProblem="true"
                :showSubmit="false"
                :showUser="false"></omegaup-run-table>
        </div>
    </div>
</template>
<script>
import RunTable from  './arena/RunTable.vue';
import {T} from '../omegaup.js';
export default {
    props: {
        runs: Array
    },
    components: {'omegaup-run-table': RunTable}
}
</script>
