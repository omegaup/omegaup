Si ya hiciste un commit y quieres cambiar el mensaje, usa `git commit --amend`, esto va a abrir el editor donde puedes cambiar el mensaje

```
Mensaje del commit

# Please enter the commit message for your changes. Lines starting
# with '#' will be ignored, and an empty message aborts the commit.
```

Cambia el mensaje y guarda el archivo. Para asegurarte que el cambio se realizó correctamente, puedes hacer `git log` y revisar que el mensaje del commit se haya actualizado.

```
commit id
Author: Fulano
Date:   fecha

    Nuevo Mensaje
```

Si ya habías hecho push de ese commit, haz `git push -f` para que se actualice.