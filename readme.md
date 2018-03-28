# Peters Wiki

Die einzelne Schritte bei der Installation sind dokumentiert im [Worklog](work_log.md)

Hier werdeb die gefundene Lösung und die dazu notwendigen Entscheide dokumentiert.

## Zustand vor 28.3.2018

* DNS-Server läuft auf dns4.pro (Benutzername schoebu)
* Praxis Internes Wiki, erreichbar unter praxis.praxisunion.ch läuft auf prxserver
* http://www.schoenbucher.ch und http://www.praxisunion.ch laufen bei mhs (213.188.35.154)
* https://testwww.schoenbucher.ch und https://test.www.praxisunion.ch laufen auf hetzner(94.130.75.222)

# Installation

* git clone https://github.com/ngiger/peter_wiki.git /opt/src/peter_wiki
* cd /opt/src/peter_wiki
* ./get_pmwiki_files # Im Moment 102 + gebraucht cookbooks
* Alle Verzeichniss /home/web/hosts/<xy>/htdocs müsse vorhanden sein
* docker-compose create --build
* docker-compose start iatrix peter # Stand 2.9.2017

# Entscheidungen

## Welches Docker-Image?

Kriterien: 

* PHP5 support (on wheezy we have 5.4.45-0+deb7u9 installed. Fand in /backup/weekly.1/localhost/home/web/hosts/peter.schoenbucher.ch/logs/ keine Deprecated gefunden,
    das gibt es erst seit http://php.net/manual/en/migration55.deprecated.php)
* NGINX

Gefunden https://hub.docker.com/r/ishakuta/docker-nginx-php5/

### Reorganisiert

Ziele waren:
* /home/web/hosts/peter.schoenbucher.ch/htdocs zu gebrauchen
* pmwiki via Script zu installieren
* PM-Wiki installation in git aufgenommen
* /home/web/hosts als git repository initalisiert. Dazu auf Hetzner folgende Kommandos ausgeführt
    cd /home/web/hosts/
    git init .
    git add *
    git commit -m "Erster import"
* $ScriptUrl und $PubDirUrl sollen den Server-Namen enthalten und keine absoluten Namen wie 'https://praxis.praxisunion.ch/pub';
    $ScriptUrl = 'http://'.$_SERVER['HTTP_HOST'].'/pmwiki/pmwiki.php';
    $PubDirUrl = 'http://'.$_SERVER['HTTP_HOST'].'/pmwiki/pub';
* skins und config.php werden hier local verwaltet, z.B.
** org.iatrix/local/config.php
** peter.schoenbucher.ch/skins/peter
** praxis.praxisunion.ch/skins/sub
* apache2/rewrite_wikis.conf (für den Host)
* apache2/common.conf (für gleiche alle Docker-Instanzen)

Damit sollten dann Unterschiede zwischen den verschiedenen Wiki-Seiten einfach gefunden und verstanden werden können.

#### Damit HTTPS auf Hetzner richtig läuft brauchte es folgende Anpassungen

Das Umstellen von HTTP auf HTTPS wird im Host-Apache /etc/apache2/sites-available/000-default.conf gemacht und sollte etwa wie folgt aussehen:

    <VirtualHost *:80>
      Redirect permanent / https://%{SERVER_NAME}
      ServerAdmin niklaus.giger@hispeed.ch
      DocumentRoot /var/www/html
      ErrorLog ${APACHE_LOG_DIR}/error.log
      CustomLog ${APACHE_LOG_DIR}/access.log combined
      RewriteEngine On
      RewriteCond %{HTTPS} !=on
      RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R,L]
    </VirtualHost>

Ich habe überall eine E-Mailadresse von mir als WebMaster genommen    
Die Docker loggen entweder nichts, via syslog oder in ein json file. Das ist anders als heute!!! Könnte aber eventuell durch volumes,
welche /var/log/apache/access_log  in ein Host-File umleiten gelöst werden. Dafür sieht man dann via docker-compose logs nix mehr.

Damit auf Hetzner alles richtig läuft braucht es dort den Eintrag `RequestHeader set X-Forwarded-Proto "https"` in der VirtualHost-Section des Apache-Conf. Weiter muss die (im internen Netz von Hetzner gebrauchte IP-Adresse von eth0, im Moment 172.31.1.100) dort wie folgt für HTTPS gebraucht werden
    <VirtualHost 172.31.1.100:443>
      ServerName peter.schoenbucher.ch

Diese Datei wird ebenfalls hier verwaltet und wurde wie folgt aktiviert
    cp -pvu apache2/rewrite_wikis.conf /etc/apache2/sites-available/rewrite_wikis.conf
    systemctl restart apache2; systemctl status apache2
    
Dann braucht im local/config.php etwa folgende Zeilen
    $FarmPubD = '/home/web/shared_wiki/';
    $FarmD    = '/home/web/shared_wiki/';
    if ( $_SERVER['HTTP_X_FORWARDED_HOST'] ) {
      $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
      $PubDirUrl = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].'/pub';
      $ScriptUrl = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].'/pmwiki.php';

    } else {
      $FarmPubDirUrl = $_SERVER['HTTP_HOST'].'/pub';
    }

### Probleme/Pendenzen

* In /peter.schoenbucher.ch/local/config.php wurde der Markup for google-search ausgeblendet, da er mit neueren Versionen von PHP nicht kompatibel sei.

* Die Dateien unter  /home/web/hosts/www.schoenbucher.ch/public_html sollten bei Gelegenheit von Peter an eine korrekteren Ort verschoben werden

### Troubleshooting

* http://peter.schoenbucher.ch:60080/local/phpinfo.php
* Can you access https://peter.schoenbucher.ch/local/phpinfo.php? If yes is everything fine?
* http://peter.schoenbucher.ch:60080/

### Automatisches Aufstarten nach einem Reboot

Das ist anscheinend Trickreich. Auf der Kommandozeile geht `cd /opt/src/peter_wiki; docker-compose up -d` ohne Probleme.
Aber das in systemd zu verpacken, das starten und stoppen kann ist etwas trickreich.

Deshalb eine einfache Lösung, welche ihn nur aufstartet gefunden und in assets/peterwiki.service

### Backup

* Siehe helpers/*. Diese Dateien sollten wie folgt aktiviert werden

    cp -pvu helpers/rsnapshot.conf.hetzner helpers/rsync.exclude /etc
    cp -pvu helpers/*daily /etc/cron.daily
    cp -pvu helpers/*monthly /etc/cron.monthly
    cp -pvu helpers/letsencrypt_renew /etc/cron.monthly

** Es werden mit Hilfe von Rsnapshot tägliche (30) und maximal 200 monatliche Backups von /etc/ und /home/web/hosts unter /opt/backup angelegt
** Täglich gibt es rsync von /etc und /home/web/hosts/.git -> praxiserver -> /backup/hetzner/

# Ideen

* Log-Dateien von Apache2 archivieren und auswerten?

# Gemachte Anpassungen

Wenn `include_once("$FarmD/cookbook/edittoolbar/edittoolbar.php");` und `$EnableGUIButtons = 1;` vorkommen, werden die Buttons nicht angezeigt. Deshalb in allen config.php auf EnableGUIButtons auf 1 gesetzt.
