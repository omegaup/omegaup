interface Commit {
    forward: ()=>void
    backward:()=>void
}


export class Operation {
    commits: Commit[]

    constructor() {
        this.commits = []
    }

    forward() {
        for (let c of this.commits) {
            c.forward();
        }
    }
    backward() {
        for (let c of this.commits) {
            c.backward();
        }
    }

    addCommit(commit:Commit) {
        this.commits.push(commit);
    }
}

const HISTORY_MAX_SIZE = 1000;

export class KarelHistory {
    private operations: Operation[];
    private currentOperation:Operation | null;
    private size:number;
    private head:number;
    constructor() {
        this.operations = [];
        this.currentOperation = null;
        this.size = 0;
        this.head = 0;

    }

    StartOperation():Operation {
        this.EndOperation();
        this.currentOperation = new Operation();
        return this.currentOperation;
    }

    EndOperation() {
        if (this.currentOperation == null) {
            return;
        }
        if (this.currentOperation.commits.length===0) {
            
            return;
        }
        //Remove all undone operations up to this point.
        while (this.head < this.operations.length) {
            this.size -= this.operations[this.operations.length-1].commits.length;
            this.operations.pop();
        }
        this.operations.push(this.currentOperation);
        this.size += this.currentOperation.commits.length;
        this.head++;
        this.currentOperation = null;
        this.TrimHistory();
    }
    Clear() {
        this.size = 0;
        this.operations = [];
    }

    Undo():boolean {
        if (this.head <= 0) {
            return false;
        }
        this.head--;
        this.operations[this.head].backward();
        return true;
    }

    Redo():boolean {
        if (this.head >= this.operations.length) {
            return false;
        }
        this.operations[this.head].forward();
        this.head++;
    }

    private TrimHistory() {
        while (this.operations.length > 1 && this.size > HISTORY_MAX_SIZE) {
            this.size-=this.operations[0].commits.length;
            this.operations.shift();
            this.head--;
        }
    }
}


export type HistoryToolbar = {
    undo:JQuery,
    redo:JQuery
}