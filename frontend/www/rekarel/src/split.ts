import Split from 'split.js'


function splitPanels (ResizeCanvas: ()=>void) {
    Split(['#splitter-left-pane', '#splitter-right-pane'], {
        sizes: [30, 70],
        onDragEnd: ResizeCanvas,
        minSize: 0,
    });
    Split(['#splitter-left-top-pane', '#splitter-left-bottom-pane'], {
        sizes: [70, 30],
        direction: 'vertical',
        minSize: 0,
    });


    
    Split(['#mobileCodePanel', '#mobileWorldPanel', '#mobileStatePanel'], {
        sizes: [30, 40, 30],
        direction: 'vertical',
        onDragEnd: ResizeCanvas,
        minSize: 0,
        gutterSize: 15
    });
}

export {splitPanels};