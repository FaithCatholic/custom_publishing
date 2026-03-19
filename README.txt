---SUMMARY---

Module aims to automatically unpublish anonymously submitted nodes, as well as customizable
email notifications to a specified email address with details about the submitted content.

---INSTALLATION---

Install as usual at /admin/extend. Grant the 'administer site configuration' permission to the desired
roles at /admin/user/permissions to access the module settings config form.

---USAGE---

1. Enable the module to automatically unpublish anonymously submitted nodes. No configuration is requried.

2. To enable notifications, navigate to the module config form at /admin/config/content/custom-publishing.
and check the 'Enable email notifcations' checkbox to enable email notifications.

3. Optionally provide the notification address (the sitewide email is used if none is provided), and an
email subject and body message with tokens. (A default body/subject is used if notifications are enabled but
the subject and body is not specified in the configuration form.)
