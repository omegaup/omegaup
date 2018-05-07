# Descripción

Por favor incluye la descripción de lo que cambiaste. Lo que pongas en esta
sección se va a utilizar como commit message cuando se haga merge.

Si tu cambio toca la interfaz, hay que incluir una captura de pantalla.

Fixes: #(issue)

# Comentarios

Si hay comentarios para los reviewers (por ejemplo, dónde hay que empezar a
revisar, algo a lo que hay que poner atención adicional, etc.), todo eso se
puede poner en esta sección.

También si faltan cosas por hacer, se puede hacer un checklist en esta sección.

Si no se utiliza, esta sección se puede eliminar.

# Checklist:

- [ ] El código sigue la [guía de
      estilo](https://github.com/omegaup/omegaup/wiki/Coding-guidelines) de
      omegaUp.
- [ ] Se corrieron todas las pruebas y pasaron.
- [ ] Si se está agregando funcionalidad nueva, se agregaron pruebas.
- [ ] Si el cambio es grande (> 200 líneas), hay que intentar partirlo en
      varios pull requests. De preferencia uno para los controladores + phpunit
      y luego otro para la interfaz.
