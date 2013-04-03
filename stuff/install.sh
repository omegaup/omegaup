echo "OmegaUp Installation script v0"

apt-get update -qq

#check apache or ngnix
apt-get install -qq -y --force-yes nginx
/etc/init.d/nginx start

#check ngnix configuration
echo -e "server { \n\
			listen   8081; ## listen for ipv4  \n\
			listen   [::]:8081 default ipv6only=on; ## ipv6\n\
			server_name  localhost;
			access_log  /var/log/nginx/localhost.access.log;\n\
			location / {\n\
				root    /opt/omegaup/omegaup/frontend/www/;\n\
				index  index.html index.htm index.php;\n\
			}\n\
\n\
			location /doc {\n\
				root   /usr/share;\n\
				autoindex on;\n\
				allow 127.0.0.1;\n\
				deny all;\n\
			}\n\
\n\
			location /images {\n\
				root   /usr/share;\n\
				autoindex on;\n\
			}\n\
\n\
			# pass PHP scripts to FastCGI server on 127.0.0.1:9000\n\
			location ~ \.php$ {\n\
				fastcgi_pass   127.0.0.1:9000;\n\
				fastcgi_index  index.php;\n\
				fastcgi_param  SCRIPT_FILENAME  /opt/omegaup/omegaup/frontend/www/$fastcgi_script_name;\n\
				include	       fastcgi_params;\n\
			}\n\
		}\n" > omegaup_dev

cp omegaup_dev /etc/ngnix/sites_available

#check mysql
apt-get install -qq -y --force-yes mysql-server mysql-client

#check php
apt-get install -qq -y --force-yes php5-cgi php5-cli php5-mysql php-pear php5-mcrypt 

#check git
apt-get install -qq -y --force-yes git

#check repo
#rm -rf omegaup2
#mkdir omegaup2
cd omegaup2
# git clone https://github.com/omegaup/omegaup.git 

cd omegaup/frontend/server/
cp config.php.sample config.php
cat config.php | grep -v OMEGAUP_DB_ > config.pre1




#chek php config.ini, set values for development

#check writable folders

#check and write config

#check db connection

#install user and db

#update config.php

#test curl

#test index with curl

#look for phpunit
