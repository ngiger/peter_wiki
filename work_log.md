# Dokumentation des Aufbaus beim neuen Hetzner Servers

## 11. August 2017: Versuch mit Docker auf prxserver

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

    find /backup/daily.5/localhost/home/web  -type f -name "*php" -exec grep -l edittoolbar  "{}" \;
    /backup/weekly.3/localhost/home/web/shared_wiki/cookbook/edittoolbar/edittoolbar.php
    find /home/web/ -type f -name "*php" -exec grep -l farmconfig  "{}" \;
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

* Versuch mit NGINX /etc/init/php5-fpm.conf

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

### Aufsetzen des  /home/web/hosts/docker.schoenbucher.ch/

rsync -av /backup/daily.5/localhost/home/web/hosts/peter.schoenbucher.ch/htdocs/ /home/web/hosts/docker.schoenbucher.ch/htdocs/
rsync -av /backup/daily.5/localhost/home/web/pmwiki-2.2.70/ /home/web/hosts/docker.schoenbucher.ch/pmwiki-2.2.70/
rsync -av /backup/daily.5/localhost/home/web/shared_wiki/ /home/web/hosts/docker.schoenbucher.ch/shared_wiki/

      - /backup/daily.5/localhost/home/web/pmwiki-2.2.70:/home/web/pmwiki-2.2.70
      - /backup/daily.5/localhost/home/web/shared_wiki:/home/web/shared_wiki
      - /backup/weekly.3/localhost/home/web/shared_wiki/cookbook:/home/web/shared_wiki/cookbook/
      - /backup/weekly.3/localhost/home/web/shared_wiki/pub:/home/web/shared_wiki/pub/

Danach konnte ich eine Unterseite wie http://prxserver:6543/cams/index.html ansehen. nicht jedoch via 
http://prxserver:6543/pmwiki/index.php/Public/Cams?from=Main.HomePage


## 18. August 2017: Log des Aufsetzen von Peters Server unter 94.130.75.222 (Hetzner)

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
    // Jetzt kann Niklaus via ssh einloggen in sein unprivilegiertes Konto
    adduser sbu docker
    adduser niklaus docker
    // Zuerst versuchte ich Namen mit vorgestelltem 'test_' zu erzeugen. Dies hatte letsencrypt nicht gerne (dn4pro hatte keine Propbleme damit. Deshalb auf test als Prefix umgeschaltet
    letsencrypt certonly # niklaus.giger@hispeed.ch, Folgendd Domainnamen eingegeben testwww.schoenbucher.ch testpeter.schoenbucher.ch test.iatrix.org testwww.iatrix.org
    // Congratulations! Your certificate and chain have been saved at /etc/letsencrypt/live/testwww.schoenbucher.ch/fullchain.pem.  Your cert will expire on 2017-11-16. 


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
    // scp -r -P 4444 praxis.schoenbucher.ch:/home/web/hosts/peter.schoenbucher.ch/htdocs/local htdocs
    rsync -av -e 'ssh -P 4444' praxis.schoenbucher.ch:/home/web/hosts/peter.schoenbucher.ch/htdocs/ /home/web/hosts/peter.schoenbucher.ch/htdocs/

    rsync -av -e "ssh -p 4444" praxis.schoenbucher.ch:/home/web/hosts/praxis.praxisunion.ch/htdocs/ /home/web/hosts/praxis.praxisunion.ch/htdocs/
    
    cd /opt/src/peter-wiki-docker
    docker-compose up --build peter_wiki
    http://94.130.75.222:6543 # Probleme da auf https umgeleitet und Zertifikat Fehler
    // letsencrpy certonly geholt. Danach zeigte es die htdocs ohne 
    // Zur Fehlersuche
    docker-compose exec peter_wiki /bin/bash # dort drin tail -f /var/log/apache2/*log
    wget http://www.pmwiki.org/pub/pmwiki/pmwiki-2.2.70.tgz
    tar -zxvf pmwiki-2.2.70.tgz
    chown -R www-data:www-data pmwiki-2.2.70/
    chgrp -R www-data htdocs/wiki.d/ # Jetzt kommt

Diverse kleine Änderungen gemacht (skin-Dateien, Dockerfile, docker-compose). Details gemäss git log

### Diverse Wiki-Seiten nach Docker-Instanzen umleiten

    // assets/peter.schoenbucher.ch.conf um mehr ServerAlias erweitert
    docker-compose up --build peter_wiki
    cp /opt/src/peter-wiki-docker/rewrite_wikis.conf /etc/apache2/sites-available/
    cd /etc/apache2/sites-enabled/
    ln -s ../sites-available/rewrite_wikis.conf .
    a2enmod proxy
    systemctl restart apache2
    // Auf dns4pro peter.schoenbucher auf den Hetzner-Server weiter geleitet
    cp /opt/src/peter-wiki-docker/assets/docker_peter_wiki.service  /etc/systemd/system/
    systemctl daemon-reload 
    systemctl enable docker_peter_wiki
    systemctl start docker_peter_wiki
    systemctl status docker_peter_wiki
    
### HTTPS für nextcloud.schoenbucher.ch aktivieren

* Eintrag in dns4.pro für nextcloud.schoenbucher.ch gemacht
* SSLCertificateKeyFile und SSLCertificateFile in /etc/apache2/sites-available/default-ssl.conf geändert
* nextcloud.schoenbucher.ch in /etc/hosts für IPv4 und IPv6 hinzugefügt
* In /etc/apache2/sites-available/000-default.conf Zeile `ServerName nextcloud.schoenbucher.ch` hinzugefügt

    Commit für diese Aktionen war:
    commit 47372cdc61067c5115a67f1911b53c5bc6611e10
    Author: Niklaus Giger <niklaus.giger@member.fsf.org>
    Date:   Sat Aug 19 19:10:12 2017 +0200
        Added HTTPS für nextcloud.schoenbucher.ch

### 2. September 2017 

rsync -av -e "ssh -p 4444" praxis.schoenbucher.ch:/home/web/hosts/www.praxis.praxisunion.ch/htdocs/ /home/web/hosts/www.praxis.praxisunion.ch/htdocs/

Nochmals alles geholt: auf Hetzner
    rsync -avz -e"ssh -p 4444" sbu@212.101.17.47:/home/web/hosts/peter.schoenbucher.ch/htdocs/ /home/web/hosts/peter.schoenbucher.ch/htdocs/

Danach diverse logische links gelöscht, da ich 
    rm /home/web/hosts/peter.schoenbucher.ch/htdocs/pmwiki/pub
    rm /home/web/hosts/peter.schoenbucher.ch/htdocs/pmwiki/wiki.d
    rm /home/web/hosts/peter.schoenbucher.ch/htdocs/pmwiki/cookbook
    rm /home/web/hosts/peter.schoenbucher.ch/htdocs/pub
    rm /home/web/hosts/peter.schoenbucher.ch/htdocs/pmwiki_old/pub
    rm /home/web/hosts/peter.schoenbucher.ch/htdocs/cookbook
    rm -rf /home/web/hosts/peter.schoenbucher.ch/htdocs/wiki.d
    rm -rf /home/web/hosts/peter.schoenbucher.ch/htdocs/wiki.d.102
    mv /home/web/hosts/peter.schoenbucher.ch/htdocs/pmwiki_old/wiki.d /home/web/hosts/peter.schoenbucher.ch/htdocs/


### 3. September 2017 

    apt-get install rsnapshot
    cd /home/web/hosts && git commit -m ".."
    apt-get install python-letsencrypt-apache
    grep domains /etc/letsencrypt/renewal/testwww.schoenbucher.ch*.conf
    # /etc/letsencrypt/renewal/testwww.schoenbucher.ch.conf:domains = testwww.schoenbucher.ch, testpeter.schoenbucher.ch, test.iatrix.org, testwww.iatrix.org
    cp -pvu helpers/rsnapshot.conf.hetzner helpers/rsync.exclude /etc
    cp -pvu helpers/*daily /etc/cron.daily
    cp -pvu helpers/*monthly /etc/cron.monthly
    cp -pvu helpers/letsencrypt_renew /etc/cron.monthly
    
Auf prxserver zur Vorbereitung des Backups
    mkdir -p /backup/hetzner/hosts
    mkdir -p /backup/hetzner/etc
    chown -R sbu /backup/hetzner

#### 3. September 2017 (Zweiter Teil praxis)

    rsync -avz -e "ssh -p 4444 " sbu@praxis.praxisunion.ch:/home/web/hosts/praxis.praxisunion.ch /home/web/hosts/
    chown -R www-data:www-data /home/web/hosts/praxis.praxisunion.ch/htdocs/
    systemctl stop apache2
    letsencrypt certonly --standalone -d iatrix.ch -d www.iatrix.ch -d www.iatrix.org -d iatrix.org
    letsencrypt certonly --standalone -d test.praxisunion.ch -d test.praxis.praxisunion.ch -d test.www.praxisunion.ch

    systemctl start apache2
     testwww.schoenbucher.ch testpeter.schoenbucher.ch test.iatrix.org testwww.iatrix.org iatrix.ch
    --apache -d iatrix.ch -d www.iatrix.ch
    
Installation von systemd für die Wiki-Dockers


    cp /opt/src/peter_wiki/assets/peterwiki.service  /etc/systemd/system/
    systemctl daemon-reload
    systemctl enable peterwiki
    systemctl start peterwiki
    systemctl status peterwiki

#### 8. September 2017

Alle Dateien aus ftp.schoenbucher.ch via ftp von mhs geholt und unter /home/web/hosts/www.schoenbucher.ch/public_html abgelegt. 

    cd /home/web/hosts/www.schoenbucher.ch
    cp -rpvu public_html/uploads .
    cp -rpvu public_html/wiki.d .
    cp -rpvu public_html/pub .
    cp -rpvu public_html/local .
    cp -rpvu public_html/.htaccess .
    chown -R www-data:www-data uploads wiki.d uploads pub local
    chown www-data:www-data  .. . .htaccess
    mv pub wiki.d/ uploads/ local/ htdocs/

Die restlichen sollten bei Gelegenheit von Peter an eine korrekteren Ort verschoben werden

#### 28. March 2018

Auf Hetzner (via ssh root@peter.schoenbucher.ch)
* Nochmals mit Hilfe von /root/get_mhs_via_ftp alles nach /opt/backup.ftp.schoenbucher.ch geholt
* apt-get install certbot -t xenial-backports

    systemctl stop apache2
    certbot --expand -d iatrix.ch -d iatrix.org -d www.iatrix.ch -d www.iatrix.org -d test.iatrix.org
    certbot --expand -d test.praxis.praxisunion.ch nextcloud.schoenbucher.ch test.praxisunion.ch test.www.praxisunion.ch
    rm -rf /etc/letsencrypt/live/test.iatrix.org*
    rm /etc/letsencrypt/renewal/test.iatrix.org-0001.conf /etc/letsencrypt/renewal/test.iatrix.org.conf
    certbot certonly --cert-name testwww.schoenbucher.ch -d testpeter.schoenbucher.ch -d testwww.schoenbucher.ch -d test.www.schoenbucher.ch  -d nextcloud.schoenbucher.ch --standalone
    systemctl start apache2
    certbot certificates
    Saving debug log to /var/log/letsencrypt/letsencrypt.log

    -------------------------------------------------------------------------------
    Found the following certs:
      Certificate Name: iatrix.ch
        Domains: iatrix.ch iatrix.org test.iatrix.org www.iatrix.ch www.iatrix.org
        Expiry Date: 2018-06-26 09:03:19+00:00 (VALID: 89 days)
        Certificate Path: /etc/letsencrypt/live/iatrix.ch/fullchain.pem
        Private Key Path: /etc/letsencrypt/live/iatrix.ch/privkey.pem
      Certificate Name: test.praxisunion.ch
        Domains: test.praxis.praxisunion.ch nextcloud.schoenbucher.ch test.praxisunion.ch test.www.praxisunion.ch
        Expiry Date: 2018-06-24 21:25:21+00:00 (VALID: 88 days)
        Certificate Path: /etc/letsencrypt/live/test.praxisunion.ch/fullchain.pem
        Private Key Path: /etc/letsencrypt/live/test.praxisunion.ch/privkey.pem
      Certificate Name: testwww.schoenbucher.ch
        Domains: testpeter.schoenbucher.ch test.www.schoenbucher.ch testwww.schoenbucher.ch
        Expiry Date: 2018-06-26 10:39:13+00:00 (VALID: 89 days)
        Certificate Path: /etc/letsencrypt/live/testwww.schoenbucher.ch/fullchain.pem
        Private Key Path: /etc/letsencrypt/live/testwww.schoenbucher.ch/privkey.pem
    -------------------------------------------------------------------------------
* cd /home/web/hosts; cp -rpvu www.schoenbucher.ch/* www.praxisunion.ch/
* Added support for  www.praxisunion.ch

    
