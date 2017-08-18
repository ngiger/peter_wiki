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


## Log des Aufsetzen von Peters Server unter 94.130.75.222 (Hetzner)

* Unter https://dns4.pro/ folgende Namen auf 94.130.75.222 weiterleiten: test_www.schoenbucher.ch test_peter.schoenbucher.ch test.iatrix.org test_www.iatrix.org* Peter gab mir root Zugang via /root/.ssh/authorized_keys
* Pakete installiert via

    apt-get update
    apt-get install etckeeper git vim-nox htop iotop fish docker-compose docker.io letsencrypt
    git config --global user.email 'niklaus.giger@member.fsf.org'
    git config --global user.name "Niklaus Giger"
    useradd --create-home niklaus
    mkdir /home/niklaus/.ssh
    mkdir /opt/src
    cp /root/.ssh/authorized_keys /home/niklaus/.ssh
    chown -R niklaus:niklaus /home/niklaus/.ssh /opt/src
    # Jetzt kann Niklaus via ssh einloggen in sein unprivilegiertes Konto
    adduser sbu docker
    adduser niklaus docker
    # Zuerst versuchte ich Namen mit vorgestelltem 'test_' zu erzeugen. Dies hatte letsencrypt nicht gerne (dn4pro hatte keine Propbleme damit. Deshalb auf test als Prefix umgeschaltet
    letsencrypt certonly # niklaus.giger@hispeed.ch, Folgendd Domainnamen eingegeben testwww.schoenbucher.ch testpeter.schoenbucher.ch test.iatrix.org testwww.iatrix.org
    # Congratulations! Your certificate and chain have been saved at /etc/letsencrypt/live/testwww.schoenbucher.ch/fullchain.pem.  Your cert will expire on 2017-11-16. 


* Als Benutzer niklaus folgendes gemacht

    git config --global user.email 'niklaus.giger@member.fsf.org'
    git config --global user.name "Niklaus Giger"
    ssh-keygen
    ssh-copy-id -p 4444 praxis.praxisunion.ch
    cd /opt/src
    git clone https://github.com/ngiger/peter_wiki.git /opt/src/peter-wiki-docker
    cd /opt/src/peter-wiki-docker
    scp -r -P 4444 praxis.schoenbucher.ch:/home/web/shared_wiki .
    mkdir htdocs
    scp -r -P 4444 praxis.schoenbucher.ch:/home/web/hosts/peter.schoenbucher.ch/htdocs/cams htdocs
    scp -r -P 4444 praxis.schoenbucher.ch:/home/web/hosts/peter.schoenbucher.ch/htdocs/pwp htdocs
    scp -r -P 4444 praxis.schoenbucher.ch:/home/web/hosts/peter.schoenbucher.ch/htdocs/pix htdocs
    scp -r -P 4444 praxis.schoenbucher.ch:/home/web/hosts/peter.schoenbucher.ch/htdocs/lumed htdocs

    scp -r -P 4444 praxis.schoenbucher.ch:/home/web/hosts/peter.schoenbucher.ch/htdocs/pmwiki_old/pmwiki-groups htdocs
    scp -r -P 4444 praxis.schoenbucher.ch:/home/web/hosts/peter.schoenbucher.ch/htdocs/pmwiki_old/uploads htdocs
    scp -r -P 4444 praxis.schoenbucher.ch:/home/web/hosts/peter.schoenbucher.ch/htdocs/pmwiki_old/wiki.d htdocs
      # scp -r -P 4444 praxis.schoenbucher.ch:/home/web/hosts/peter.schoenbucher.ch/htdocs/local htdocs
    cd /opt/src/peter-wiki-docker
    docker-compose up --build peter_wiki
    http://94.130.75.222:6543 # Probleme da auf https umgeleitet und Zertifikat Fehler
    # letsencrpy certonly geholt. Danach zeigte es die htdocs ohne 
    # Zur Fehlersuche
    docker-compose exec peter_wiki /bin/bash # dort drin tail -f /var/log/apache2/*log
    wget http://www.pmwiki.org/pub/pmwiki/pmwiki-2.2.70.tgz
    tar -zxvf pmwiki-2.2.70.tgz
    sudo chown -R www-data:www-data pmwiki-2.2.70/
    sudo chgrp -R www-data htdocs/wiki.d/ # Jetzt kommt

Diverse kleine Änderungen gemacht (skin-Dateien, Dockerfile, docker-compose). Details gemäss git log
