The `Notification.vue` component must be able to display all pending notifications for a user.
To achieve this, these notifications must first be loaded into the `Notifications` table in the database. The `contents` field of each element in this table is a JSON that must have the following structure:

```
{
  type: notificationType,
  any_field: valueOfAnyField,
}
```

The `type` field will tell the `Notification.vue` component what format the notification should have (with image, without image, image on the right, without date, etc). It's important to know that this format will only work if the appropriate styles are created/adjusted in the mentioned Vue component.

The `any_field` can have any name you want, it works as a "payload", carrying the relevant information for the notification to be properly displayed.

For example, in: https://github.com/omegaup/omegaup/blob/master/stuff/cron/assign_badges.py#L66
```
{
  'type': 'badge',
  'badge': 500score
}
```
The notification indicates that it is of type badge and the `badge` field works as a payload to indicate it's for the `500score` badge.

From the `Notifications.vue` component, this is supported through: https://github.com/omegaup/omegaup/blob/master/frontend/www/js/omegaup/components/notification/Notification.vue#L58, and the various styles that are applied.

If you have any questions, it's better to post them in the #depto_tecnico channel on our Slack :)