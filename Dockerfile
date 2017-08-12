# https://github.com/docker-library/php/blob/ddc7084c8a78ea12f0cfdceff7d03c5a530b787e/5.6/zts/alpine/Dockerfile
# https://github.com/docker-library/php/blob/master/5.6/Dockerfile
FROM debian:wheezy

MAINTAINER niklaus.giger@member.fsf.org
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y \
  php5 \
  php5-cli \
  php5-recode \
  apache2-utils \
  apache2-mpm-prefork \
  apache2

RUN mkdir -p /home/web/pmwiki-2.2.70/
EXPOSE 80
ADD assets/peter.schoenbucher.ch.conf /etc/apache2/sites-available/default

#ADD assets/peter.schoenbucher.ch.site /etc/nginx/sites-available/default

# forward request and error logs to docker log collector (nginx)
# RUN ln -sf /dev/stdout /var/log/nginx/access.log \
# 	&& ln -sf /dev/stdout /var/log/php5-fpm.log \
# 	&& ln -sf /dev/stderr /var/log/nginx/error.log

# RUN ln -sf /dev/stdout /var/log/apache2/access.log \
#  	&& ln -sf /dev/stderr /var/log/apache2/error.log \
#    	&& chown www-data  /var/log/apache2/access.log  /var/log/apache2/error.log 

CMD ln -sf /dev/stdout /var/log/apache2/access.log \
	&& ln -sf /dev/stderr /var/log/apache2/error.log

ADD htpasswd /etc/apache2/htpasswd
# CMD service php5-fpm start && nginx
CMD whoami && /etc/init.d/apache2 start && sleep 1000000

# docker build -t ngiger/peter-wiki . 
# docker run  --rm --name peter --detach ngiger/peter-wiki
# docker run --rm --name peter -p 6542:80 ngiger/peter-wiki 
# docker stop peter
# chromium http://localhost:6542

# nginx uses as default /usr/share/nginx/html as defined in /etc/nginx/conf.d/default.conf
# found more files via
# /etc/init/php5-fpm.conf
# docker run --rm -ti --name peter -p 6542:80 ishakuta/docker-nginx-php5 /bin/bash
