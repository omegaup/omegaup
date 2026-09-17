

export namespace AppVars {
    type DelayListener = (value:number)=> void
    const onDelayChangeListeners:DelayListener[] = []
    export let randomBeeperMinimum = 1;
    export let randomBeeperMaximum = 99;
    let delay = 300;

    export function getDelay() {
        return delay;
    }
    export function setDelay(_delay:number) {
        delay = _delay;
        for (const listener of onDelayChangeListeners) {
            listener(delay);
        }
    }

    export function registerDelayChangeListener(listener:DelayListener) {
        onDelayChangeListeners.push(listener);
    }


}
