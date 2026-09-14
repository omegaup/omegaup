import { WorldRenderer } from "../worldRenderer";
import { CellSelection } from "./selection";

export type SelectionBox = {
    main: HTMLElement,
    left: HTMLElement,
    right: HTMLElement,
    top: HTMLElement,
    bottom: HTMLElement,
    cursor:HTMLElement
}

export class SelectionWaffle {
    box: SelectionBox

    constructor(box: SelectionBox) {
        this.box = box;
    }



    UpdateWaffle(selection: CellSelection, renderer: WorldRenderer) {

        let point = {
            r: selection.r,
            c: selection.c
        };

        let point2 = {
            r: selection.r + (selection.rows-1) * selection.dr, 
            c: selection.c + (selection.cols-1) * selection.dc
        };

        if (point2.r > point.r) {
            let r = point.r;
            point.r = point2.r;
            point2.r = r;
        }
        if (point2.c < point.c) {
            let c = point.c;
            point.c = point2.c;
            point2.c = c;
        }

        
        let coords = renderer.CellToPoint(point.r, point.c);
        let coords2 = renderer.CellToPoint(point2.r-1, point2.c+1);

        
        let selectionBox = this.box.main;
        selectionBox.style.top = `${coords.y }px`
        selectionBox.style.left = `${coords.x }px`
        const ww = (coords2.x - coords.x);
        const hh = (coords2.y - coords.y);
        const width = `${ww}px`
        const height = `${hh}px`

        if (selection.dc > 0) {
            this.box.cursor.style.left = "-2.75px";
        } else {
            this.box.cursor.style.left = `${ww-2.75}px`;
        }
        if (selection.dr < 0) {
            this.box.cursor.style.top = "-2.75px";
        } else {
            this.box.cursor.style.top = `${hh-2.75}px`;
        }
        this.box.top.style.maxWidth = width;
        this.box.top.style.minWidth = width;

        this.box.bottom.style.maxWidth = width;
        this.box.bottom.style.minWidth = width;

        this.box.right.style.left = width;

        this.box.left.style.maxHeight = height;
        this.box.left.style.minHeight = height;

        this.box.right.style.maxHeight = height;
        this.box.right.style.minHeight = height;

        this.box.bottom.style.top = height;

    }
}