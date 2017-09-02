FROM php:7.0-apache

MAINTAINER niklaus.giger@member.fsf.org
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y wget unzip && apt-get clean && rm -rf /var/cache/apt
RUN mkdir /home/web  && \
    wget http://www.pmwiki.org/pub/pmwiki/pmwiki-2.2.102.tgz  && \
    tar -C  /home/web -xzvf pmwiki-2.2.102.tgz && \
    chown -R www-data:www-data  /home/web
WORKDIR  /home/web/host/htdocs
# ADD assets/peter.schoenbucher.ch.conf /etc/apache2/sites-available/000-default.conf
# ADD assets/www.iatrix.org.conf /etc/apache2/sites-enabled/www.iatrix.org.conf # does not work
# ADD assets/www.iatrix.org.conf /etc/apache2/sites-available/000-default.conf
# ADD assets/peter.conf /etc/apache2/sites-enabled/peter.conf
# ADD assets/start_apache.sh /root/start_apache.sh
# CMD /root/start_apache.sh
# CMD a2enmod ssl # && service apache2 restart
