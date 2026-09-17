import { EditorView } from "codemirror";
import { SetText } from "../editor/editor";
import { KarelController } from "../KarelController";


export interface NavbarData {
    openCode: string,
    openWorldIn:string
}


function getCode (editor:EditorView) {
    var file = document.createElement('input');
    file.type = 'file';
    file.accept = '.kj, .kp';
    file.addEventListener('change', function (evt) {
        //@ts-ignore
      var files = evt.target.files; // @ts-ignore FileList object 

      // Loop through the FileList and render image files as thumbnails.
      for (var i = 0, f; (f = files[i]); i++) {
        // Only process text files.
        if (
          f.type &&
          !(f.type.match('text.*') || f.type == 'application/javascript')
        ) {
          continue;
        }

        var reader = new FileReader();

        // Closure to capture the file information.
        reader.onload = (function (theFile) {
          return function (e) {
            
            SetText(editor, reader.result as string); 
          };
        })(f);

        // Read in the file as a data URL.
        reader.readAsText(f);
      }
    });
    file.click();
}

function parseWorld(xml:string) {
  // Parses the xml and returns a document object.
  return new DOMParser().parseFromString(xml, 'text/xml');
}

function getWorldIn(karelController:KarelController) {
  var file = document.createElement('input');
    file.type = 'file';    
    file.accept = '.in';
    file.addEventListener('change', function (evt) {
      //@ts-ignore
      var files = evt.target.files; // FileList object

      // Loop through the FileList and render image files as thumbnails.
      for (var i = 0, f; (f = files[i]); i++) {
        // Only process text files.
        if (f.type && !f.type.match('text.*')) {
          continue;
        }

        var reader = new FileReader();

        // Closure to capture the file information.
        reader.onload = (function (theFile) {
          return function (e) {
            const result = reader.result as string
            karelController.LoadWorld(parseWorld(result));
            karelController.Reset()
          };
        })(f);

        // Read in the file as a data URL.
        reader.readAsText(f);
      }
    });
    file.click();
}

export function HookNavbar(navbar:NavbarData ,editor:EditorView, karelController:KarelController) {
   $(navbar.openCode).on("click", ()=>getCode(editor));
  //  $(navbar.openWorldIn).on("click", ()=>getWorldIn(karelController));

}

