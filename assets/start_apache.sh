#!/bin/sh
echo PMWIKI_URL ist $PMWIKI_URL

# index_file=/home/web/hosts/peter.schoenbucher.ch/htdocs/index.php
# echo "<?php include_once('/home/web/shared_wiki/pmwiki.php');" > $index_file
# echo "<?php include_once('/home/web/shared_wiki/pmwiki.php');" > /home/web/hosts/peter.schoenbucher.ch/htdocs/pmwiki/index.php
# chown www-data:www-data $index_file
# chmod +x $index_file
apache2-foreground
