import { KarelNumbers } from "@rekarel/core"
import { AppVars } from "../volatileMemo"
import { WorldViewController } from "../worldViewController/worldViewController"

type BeeperToolbar= {
    addOne: JQuery,
    removeOne: JQuery,
    ammount: JQuery,
    infinite: JQuery,
    clear: JQuery,    
    random: JQuery,    
}
type KarelToolbar= {
    north: JQuery,
    east: JQuery,
    south: JQuery,
    west: JQuery,
}


type WallToolbar= {
    north: JQuery,
    east: JQuery,
    south: JQuery,
    west: JQuery,
    outside: JQuery,
}


export type WorldToolbarData = {
    beepers: BeeperToolbar
    karel: KarelToolbar
    wall: WallToolbar
}



export class WorldBar {
    data : WorldToolbarData;
    constructor (ui: WorldToolbarData) {
        this.data = ui;
    }

    Connect() {              
        const worldController = WorldViewController.GetInstance();
        this.data.beepers.addOne.on("click", ()=>worldController.ChangeBeepers(1));
        this.data.beepers.removeOne.on("click", ()=>worldController.ChangeBeepers(-1));        
        this.data.beepers.infinite.on("click", ()=>worldController.SetBeepers(KarelNumbers.a_infinite));
        this.data.beepers.clear.on("click", ()=>worldController.SetBeepers(0));
        this.data.beepers.random.on("click", ()=>worldController.SetRandomBeepers(AppVars.randomBeeperMinimum, AppVars.randomBeeperMaximum));

        this.data.karel.north.on("click", ()=>worldController.SetKarelOnSelection("north"));
        this.data.karel.east.on("click", ()=>worldController.SetKarelOnSelection("east"));
        this.data.karel.south.on("click", ()=>worldController.SetKarelOnSelection("south"));
        this.data.karel.west.on("click", ()=>worldController.SetKarelOnSelection("west"));
        
        this.data.wall.north.on("click", ()=>worldController.ToggleWall("north"));
        this.data.wall.east.on("click", ()=>worldController.ToggleWall("east"));
        this.data.wall.south.on("click", ()=>worldController.ToggleWall("south"));
        this.data.wall.west.on("click", ()=>worldController.ToggleWall("west"));
        this.data.wall.outside.on("click", ()=>worldController.ToggleWall("outer"));
    }

    OnClick(event:(e:JQuery.Event)=>void) {                    
        this.data.beepers.addOne.on("click", event); 
        this.data.beepers.removeOne.on("click", event); 
        this.data.beepers.infinite.on("click", event); 
        this.data.beepers.clear.on("click", event); 
        this.data.beepers.random.on("click", event); 

        this.data.karel.north.on("click", event); 
        this.data.karel.east.on("click", event); 
        this.data.karel.south.on("click", event); 
        this.data.karel.west.on("click", event); 
        
        this.data.wall.north.on("click", event); 
        this.data.wall.east.on("click", event); 
        this.data.wall.south.on("click", event); 
        this.data.wall.west.on("click", event); 
        this.data.wall.outside.on("click", event); 
    }
    
}