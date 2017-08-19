# https://github.com/docker-library/php/blob/ddc7084c8a78ea12f0cfdceff7d03c5a530b787e/5.6/zts/alpine/Dockerfile
# https://github.com/docker-library/php/blob/master/5.6/Dockerfile
FROM php:7.0-apache

MAINTAINER niklaus.giger@member.fsf.org
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update 
RUN apt-get install -y wget

EXPOSE 80
RUN mkdir /home/web 
RUN chown -R www-data:www-data  /home/web
RUN wget http://www.pmwiki.org/pub/pmwiki/pmwiki-2.2.102.tgz
RUN tar -C  /home/web -xzvf pmwiki-2.2.102.tgz
  
# RUN ln -sf /dev/stdout /var/log/apache2/access.log \
# && ln -sf /dev/stderr /var/log/apache2/error.log

RUN rm -f /var/log/apache2/error.log /var/log/apache2/access.log
ADD assets/peter.schoenbucher.ch.conf /etc/apache2/sites-enabled/peter.conf

CMD whoami && /etc/init.d/apache2 start && sleep 1000000

