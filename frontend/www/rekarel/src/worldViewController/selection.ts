import { CellPair } from "../cellPair"



export type SelectionState = "normal"|"selecting"


interface SelectionData  {
    r:number,
    c:number,
    rows: number
    cols: number
    dr:number,
    dc:number,
    state:SelectionState,
}

export class CellSelection {
    r: number
    c: number
    rows: number
    cols: number
    dr: number
    dc: number
    state:SelectionState

    GetData():SelectionData {
        return {
            r:this.r,
            c:this.c,
            rows: this.rows,
            cols: this.cols,
            dr:this.dr,
            dc:this.dc,
            state:this.state,
        }
    }
    SetData(data:SelectionData) {
        this.r=data.r;
        this.c=data.c;
        this.rows = data.rows;
        this.cols = data.cols;
        this.dr=data.dr;
        this.dc=data.dc;
        this.state=data.state;
    }

    GetSecondAnchor():CellPair {
        return {
            r: this.r + (this.rows-1)*this.dr,
            c: this.c + (this.cols-1)*this.dc, 
        }
    }

    forEach(callback:((r:number, c:number)=>void)) {
        for (let i = 0; i < this.rows; i++) {
            for (let j =0; j < this.cols; j++) {
                callback(
                    (this.r + i * this.dr),
                    (this.c + j * this.dc),
                )
            }
        }
    }
}


