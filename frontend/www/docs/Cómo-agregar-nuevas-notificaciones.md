El componente `Notification.vue` debe ser capaz de mostrar todas las notificaciones que un usuario tenga pendientes.
Para ello, estas notificaciones deben primero ser cargadas en la tabla `Notifications` en la base de datos. El campo `contents` de cada elemento de dicha tabla, es un JSON, que debe tener la siguiente estructura:

```
{
  type: elTipoDeNotificacion,
  any_field: valueOfAnyField,
}
```

El campo `type` servirá para indicar al componente `Notification.vue`, el formato que debe tener la notificación (con imagen, sin imagen, la imagen a la derecha, sin fecha, etc). Es importante saber que este formato solo funcionará si los estilos adecuados son creados/ajustados en el componente en vue mencionado.

El campo `any_field` puede tener el nombre que se quiera, funciona como un "payload", lleva la información de relevancia para que la notificación sea debidamente desplegada.

Por ejemplo, en: https://github.com/omegaup/omegaup/blob/master/stuff/cron/assign_badges.py#L66
```
{
  'type': 'badge',
  'badge': 500score
}
```
La notificación indica que es del tipo badge y el campo `badge` funciona como un payload para indicar se trata del badge `500score`.

Desde el componente `Notifications.vue`, esto es soportado a través de: https://github.com/omegaup/omegaup/blob/master/frontend/www/js/omegaup/components/notification/Notification.vue#L58, y los diversos estilos que son aplicados.

Si existen más dudas, es mejor publicarlas en el canal de #depto_tecnico en nuestro Slack :)