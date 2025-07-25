The `Notification.vue` component must be able to display all pending notifications for a user.
To achieve this, these notifications must first be loaded into the `Notifications` table in the database. The `contents` field of each element in this table is a JSON that must have the following structure:

```
{
  type: notificationType,
  any_field: valueOfAnyField,
}
```

The `type` field will tell the `Notification.vue` component what format the notification should have. Currently supported notification types are:

- `badge`: Used for badge achievements, requires a `badge` field with the badge name
- `demotion`: Used for user status changes (e.g. banned status), requires a `status` and `message` field
- `general_notification`: Used for general purpose notifications, requires a `message` and optional `url` field 
- System notifications using the `body` field structure:
  ```json
  {
    "type": "notification-type",
    "body": {
      "localizationString": "translationKey",
      "localizationParams": {
        "param1": "value1",
        "param2": "value2"
      },
      "url": "/path/to/resource",
      "iconUrl": "/media/icon.png"
    }
  }
  ```

Each notification type may require different payload fields and will render with specific styles in the [`Notification.vue`](frontend/www/js/omegaup/components/notification/Notification.vue) component.

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

If you have any questions, it's better to post them in the #ingenieria channel on our Slack :)