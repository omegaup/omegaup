import { WorldRenderer } from "../worldRenderer";
import { KarelController } from "../KarelController";
import { World } from "@rekarel/core";
import { SelectionBox, SelectionWaffle } from "./waffle";
import { CellSelection, SelectionState } from "./selection";
import { CellPair } from "../cellPair";


type Gizmos = {
    selectionBox: SelectionBox,
    HorizontalScrollElement: JQuery,
    VerticalScrollElement: JQuery,
}

type MouseState = {
    cursorX: number,
    cursorY: number,
    cellPair: CellPair,
}

type ClickMode = "normal" | "alternate"

type PinchData = {
    pointers: PointerEvent[]
    prevDiff:number
    startZoom:number
    freed:boolean
    cell:CellPair
    proportions:{left: number, bottom:number}
}

type OnBeepersChangeCallback = (beepers:number)=>void

class WorldViewController {
    renderer: WorldRenderer
    container: HTMLElement
    gizmos: Gizmos
    scale: number;
    state: MouseState
    selection: CellSelection;
    private lock: boolean;
    private karelController : KarelController;
    private waffle: SelectionWaffle
    private clickMode: ClickMode
    private pinch: PinchData
    private followScroll:boolean
    private onBeepersChangeListeners;
    private static _instance:WorldViewController;

    constructor(renderer: WorldRenderer, karelController: KarelController, container: HTMLElement,  gizmos: Gizmos) {
        WorldViewController._instance = this;
        this.renderer = renderer;
        this.container = container;
        this.lock = false;
        this.karelController = karelController;
        this.selection = new CellSelection()
        this.selection.SetData({
            r: 1,
            c: 1,
            rows: 1,
            cols: 1,
            dr: 1,
            dc: 1,
            state:"normal"
        });
        this.state = {
            cursorX: 0,
            cursorY: 0,
            cellPair: {r:0, c:0}
        }
        this.gizmos = gizmos;
        this.scale = 1;


        this.karelController.RegisterResetObserver(this.OnReset.bind(this));
        this.karelController.RegisterNewWorldObserver(this.OnNewWorld.bind(this));
        this.karelController.RegisterStepController(this.onStep.bind(this));

        this.waffle = new SelectionWaffle(gizmos.selectionBox);
        this.clickMode = "normal";
        this.pinch = {
            pointers: [],
            prevDiff: -1,
            startZoom : 1,
            freed:false,
            cell: {r:1,c:1},
            proportions: {left:0,bottom:0},
        }
        this.followScroll = true;
        this.onBeepersChangeListeners = [];
    }

    static GetInstance() {
        return WorldViewController._instance;
    }

    
    SetClickMode(mode:ClickMode) {
        this.clickMode = mode;
    }
    
    Lock() {
        this.lock = true;
    }
    
    UnLock() {
        this.lock = false;
    }
    
    
    
    
    GetBeepersInBag(): number {
        return this.karelController.world.bagBuzzers;
    }
    
    SetBeepersInBag(amount : number) {
        this.karelController.world.setBagBuzzers(amount);
        this.NotifyBeeperBagUpdate(amount);
    }
    

    RegisterBeeperBagListener(listener:OnBeepersChangeCallback) {
        this.onBeepersChangeListeners.push(listener);
    }
    
    CheckUpdate() {
        if (this.karelController.world.dirty) {
            this.Update();
        }
    }
    
    ErrorMode() {
        this.renderer.ErrorMode();
        this.Update();
    }
    
    NormalMode() {
        this.renderer.NormalMode();
        this.Update();
    }
    
    Select(r: number, c: number, r2: number, c2: number, state:SelectionState="normal") {
        if (
            r > this.karelController.world.h ||
            c > this.karelController.world.w ||
            r < 1 ||
            c < 1
            
        ) {
            return;
        }
        
        this.selection.SetData({
            r: r,
            c: c,
            rows: Math.abs(r - r2) + 1,
            cols: Math.abs(c - c2) + 1,
            dr: r <= r2 ? 1 : -1,
            dc: c <= c2 ? 1 : -1,
            state:state
        });
        this.UpdateGutter();
        this.UpdateWaffle();
    }
    
    GetCoords2() {
        return {
            r2:this.selection.r + (this.selection.rows-1)*this.selection.dr,
            c2:this.selection.c + (this.selection.cols-1)*this.selection.dc,
        }
    }
    
    MoveSelection(dr: number, dc: number, moveSecond =false) {
        let {r2,c2}=this.GetCoords2();
        let r = this.selection.r;
        let c = this.selection.c;
        if (moveSecond) {                    
            r2 += dr;
            c2 += dc;      
            
        } else {
            r += dr;
            c += dc;
            r2=r;
            c2=c;
            
        }
        if (r < 1 || c < 1 || r > this.karelController.world.h || c > this.karelController.world.w) {
            return;
        }
        if (r2 < 1 || c2 < 1 || r2 > this.karelController.world.h || c2 > this.karelController.world.w) {
            return;
        }
        
        this.TrackFocus(r2,c2);
        this.Select(r, c, r2, c2);
    }
    
    UpdateWaffle() {
        this.waffle.UpdateWaffle(this.selection, this.renderer);
    }
    
    SetScale(scale: number, updateScroll:boolean=true) {
        this.renderer.scale = scale ;
        this.scale = scale;
        //FIXME, this should be in update waffle
        // this.gizmos.selectionBox.bottom.style.maxWidth = `${this.renderer.CellSize * scale}px`;
        // this.gizmos.selectionBox.bottom.style.minWidth = `${this.renderer.CellSize * scale}px`;
        
        // this.gizmos.selectionBox.top.style.maxWidth = `${this.renderer.CellSize * scale}px`;
        // this.gizmos.selectionBox.top.style.minWidth = `${this.renderer.CellSize * scale}px`;
        
        // this.gizmos.selectionBox.left.style.maxHeight = `${this.renderer.CellSize * scale}px`;
        // this.gizmos.selectionBox.left.style.minHeight = `${this.renderer.CellSize * scale}px`;
        
        // this.gizmos.selectionBox.right.style.maxHeight = `${this.renderer.CellSize * scale}px`;
        // this.gizmos.selectionBox.right.style.minHeight = `${this.renderer.CellSize * scale}px`;
        
        // this.gizmos.selectionBox.bottom.style.top = `${this.renderer.CellSize * scale}px`;
        // this.gizmos.selectionBox.right.style.left = `${this.renderer.CellSize * scale}px`;
        
        
        this.UpdateWaffle();
        this.Update();             
        this.UpdateScrollElements();   
        if (updateScroll) {
            this.ReFocusCurrentElement();
        }
    }
    
    RecalculateScale() {
        this.renderer.scale = this.scale;
        this.UpdateWaffle();
        this.Update();             
        this.UpdateScrollElements();   
    }
    
    ClientXYToStateXY(clientX:number, clientY:number) {
        let canvas = this.renderer.canvasContext.canvas;
        let boundingBox = canvas.getBoundingClientRect();
        let x = (clientX - boundingBox.left) * canvas.width / boundingBox.width;
        let y = (clientY - boundingBox.top) * canvas.height / boundingBox.height;
        x /= this.renderer.scale;
        y /= this.renderer.scale;
        return {x,y};
    }
    
    ClientXYToProportions(clientX:number, clientY:number) {
        let canvas = this.renderer.canvasContext.canvas;
        let boundingBox = canvas.getBoundingClientRect();
        let x = (clientX - boundingBox.left)/ boundingBox.width;
        let y = (clientY - boundingBox.top) / boundingBox.height;
        
        let left = x;
        let bottom = 1-y;
        return {left,bottom};
    }
    
    TrackMouse(e: MouseEvent) {
        let {x,y} = this.ClientXYToStateXY(e.clientX, e.clientY);
        this.state.cursorX = x;
        this.state.cursorY = y;
        this.state.cellPair = this.renderer.PointToCell(this.state.cursorX, this.state.cursorY);
        
        if (this.selection.state === "selecting") {
            let cell = this.state.cellPair;
            this.ExtendSelection(cell.r, cell.c, "selecting");
        }
        
    }
    
    ExtendSelection(r:number, c:number, state:SelectionState="normal") {
        if (r < 0 || r > KarelController.GetInstance().world.h) return;
        if (c < 0 || c > KarelController.GetInstance().world.w) return;
        
        this.Select(this.selection.r, this.selection.c, r, c,state);
    }
    
    ClickUp(e: MouseEvent) {
        let cell = this.renderer.PointToCell(this.state.cursorX, this.state.cursorY);
        if (this.selection.state!=="selecting") return; 
        this.selection.state = "normal";
        if (this.clickMode === "normal") {
            this.ExtendSelection(cell.r, cell.c);
        } else {            
            this.Select(cell.r, cell.c, this.selection.r, this.selection.c);            
        }
    }
    
    ClickDown(e:MouseEvent) {
        e.preventDefault();
        this.renderer.canvasContext.canvas.focus();
        if (this.clickMode === "normal") {
            let cell = this.renderer.PointToCell(this.state.cursorX, this.state.cursorY);
            if (cell.r < 0) {
                return;
            }
            if (e.shiftKey) {
                this.Select(this.selection.r, this.selection.c, cell.r, cell.c, "selecting");
            } else {
                this.Select(cell.r, cell.c, cell.r, cell.c, "selecting");
            }            
        } else {
            this.selection.state = "selecting";
        }
        $(":focus").blur();
        
    }
    
    PointerDown(e:PointerEvent) {
        this.pinch.pointers.push(e);        
    }
    PointerUp(e:PointerEvent) {
        const index = this.pinch.pointers.findIndex(
            (cachedEv) => cachedEv.pointerId === e.pointerId,
        );
        if (index !== -1)
            this.pinch.pointers.splice(index, 1);
        
        if (this.pinch.pointers.length < 2) {
            this.pinch.prevDiff = -1;
            if (this.renderer.Snap()) {
                this.Update();
                this.UpdateWaffle();
            }
            this.followScroll = true;
        }
        
    }
    PointerMove(e:PointerEvent) {
        
        const index = this.pinch.pointers.findIndex(
            (cachedEv) => cachedEv.pointerId === e.pointerId,
        );
        if (index !== -1)
            this.pinch.pointers[index] = e;
        // If two pointers are down, check for pinch gestures
        if (this.pinch.pointers.length === 2) {
            
            let cX = (this.pinch.pointers[0].clientX + this.pinch.pointers[1].clientX)/2;
            let cY = (this.pinch.pointers[0].clientY + this.pinch.pointers[1].clientY)/2;
            this.pinch.proportions = this.ClientXYToProportions(cX, cY);
            // Calculate the distance between the two pointers
            const diffX = Math.abs(this.pinch.pointers[0].clientX - this.pinch.pointers[1].clientX);
            const diffY = Math.abs(this.pinch.pointers[0].clientY - this.pinch.pointers[1].clientY);
            let curDiff =  Math.sqrt(diffX*diffX+diffY*diffY);
            if (this.pinch.prevDiff > 0) {
                let delta = curDiff/this.pinch.prevDiff;
                if (!this.pinch.freed) {
                    if (0.9 < delta && delta < 1.1 ) {
                        return;
                    } else if (0.8 < delta && delta < 1) {
                        delta = (delta-0.8)*2+0.8;
                    } else if (1 < delta && delta < 1.2) {
                        delta = (delta-1.1)*2+1.1;
                    } else {
                        this.pinch.freed = true;
                    }
                }
                
                
                let newZoom = this.pinch.startZoom* delta;
                if (newZoom < 0.5) newZoom=0.5;
                if (newZoom > 8) newZoom=8;
                this.SetScale(newZoom, false);
                this.FocusCellToScreenPortion(
                    this.pinch.cell.r, 
                    this.pinch.cell.c,
                    this.pinch.proportions.left,
                    this.pinch.proportions.bottom,
                    false // Do not snap
                );
            } else {
                this.pinch.prevDiff = curDiff;
                this.pinch.startZoom = this.scale;
                this.pinch.freed = false;
                let {x, y} = this.ClientXYToStateXY(cX,cY);
                this.pinch.cell = this.renderer.PointToCell(x,y, true);
                this.followScroll = false;
                
            }
        }
    }

    SetKarelOnSelection(direction: "north" | "east" | "west" | "south" = "north") {
        if (this.lock) return;
        this.karelController.world.move(this.selection.r, this.selection.c);
        switch (direction) {
            case "north":
                this.karelController.world.rotate('NORTE');
                break;
            case "east":
                this.karelController.world.rotate('ESTE');
                break;
            case "south":
                this.karelController.world.rotate('SUR');
                break;
            case "west":
                this.karelController.world.rotate('OESTE');
                break;
        }
        this.Update();

    }

    ChangeBeepers(delta: number) {
        if (this.lock) return;
        if (delta === 0) {
            return;
        }

        const history = KarelController.GetInstance().GetHistory();
        const op = history.StartOperation();
        
        let rmin = Math.min(this.selection.r, this.selection.r + (this.selection.rows - 1)*this.selection.dr);
        let rmax = Math.max(this.selection.r, this.selection.r + (this.selection.rows - 1)*this.selection.dr);
        let cmin = Math.min(this.selection.c, this.selection.c + (this.selection.cols - 1)*this.selection.dc);
        let cmax = Math.max(this.selection.c, this.selection.c + (this.selection.cols - 1)*this.selection.dc);
        for (let i =rmin; i<=rmax; i++) {
            for (let j=cmin; j <=cmax; j++) {
                const oriBuzzers = this.karelController.world.buzzers(i,j);
                let buzzers = this.karelController.world.buzzers(i,j);
                if (buzzers < 0 && delta < 0) {
                    //Do nothing
                    continue;
                }
                buzzers += delta;
                if (buzzers < 0) {
                    buzzers = 0;
                }
                this.karelController.world.setBuzzers(
                    i,
                    j,
                    buzzers
                );
                op.addCommit({
                    forward:()=> {
                        this.karelController.world.setBuzzers(
                            i,
                            j,
                            buzzers
                        );
                    },
                    backward: ()=> {
                        
                        this.karelController.world.setBuzzers(
                            i,
                            j,
                            oriBuzzers
                        );
                    }
                })
            }
        }
        history.EndOperation();
        this.Update();
    }

    SetRandomBeepers(minimum: number, maximum: number) {
        if (this.lock) return;
        const history = KarelController.GetInstance().GetHistory();
        const op = history.StartOperation();

        let rmin = Math.min(this.selection.r, this.selection.r + (this.selection.rows - 1)*this.selection.dr);
        let rmax = Math.max(this.selection.r, this.selection.r + (this.selection.rows - 1)*this.selection.dr);
        let cmin = Math.min(this.selection.c, this.selection.c + (this.selection.cols - 1)*this.selection.dc);
        let cmax = Math.max(this.selection.c, this.selection.c + (this.selection.cols - 1)*this.selection.dc);
        for (let i =rmin; i<=rmax; i++) {
            for (let j=cmin; j <=cmax; j++) {
                let amount = Math.round( Math.random()*(maximum-minimum)+minimum);
                const oriBuzzers = this.karelController.world.buzzers(i, j);
                if (oriBuzzers === amount) {
                    continue;
                }
                op.addCommit({
                    forward:()=>
                        this.karelController.world.setBuzzers(i, j, amount),
                    backward:()=>
                        this.karelController.world.setBuzzers(i, j, oriBuzzers),
                })
                this.karelController.world.setBuzzers(i, j, amount);
            }
        }
        history.EndOperation();
        this.Update();
    }

    SetBeepers(amount: number) {
        if (this.lock) return;
        if (amount < 0) {
            throw {message:`Cannot set to a negative amount of beepers, (${amount})`};
        }
        const history = KarelController.GetInstance().GetHistory();
        const op = history.StartOperation();
        let rmin = Math.min(this.selection.r, this.selection.r + (this.selection.rows - 1)*this.selection.dr);
        let rmax = Math.max(this.selection.r, this.selection.r + (this.selection.rows - 1)*this.selection.dr);
        let cmin = Math.min(this.selection.c, this.selection.c + (this.selection.cols - 1)*this.selection.dc);
        let cmax = Math.max(this.selection.c, this.selection.c + (this.selection.cols - 1)*this.selection.dc);
        for (let i =rmin; i<=rmax; i++) {
            for (let j=cmin; j <=cmax; j++) {
                const oriBuzzers = this.karelController.world.buzzers(i, j);
                if (oriBuzzers === amount) {
                    continue;
                }
                op.addCommit({
                    forward: ()=>
                        this.karelController.world.setBuzzers(i, j, amount),
                    backward: ()=>
                        this.karelController.world.setBuzzers(i, j, oriBuzzers)
                })
                this.karelController.world.setBuzzers(i, j, amount);
            }
        }
        history.EndOperation();
        this.Update();
    }

    AppendUnitToBeepers(amount: number) {
        const history = KarelController.GetInstance().GetHistory();
        const op = history.StartOperation(); 
        this.selection.forEach(
            (r, c)=> {
                let beepers = this.karelController.world.buzzers(r, c);
                let nextBeepers = beepers*10+amount;
                if (beepers === -1) {
                    // Skip infinity beepers
                    return;
                }
                op.addCommit({
                    forward: ()=> this.karelController.world.setBuzzers(r, c, nextBeepers),
                    backward: ()=> this.karelController.world.setBuzzers(r, c, beepers),
                });
                this.karelController.world.setBuzzers(r,c, nextBeepers);
            }
        );
        
        history.EndOperation();
        this.Update();
    }

    DivideBeepers(divider:number) {
        const history = KarelController.GetInstance().GetHistory();
        const op = history.StartOperation(); 
        this.selection.forEach(
            (r, c)=> {
                let beepers = this.karelController.world.buzzers(r, c);
                if (beepers === -1) {
                    // Skip infinity beepers
                    return;
                }                
                let nextBeepers = Math.trunc(beepers/divider);
                op.addCommit({
                    forward: ()=> this.karelController.world.setBuzzers(r, c, nextBeepers),
                    backward: ()=> this.karelController.world.setBuzzers(r, c, beepers),
                });
                this.karelController.world.setBuzzers(r,c, nextBeepers);
            }
        );
        
        history.EndOperation();
        this.Update();
    }

    SetCellEvaluation(state:boolean) {
        if (this.lock) return;
        const world = this.karelController.world;
        let rmin = Math.min(this.selection.r, this.selection.r + (this.selection.rows - 1)*this.selection.dr);
        let rmax = Math.max(this.selection.r, this.selection.r + (this.selection.rows - 1)*this.selection.dr);
        let cmin = Math.min(this.selection.c, this.selection.c + (this.selection.cols - 1)*this.selection.dc);
        let cmax = Math.max(this.selection.c, this.selection.c + (this.selection.cols - 1)*this.selection.dc);
        for (let i =rmin; i<=rmax; i++) {
            for (let j=cmin; j <=cmax; j++) {
                world.setDumpCell(i, j, state);
            }
        }
        this.Update();
    }

    ToggleKarelPosition(rotate:boolean = false) {
        if (this.lock) return;
        const history = KarelController.GetInstance().GetHistory();
        const op = history.StartOperation();        
        const world = this.karelController.world;
        if (world.start_i !==this.selection.r || world.start_j !==this.selection.c ) {
            const orI = world.start_i;
            const orJ = world.start_j;
            op.addCommit({
                forward:() => world.move(this.selection.r, this.selection.c),
                backward:() => world.move(orI, orJ),
            });
            world.move(this.selection.r, this.selection.c)
        }

        if (rotate) {
            function doRotation() {
                world.rotate();
            }
            op.addCommit({
                forward:()=>doRotation(),
                backward:()=>world.rotate()
            })

            doRotation();
        }
        history.EndOperation();
            this.Update();
    }

    FocusOrigin() {
        this.container.scrollLeft = 0;
        this.container.scrollTop = this.container.scrollHeight - this.container.clientHeight;       
    }

    FocusKarel() {
        let r = this.karelController.world.i;
        let c = this.karelController.world.j;

        this.FocusCellToScreenPortion(r, c,0.5, 0.5, true);
    }
    
    FocusSelection() {
        let r1 = this.selection.r;
        let c1 = this.selection.c;
        
        let {r, c} = this.selection.GetSecondAnchor();

        let cR = (r1+r)/2;
        let cC = (c1+c)/2;
        this.FocusCellToScreenPortion(cR, cC,0.5,0.5);
        this.TrackFocus(r1, c1);
    }

    ReFocusCurrentElement() {
        const origin = this.renderer.GetOrigin();
        this.FocusTo(origin.r, origin.c)
    }

    FocusCellToScreenPortion(r:number, c:number, leftRatio:number, bottomRatio:number, snap:boolean=true) {
        const cols = this.renderer.GetColCount("noRounding");
        const rows = this.renderer.GetRowCount("noRounding");
        const w = this.karelController.world.w;
        const h = this.karelController.world.h;
        let target_c = c - leftRatio * cols;
        let target_r = r - bottomRatio * rows;
        
        if (target_c < 1) target_c =1;
        if (target_r < 1) target_r =1;

        if (target_c > w) target_c = w;
        if (target_r > h) target_r = h;
        this.FocusTo(target_r, target_c, snap);
    }

    TrackFocus(r:number, c:number) {
        let origin = this.renderer.GetOrigin();
        let rows = this.renderer.GetRowCount("floor");
        let cols = this.renderer.GetColCount("floor");

        
        if (rows*cols === 0) {
            this.FocusKarel();
            return;
        }

        if (
            origin.c <= c 
            &&  c < origin.c + cols
            && origin.r <= r
            &&  r < origin.r + rows
        ) {
            //Karel is already on focus.
            return;
        }

        let tr = origin.r;
        let tc = origin.c;

        if (r < tr) {
            tr = r;
        } else if (r >= tr + rows ) {
            tr = r -  rows + 1;
        }

        if (c < tc) {
            tc = c;
        } else if (c >= tc + cols ) {
            tc = c -  cols + 1;
        }

        this.FocusTo(tr,tc);
    }

    TrackFocusToKarel () {

        this.TrackFocus(this.karelController.world.i,this.karelController.world.j);
    }

    FocusTo(r: number, c: number, snap:boolean = true) {
        let worldWidth = this.karelController.world.w;
        let worldHeight = this.karelController.world.h;
        
        let left = (c-1 + 0.1) / (this.karelController.world.w - this.renderer.GetColCount("floor") + 1);
        if (left < 0) {
            c=1;
            left=0;
        } else if(left > 1) {
            c = 1+ (worldHeight - this.renderer.GetRowCount("floor") + 1);
            left =1;
        }
        let top = (r-1 + 0.01) / (this.karelController.world.h - this.renderer.GetRowCount("floor") + 1);

        if (top < 0) {
            r = 1;
            top=0;
        } else if(top > 1) {
            r = 1+(worldHeight - this.renderer.GetRowCount("floor") + 1);
            top =1;
        }

        // let la =0, lb = 1;
        // let ta =0, tb = 1;
        // let left = (la+lb)/2.0;
        // let top = (ta+tb)/2.0;
        // for (let iter = 0; iter < 60; iter++) {
        //     let lm = (la+lb)/2.0;
        //     let tm = (ta+tb)/2.0;
        //     this.ChangeOriginFromScroll( lm, tm);
        //     if (this.renderer.origin.c < c) {
        //         la = lm;
        //     } else if (this.renderer.origin.c > c) {
        //         lb = lm;

        //     } else {
        //         left = lm;
        //         la=lb=lm;
        //     }


        //     if (this.renderer.origin.f < r) {
        //         ta = tm;
        //     } else if (this.renderer.origin.f > r) {
        //         tb = tm;
        //     } else {
        //         top = tm;
        //         ta=tb=tm;
        //     }
        // }
        if (snap) {
            this.renderer.SnappySetOrigin({
                c:c,
                r:r,
            });
        } else {
            
            this.renderer.SmoothlySetOrigin({
                c:c,
                r:r,
            });
        }
        // this.lockScroll=true;
        this.container.scrollLeft = left * (this.container.scrollWidth - this.container.clientWidth);        
        this.container.scrollTop = (1 - top) * (this.container.scrollHeight - this.container.clientHeight);       
        // this.Update();        
        // this.UpdateWaffle(); 
    }

    ToggleWall(which: "north" | "east" | "west" | "south" | "outer", reversible:boolean = true) {
        if (this.lock) return;
        if (reversible) {
            const history = KarelController.GetInstance().GetHistory();
            const op = history.StartOperation();        
            const opSelection = this.selection.GetData();
            op.addCommit({
                forward: ()=>{ 
                    const prevSelection = this.selection.GetData();
                    this.selection.SetData(opSelection);
                    this.ToggleWall(which, false);
                    this.selection.SetData(prevSelection);
                },
                backward: ()=>{
                    
                    const prevSelection = this.selection.GetData();
                    this.selection.SetData(opSelection);
                    this.ToggleWall(which, false);
                    this.selection.SetData(prevSelection);
                },
            })
            history.EndOperation();
        }
        let r=this.selection.r,c=this.selection.c;
        switch (which) {
            case "north":
                r = Math.max(
                    this.selection.r,
                    this.selection.r + (this.selection.rows - 1) * this.selection.dr
                );
                for (let i = 0; i < this.selection.cols; i++) {
                    
                    c = this.selection.c + this.selection.dc * i;
                    this.karelController.world.toggleWall(r, c, 1);
                }
                break;
            case "south":
                r = Math.min(
                    this.selection.r,
                    this.selection.r + (this.selection.rows - 1) * this.selection.dr
                );
                for (let i = 0; i < this.selection.cols; i++) {
                    
                    c = this.selection.c + this.selection.dc * i;
                    this.karelController.world.toggleWall(r, c, 3);
                }
                break;
            case "west":
                c = Math.min(
                    this.selection.c,
                    this.selection.c + (this.selection.cols - 1) * this.selection.dc
                );
                for (let i = 0; i < this.selection.rows; i++) {
                    r = this.selection.r + i * this.selection.dr;
                    
                    this.karelController.world.toggleWall(r, c, 0);
                }
                break;
            case "east":
                c = Math.max(
                    this.selection.c,
                    this.selection.c + (this.selection.cols - 1) * this.selection.dc
                );
                for (let i = 0; i < this.selection.rows; i++) {
                    r = this.selection.r + i * this.selection.dr;
                    
                    this.karelController.world.toggleWall(r, c, 2);
                }
                break;
            case "outer":
                let rmin = Math.min(
                    this.selection.r,
                    this.selection.r + (this.selection.rows - 1) * this.selection.dr
                );
                let rmax = Math.max(
                    this.selection.r,
                    this.selection.r + (this.selection.rows - 1) * this.selection.dr
                );
                let cmin = Math.min(
                    this.selection.c,
                    this.selection.c + (this.selection.cols - 1) * this.selection.dc
                );
                let cmax = Math.max(
                    this.selection.c,
                    this.selection.c + (this.selection.cols - 1) * this.selection.dc
                );
                for (let i = 0; i < this.selection.cols; i++) {
                    
                    c = this.selection.c + i * this.selection.dc;
                    this.karelController.world.toggleWall(rmin, c, 3);
                    this.karelController.world.toggleWall(rmax, c, 1);
                }
                for (let i = 0; i < this.selection.rows; i++) {
                    r = this.selection.r + i * this.selection.dr;
                    
                    this.karelController.world.toggleWall(r, cmin, 0);
                    this.karelController.world.toggleWall(r, cmax, 2);
                }
                break;
        }
        this.Update();
    }

    Undo() {
        if (this.lock) return;
        const KC = KarelController.GetInstance();
        KC.GetHistory().Undo();
        this.Update();
    }

    Redo() {
        if (this.lock) return;
        const KC = KarelController.GetInstance();
        KC.GetHistory().Redo();
        this.Update();
    }

    RemoveEverything() {
        if (this.lock) return;
        
        const history = KarelController.GetInstance().GetHistory();
        const op = history.StartOperation();

        let rmin = Math.min(this.selection.r, this.selection.r + (this.selection.rows - 1)*this.selection.dr);
        let rmax = Math.max(this.selection.r, this.selection.r + (this.selection.rows - 1)*this.selection.dr);
        let cmin = Math.min(this.selection.c, this.selection.c + (this.selection.cols - 1)*this.selection.dc);
        let cmax = Math.max(this.selection.c, this.selection.c + (this.selection.cols - 1)*this.selection.dc);
        const world = this.karelController.world;
        for (let i =rmin; i<=rmax; i++) {
            for (let j=cmin; j <=cmax; j++) {
                const oriBuzzers =  world.buzzers(i,j);
                const oriWalls =  world.walls(i,j);
                if (oriBuzzers!=0) {
                    world.setBuzzers(i, j, 0);
                    op.addCommit({
                        forward:()=>
                            world.setBuzzers(i, j, 0),
                        backward:()=> 
                            world.setBuzzers(i, j, oriBuzzers)                            
                    })
                }
                    
                world.setDumpCell(i, j, false);
                world.setWallMask(i,j,0);
                const nextWalls = world.walls(i,j);
                if (nextWalls!== oriWalls) {
                    op.addCommit({
                        forward:()=>
                            world.setWallMask(i, j, 0),
                        backward:()=> 
                            world.setWallMask(i, j, oriWalls)                            
                    })
                }

            }
        }

        for (let i = rmin; i <= rmax; i++) {
            if (cmin!=1) {
                const oriWalls = world.walls(i, cmin-1);
                const nextWalls = oriWalls & (~(1<<2));
                world.setWallMask(i, cmin-1, nextWalls);
                if (oriWalls != nextWalls) {
                    op.addCommit({
                        forward:()=>
                            world.setWallMask(i, cmin-1, nextWalls),
                        backward:()=> 
                            world.setWallMask(i, cmin-1, oriWalls)                            
                    })
                }                
            }
            if (cmax!=world.w) {
                const oriWalls = world.walls(i, cmax+1);
                const nextWalls = oriWalls & (~(1<<0));
                world.setWallMask(i, cmax+1, nextWalls);
                if (oriWalls != nextWalls) {
                    op.addCommit({
                        forward:()=>
                            world.setWallMask(i, cmax+1, nextWalls),
                        backward:()=> 
                            world.setWallMask(i, cmax+1, oriWalls)                            
                    })
                }                
            }
        }

        for (let j = cmin; j <= cmax; j++) {
            if (rmin!=1) {
                const oriWalls = world.walls(rmin-1, j);
                const nextWalls = oriWalls & (~(1<<1));
                world.setWallMask(rmin-1, j, nextWalls);
                if (oriWalls != nextWalls) {
                    op.addCommit({
                        forward:()=>
                            world.setWallMask(rmin-1, j, nextWalls),
                        backward:()=> 
                            world.setWallMask(rmin-1, j, oriWalls)                            
                    })
                }                
            }
            if (rmax!=world.h) {
                const oriWalls = world.walls(rmax+1, j);
                const nextWalls = oriWalls & (~(1<<3));
                world.setWallMask(rmax+1, j, nextWalls);
                if (oriWalls != nextWalls) {
                    op.addCommit({
                        forward:()=>
                            world.setWallMask(rmax+1, j, nextWalls),
                        backward:()=> 
                            world.setWallMask(rmax+1, j, oriWalls)                            
                    })
                }                
            }
        }

        history.EndOperation();
        this.Update();
    }

    ChangeOriginFromScroll(left:number, top:number) {
        if (!this.followScroll) {
            return;
        }
        let worldWidth = this.karelController.world.w;
        let worldHeight = this.karelController.world.h;

        // this.renderer.SnappySetOrigin({
        //     r: Math.floor(
        //         1 + Math.max(
        //             0,
        //             (worldHeight - this.renderer.GetRowCount("floor") + 1) * top
        //         )
        //     ),
        //     c: Math.floor(
        //         1 + Math.max(
        //             0,
        //             (worldWidth - this.renderer.GetColCount("floor") + 1) * left
        //         )
        //     ),
        // });

        
        this.renderer.SnappySetOrigin({
            r: 
                1 + Math.max(
                    0,
                    (worldHeight - this.renderer.GetRowCount("floor") + 1) * top
                )
            ,
            c: 
                1 + Math.max(
                    0,
                    (worldWidth - this.renderer.GetColCount("floor") + 1) * left
                )
            ,
        });
    }

    UpdateScroll(left: number, top: number): void {
        this.ChangeOriginFromScroll(left, top);
        this.Update();
        this.UpdateWaffle();
    }

    Update() {
        this.karelController.world.dirty=false;
        this.renderer.Draw(this.karelController.world, this.selection);
    }

    UpdateGutter() {
        this.renderer.DrawGutters(this.selection);
    }
   

    UpdateScrollElements() {
        let c = this.renderer.CellSize;
        let h = (this.karelController.world.h * this.scale*c + this.renderer.GutterSize) / window.devicePixelRatio;
        let w = (this.karelController.world.w * this.scale*c + this.renderer.GutterSize) / window.devicePixelRatio;
        this.gizmos.HorizontalScrollElement.css("width", `${w}px`);
        this.gizmos.VerticalScrollElement.css("height", `${h}px`);
        
    }

    private onStep(caller:KarelController, state) {
        this.TrackFocusToKarel();
        this.Update();
    }

    private OnReset(caller: KarelController) {
        this.Update();
        this.TrackFocusToKarel();
    }

    private OnNewWorld(caller: KarelController, world:World) {
        this.Select(1,1,1,1); 
        this.Update();
        this.FocusKarel();
        this.UpdateScrollElements();

    }

    private NotifyBeeperBagUpdate(amount:number) {
        for (const callback of this.onBeepersChangeListeners) {
            callback(amount);
        }
    }

}

export { WorldViewController, Gizmos };