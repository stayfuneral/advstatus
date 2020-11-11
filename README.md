#ADV Status

Плагин смены статуса после добавления комментария к заявке

### Установка

* Клонируйте данный репозиторий в папку с плагинами
* Откройте редактор cron `crontab -e` и добавьте туда строку

```
*/1 * * * *  /usr/bin/php*.* /path/to/glpi/plugins/advstatus/cron.php &>/dev/null
```

`/path/to/glpi` - абсолютный путь к директории с glpi на сервере,

`php*.*` - версия php
* Перезапустите cron командой `service cron reload`