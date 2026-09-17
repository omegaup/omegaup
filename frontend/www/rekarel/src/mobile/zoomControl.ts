import { WorldViewController } from "../worldViewController/worldViewController";

export interface ZoomControlData {
    zoomSlider: JQuery,
    zoomCollapsable: JQuery
}


export class ZoomControl {
    ui:ZoomControlData

    constructor(ui:ZoomControlData) {
        this.ui = ui;
        this.Connect()
    }

    private Connect() {
        this.ui.zoomSlider.on("change input", this.ChangeKarelZoom.bind(this))
        this.ui.zoomCollapsable.on("show.bs.dropdown", ()=> {
            let minValue = parseFloat(this.ui.zoomSlider.attr("min"))
            let maxValue = parseFloat(this.ui.zoomSlider.attr("max"))
            let maxScale = 8;
            let minScale = 0.25;
            let val = (WorldViewController.GetInstance().scale-minScale)/(maxScale - minScale);
            this.ui.zoomSlider.val(val*(maxValue-minValue)+minValue);
        })
    }

    private ChangeKarelZoom () {
        let minValue = parseFloat(this.ui.zoomSlider.attr("min"))
        let maxValue = parseFloat(this.ui.zoomSlider.attr("max"))
        let val = (<number>this.ui.zoomSlider.val()-minValue)/(maxValue - minValue);
        let maxScale = 8;
        let minScale = 0.25;
        
        WorldViewController.GetInstance().SetScale(val*(maxScale-minScale)+minScale)
    }
}