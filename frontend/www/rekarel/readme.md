# ReKarel
Hosted on: https://kishtarn555.github.io/ReKarel/webapp/

Dev version: https://kishtarn555.github.io/ReKarelDev/webapp/

[Karel](https://en.wikipedia.org/wiki/Karel_(programming_language)) (a modified version of) is the programming language of choice of junnior stages of the Mexican Informatics Olympiad. The main resource used to introduce young students to programming through this language is [Karel.js](https://github.com/omegaup/karel.js) (developed by Omegaup). In its web version you can interactively update Karel's world, write code, and visualize the execution step by step in the graphic interface. ReKarel is a rework of Karel.js, with the propuse of improving the User Experience, patching errors that never made it to production in Karel.js, and making it available for mobile, thus spreading it to a wider audience (according to the [latest census data](https://www.inegi.org.mx/contenidos/saladeprensa/boletines/2024/ENDUTIH/ENDUTIH_23.pdf), **only 48.4% of mexican families** own a computer, **while 82.4% of people above the age of 6** has access to a mobile phone). 


Currently, Karel.js is unusable in mobile devices due to the following reasons:

* CodeMirror 5 isn't mobile compatible.
* WorldRenderer scroll doesn't work on all devices.

This prompts the graphic interface unresponsive, and removes the toolbar.

The new version includes:

* Now with bootstrap 5
* Now with CodeMirror 6


# Features from Karel.js


## Migrations
* [x] Migrated to Bootstrap 5.3.0
* [x] Migrated to JQuery 3.6.1
* [x] Migrated to CodeMirror 6
* [x] Migrated Split.js
## Deprecated
* [x] Removed Karel Ruby as it didn't work and it seemed to be in route of deprecation.
## Mobile
* [x] Supports touch control on both views.
* [x] UI changes to one better suited for small screens
* CodeEditor maybe edited without writing.

## Quality of live
* Settings that allow to set:
    * [ ] ~Prefered programming language (the default is Karel.js).~ 
        * ~If set, syntax higlighting and compile will always try that language.~
    * [x] The view (force one view independent of screen size).
    * [x] Code Font size.
* Hotkeys
    * [x] `Ctrl+K` Decreases editor's font.
    * [x] `Ctrl+L` Increases editor's font.
* [x] Added the ability to zoom in and out of the world.
* [x] Native scrolling with scrollbars for the world renderer.
    * The previous scroll was hard to use IMO.
* [x] Added the ability to specify the name of the file to be downloaded, either code or world.
* [x] Added clean button to remove old messages from console.
* [x] "Are you sure?" before reseting code.
* [x] "Are you sure?" before reseting world.
* [x] Added icons to Karel position.
* [x] If the execution ends in run time error (RTE), it is made more noticable by changing the world background color.
* [x] You can change Karel Codes.
* [x] Dark and Light mode are now enabled. 
# World Editor
* [x] Reworked world editor.

Now, it works similar to spreadsheet software. World edition is based on cell selection and a toolbar.

You can select a rectangular region of cells and:
* [x] Place walls on the selection border (You may use the toolbar or WASD hot-keys).
* [x] Add, remove, clear, set the ammount of beepers in the selection (Use 1 ... 9 or Q E R C hot-keys).
* [x] Move/Rotate Karel (Use G or P hot-keys).

# Execution
* [x] You can step into a function, out of a function or skip a function, similar to other debuggers.
* [x] The stack now also reports what number it is in the stack for readability.
