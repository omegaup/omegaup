import { jsxLanguage } from "@codemirror/lang-javascript";
import { KarelController } from "./KarelController";
import { SetText, setLanguage } from "./editor/editor";
import { getEditors } from "./editor/editorsInstances";
import { deserializeKarelBinary, KarelBinarySerializer } from "@rekarel/binary";

export function HookSession() {
    KarelController.GetInstance().RegisterCompileObserver((_,__, lan)=> SaveSession(lan));
}

export function SaveSession(lang:string) {
    if (sessionStorage == null) {
        return;
    }
    try {
        let code = getEditors()[0].state.doc.toString();
        sessionStorage.setItem("rekarel:code", code);
        sessionStorage.setItem("rekarel:lang", lang);

        const world = KarelController.GetInstance().world;
        const serializer = new KarelBinarySerializer();
        const data = new Uint8Array(serializer.serialize(world));
        if (data.byteLength > 4*1024*1024) {
            //TODO: Add notification
            console.warn("World is not saved due to it being too large")
            return;
        }
        let binaryString = ""
        for (let i = 0; i < data.length; i++) {
            binaryString += String.fromCharCode(data[i]);
        }

        sessionStorage.setItem("rekarel:world", binaryString);
    } catch(e) {
        //TODO: Add notification
        console.log("Error saving session", e)
    }
}

function parseWorld(xml:string) {
    // Parses the xml and returns a document object.
    return new DOMParser().parseFromString(xml, 'text/xml');
  }

export function RestoreSession() {
    if (sessionStorage == null) {
        return;
    }
    
    
    let source =  sessionStorage.getItem("rekarel:code");
    if (source) {
        SetText(getEditors()[0], source);
    }
    let world = sessionStorage.getItem("rekarel:world");
    if (world) {
        const binaryWorld = new Uint8Array(world.length);
    
        for (let i = 0; i < world.length; i++) {
            binaryWorld[i] = world.charCodeAt(i);
        }
        try {
            deserializeKarelBinary(KarelController.GetInstance().world, binaryWorld.buffer);
        } catch(e) {
            console.error("Error deserializing memory", e)
        }
    }
    let language = sessionStorage.getItem("rekarel:lang");
    if (language==="java") {
        setLanguage(getEditors()[0],"java");
    }
    if (language==="pascal") {
        setLanguage(getEditors()[0],"pascal");
    }
}