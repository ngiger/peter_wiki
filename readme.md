# Peters Wiki

Die einzelne Schritte bei der Installation sind dokumentiert im [Worklog](work_log.md)

Hier werdeb die gefundene Lösung und die dazu notwendigen Entscheide dokumentiert.

# Installation

* git clone https://github.com/ngiger/peter_wiki.git /opt/src/peter_wiki
* cd /opt/src/peter_wiki
* ./get_pmwiki_files # Im Moment 102 + gebraucht cookbooks
* Alle Verzeichniss /home/web/hosts/<xy>/htdocs müsse vorhanden sein
* docker-compos start iatrix peter # Stand 2.9.2017

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

Ziele:
* $ScriptUrl und $PubDirUrl sollen den Server-Namen enthalten und keine absoluten Namen wie 'https://praxis.praxisunion.ch/pub';
    $ScriptUrl = 'http://'.$_SERVER['HTTP_HOST'].'/pmwiki/pmwiki.php';
    $PubDirUrl = 'http://'.$_SERVER['HTTP_HOST'].'/pmwiki/pub';
* skins und config.php werden hier local verwaltet, z.B.
** org.iatrix/local/config.php
** org.iatrix/skins/xxx
* apache.conf für den Hosts werden ebenfalls hier verwaltet, z.B.
** org.iatrix/apache/org.iatrix.conf
* Innerhalb der Dockers wird immer dieselbe Apache.conf vewendet und diese wird hier unter ./apache2/common.conf verwaltet

Damit sollten dann Unterschiede zwischen den verschiedenen Wiki-Seiten einfach gefunden und verstanden werden können.

Das Umstellen von HTTP auf HTTPS wird im Host-Apache /etc/apache2/sites-available/000-default.conf gemacht und sollte etwa wie folgt aussehen:

    <VirtualHost *:80>
      Redirect permanent / https://%{SERVER_NAME}
      ServerAdmin webmaster@localhost
      DocumentRoot /var/www/html
      ErrorLog ${APACHE_LOG_DIR}/error.log
      CustomLog ${APACHE_LOG_DIR}/access.log combined
    </VirtualHost>

Die Docker loggen entweder nichts, via syslog oder in ein json file. Das ist anders als heute!!! Könnte aber eventuell durch volumes,
welche /var/log/apache/access_log  in ein Host-File umleiten gelöst werden. Dafür sieht man dann via docker-compose logs nix mehr.

### Probleme

* In /peter.schoenbucher.ch/local/config.php wurde der Markup for google-search ausgeblendet, da er mit neueren Versionen von PHP nicht kompatibel sei.

### Pendenzen

* Backup auf Hetzner ?
* Backup von Hetzner auf externe Harddisk ?
* Log-Dateien von Apache2 archivieren und auswerten?

### Iatrix.org

/home/web/hosts/www.iatrix.org/htdocs von prxserver kopiert (an den gleichen Ort und auf www-data geändert).
index.php und pmwiki.php auf `<?php include_once('/home/web/shared_wiki/pmwiki.php');` geändert.
In local/config.php überall `https://www.iatrix.org` nach `$HOST_NAME` (falls notwendig `"` anstelle von `'` gebraucht) ersetzt. Z.B `"$ScriptUrl = "$HOST_NAME/pmwiki.php";`. Damit läuft das wiki auch, wenn man http: anstelle von https: oder localhost:62080 gebraucht. Die Umsetzung von http -> https erfolgt im apache config!
