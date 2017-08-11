# Welches Docker-Image?

Kriterien: 

* PHP5 support (on wheezy we have 5.4.45-0+deb7u9 installed. Fand in /backup/weekly.1/localhost/home/web/hosts/peter.schoenbucher.ch/logs/ keine Deprecated gefunden,
    das gibt es erst seit http://php.net/manual/en/migration55.deprecated.php)
* NGINX

Gefunden

## https://hub.docker.com/r/ishakuta/docker-nginx-php5/

* Vorbereitungen

Alle alten Docker-Pakete gelöscht und gepurged. Ebenfalls `rm -rf /var/lib/docker`. sbu und niklaus dere Gruppe Docker hinzugefügt

Danach vorgegangen gemäss https://docs.docker.com/engine/installation/linux/docker-ce/debian/

    cd /home/web/hosts/docker.schoenbucher.ch    
    docker build -t ngiger/peter-wiki .
    docker run --detach --rm --name peter -p 6542:80 ishakuta/docker-nginx-php5
    # Danach konnte ich http://localhost:6542 das PHP-Info sehen (PHP Version 5.5.9-1ubuntu4)
    docker-compose start peter_wiki
    # Danach konnte ich http://localhost:6543/phpinfo.php das PHP-Info sehen (PHP Version 5.5.9-1ubuntu4)
    # http://localhost:6543/ zeigte das favicon an, jedoch nicht alles
    docker-compose exec peter_wiki /bin/bash # zum inspizieren 
    # dort tail -f /var/log/nginx/*log /var/log/php5-fpm.log aufgerufen
    #  include_once(/home/web/pmwiki-2.2.70/pmwiki.php) failed!!!
    # jetzt motzt er wegen edittoolbar.php
    # ist in /backup/daily.5/localhost/home/web/shared_wiki/cookbook/edittoolbar/edittoolbar.php

    sudo find /backup/daily.5/localhost/home/web  -type f -name "*php" -exec grep -l edittoolbar  "{}" \;
    /backup/weekly.3/localhost/home/web/shared_wiki/cookbook/edittoolbar/edittoolbar.php
    sudo find /home/web/ -type f -name "*php" -exec grep -l farmconfig  "{}" \;
    /home/web/pmwiki-2.2.102/pmwiki.php
    /home/web/inaktiv/BACKUP_iatrix.org+havluagglo+www.praxisunion.ch/www.praxisunion.ch/public_html/pmwiki.php
    /home/web/inaktiv/BACKUP_iatrix.org+havluagglo+www.praxisunion.ch/iatrix/pmwiki.php
    /home/web/inaktiv/lumed.schoenbucher.chxxx/htdocs/pmwiki.php
    /home/web/inaktiv/praxis.schoenbucher.ch/htdocs/wk/pmwiki.php
    /home/web/inaktiv/iatrix.org/pmwiki.php
    /home/web/inaktiv/kine-cranio.ch/htdocs/pmwiki.php
    /home/web/inaktiv/iatrix.org+havluagglo.ch/iatrix/pmwiki.php
    /home/web/pmwiki-2.2.70.old/pmwiki.php
    /home/web/hosts/praxis.praxisunion.ch/htdocs/wk_old/pmwiki.php
    /home/web/hosts/www.praxisunion.ch/mpwk/pmwiki-2.2.57/pmwiki.php
    /home/web/hosts/www.praxisunion.ch/pmwiki.php
    /home/web/hosts/peter.schoenbucher.ch/htdocs/lumed/pmwiki.php
    /home/web/hosts/docker.schoenbucher.ch/htdocs/lumed/pmwiki.php
    
Mit Docker-Compose

    docker-compose create --build peter_wiki
    docker-compose start peter_wiki

## NIGINX /etc/init/php5-fpm.conf

Vergebliche Versuche mein Modul zum Laufen zu bringen

    php5-fpm - The PHP FastCGI Process Manager

    description "The PHP FastCGI Process Manager"
    author "Ondřej Surý <ondrej@debian.org>"

    start on runlevel [2345]
    stop on runlevel [016]

    reload signal USR2

    pre-start exec /usr/lib/php5/php5-fpm-checkconf

    respawn
    exec /usr/sbin/php5-fpm --nodaemonize --fpm-config /etc/php5/fpm/php-fpm.conf

## Aufsetzen des  /home/web/hosts/docker.schoenbucher.ch/

sudo rsync -av /backup/daily.5/localhost/home/web/hosts/peter.schoenbucher.ch/htdocs/ /home/web/hosts/docker.schoenbucher.ch/htdocs/
sudo rsync -av /backup/daily.5/localhost/home/web/pmwiki-2.2.70/ /home/web/hosts/docker.schoenbucher.ch/pmwiki-2.2.70/
sudo rsync -av /backup/daily.5/localhost/home/web/shared_wiki/ /home/web/hosts/docker.schoenbucher.ch/shared_wiki/

      - /backup/daily.5/localhost/home/web/pmwiki-2.2.70:/home/web/pmwiki-2.2.70
      - /backup/daily.5/localhost/home/web/shared_wiki:/home/web/shared_wiki
      - /backup/weekly.3/localhost/home/web/shared_wiki/cookbook:/home/web/shared_wiki/cookbook/
      - /backup/weekly.3/localhost/home/web/shared_wiki/pub:/home/web/shared_wiki/pub/

Danach konnte ich eine Unterseite wie http://prxserver:6543/cams/index.html ansehen. nicht jedoch via 
http://prxserver:6543/pmwiki/index.php/Public/Cams?from=Main.HomePage
