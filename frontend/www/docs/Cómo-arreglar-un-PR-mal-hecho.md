Para arreglar un PR al que hayas hecho algún commit erróneo, ya que hayas hecho push de los commits no-erróneos, en consola haz:
```
git rebase HEAD~n -i
```
Donde `n` es la cantidad de commits que quieres agrupar.

Esto te muestra los commits como:
```
pick commit-1
pick commit-2
.
.
.
pick commit-n
```
Para quitar los commits chafas, el commit de hasta arriba lo dejas con `pick`. En los siguientes, solamente cambia `pick` por `fixup` o `f` y guarda el archivo. 

Debe verse así:
```
pick commit-1
f commit-2
f commit-3
.
.
f commit-n
```
Haz `git push -f` y en el PR ya no se van a mostrar los commits que marcaste como `fixup`.

