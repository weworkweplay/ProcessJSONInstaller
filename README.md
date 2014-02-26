# WWWP Installer

```
 _    _        _    _            _      _    _       ______ _
| |  | |      | |  | |          | |    | |  | |      | ___ \ |
| |  | | ___  | |  | | ___  _ __| | __ | |  | | ___  | |_/ / | __ _ _   _
| |/\| |/ _ \ | |/\| |/ _ \| '__| |/ / | |/\| |/ _ \ |  __/| |/ _` | | | |
\  /\  /  __/ \  /\  / (_) | |  |   <  \  /\  /  __/ | |   | | (_| | |_| |
 \/  \/ \___|  \/  \/ \___/|_|  |_|\_\  \/  \/ \___| \_|   |_|\__,_|\__, |
                                                                     __/ |
                                                                    |___/
```

Module that can interpret JSON as ProcessWire fields, templates and pages. This is very useful for quick setup tasks.

Written by Pieter ([@pieterbeulque](http://twitter.com/pieterbeulque)) at [We Work We Play](http://weworkweplay.com).

## Modules
The **Config**-installer runs when installing this module, for two reasons:
1) To test if everything has been installed correctly; 2) Because other modules might use it to store some data.

* **Config**: Creates page `/config/`
* **SEO**: meta description field
* **Sharing**: Facebook sharing options
* **Contact**: contact form fields (name, email, message) + recipient field in config + importing `FormTemplateProcessor`
* **Under Construction**: adds an *Is Live*-checkbox to the config. Creates a `page_underconstruction` template with an image field and a background-color field.
