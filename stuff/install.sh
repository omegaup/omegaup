#!/bin/bash
set -e

# Helper functions
show_help() {
	echo "OmegaUp Installation script"
	echo -e "\t$0 -u git_username -m git_email [-p path_para_omegaup]"
	exit 1
}

# Configuration.
OMEGAUP_ROOT=/opt/omegaup
WWW_ROOT=/var/www/omegaup.com
USER=`whoami`
MYSQL_PASSWORD=omegaup
MYSQL_DB_NAME=omegaup
UBUNTU=`uname -a | grep -i ubuntu | wc -l`
WHEEZY=`grep 'Debian GNU/Linux 7' /etc/issue | wc -l`
HOSTNAME=localhost

# Get parameters
while getopts "h:u:m:p:01" optname; do
	case "$optname" in
		"h")
			HOSTNAME=$OPTARG
			;;
		"p")
			OMEGAUP_ROOT=$OPTARG
			;;
		"u")
			GIT_USERNAME=$OPTARG
			;;
		"m")
			GIT_EMAIL=$OPTARG
			;;
		"0")
			SKIP_INSTALL=1
			;;
		"1")
			SKIP_NGINX=1
			;;
		"2")
			SKIP_GRADER=1
			;;
	esac
done

if [ "$GIT_USERNAME" = "" -o "$GIT_EMAIL" = "" ]; then
	show_help
fi

# Install _crucial_ stuff first.
if [ ! -f /usr/bin/curl ]; then
	sudo apt-get install -qq -y curl
fi

if [ ! -f /usr/bin/vim ]; then
	sudo apt-get install -qq -y vim
fi

# Install everything needed.
if [ "$SKIP_INSTALL" != "1" ]; then
	if [ "$UBUNTU" = "1" ]; then
		if [ "`cat /etc/apt/sources.list | grep universe | wc -l `" -eq 0 ]; then
			sed -e "s/http.*/& universe/" /etc/apt/sources.list > sources.list
			sudo mv sources.list /etc/apt/sources.list
		fi
	elif [ "$WHEEZY" != "1" ]; then
		curl -s http://www.dotdeb.org/dotdeb.gpg | sudo apt-key add - > /dev/null
		cat > dotdeb.list << EOF
deb http://packages.dotdeb.org squeeze all
deb-src http://packages.dotdeb.org squeeze all
EOF
		sudo mv dotdeb.list /etc/apt/sources.list.d
	fi
	
	sudo apt-get update -qq -y
	
	sudo apt-get install -qq -y nginx mysql-client php5-fpm php5-cli php5-mysql php-pear php5-mcrypt php5-curl git phpunit g++ fp-compiler unzip openssh-client make zip
	sudo apt-get install -qq -y openjdk-7-jdk || sudo apt-get install -qq -y openjdk-6-jdk
	
	if [ ! -f /usr/sbin/mysqld ]; then
		sudo DEBIAN_FRONTEND=noninteractive apt-get install -q -y mysql-server
		sleep 5
		mysqladmin -u root password $MYSQL_PASSWORD
	fi
	
	sudo apt-get install -qq -y phpunit-selenium || echo 'Selenium unavailable'
	
	# Restart php-fpm so it picks php5-curl and php5-mcrypt.
	sudo /etc/init.d/php5-fpm restart
fi

# Install SBT.
if [ ! -f /usr/bin/sbt ]; then
	sudo wget -q http://repo.typesafe.com/typesafe/ivy-releases/org.scala-sbt/sbt-launch//0.12.3/sbt-launch.jar -O /usr/bin/sbt-launch.jar
	cat > sbt << EOF
#!/bin/sh
java -Xms512M -Xmx1536M -Xss1M -XX:+CMSClassUnloadingEnabled -XX:MaxPermSize=384M -jar \`dirname \$0\`/sbt-launch.jar "\$@"
EOF
	sudo mv sbt /usr/bin/
	sudo chmod +x /usr/bin/sbt
fi

# Add ngnix configuration.
if [ "$SKIP_NGINX" != "1" ]; then
	FPM_PORT=`grep '^listen\b' /etc/php5/fpm/pool.d/www.conf 2>/dev/null | sed -e 's/.*=\s*//'`
	if [ "$FPM_PORT" = "" ]; then
		FPM_PORT=127.0.0.1:9000
	fi
	if [ "`echo $FPM_PORT | grep '/' | wc -l `" != "0" ]; then
		FPM_PORT=unix:$FPM_PORT
	fi
	cat > default.conf << EOF
server {
listen       80;
server_name  .$HOSTNAME;

location / {
    root   /var/www/omegaup.com;
    index  index.php index.html;
}

# redirect server error pages to the static page /50x.html
#
error_page   500 502 503 504  /50x.html;
location = /50x.html {
    root   html;
}

location /api/ {
    rewrite ^/api/(.*)$ /api/ApiEntryPoint.php last;
}

# rewrites.
rewrite ^/contest/(.*)$ /contest.php?alias=\$1 last;
rewrite ^/arena/?$ /arena/index.php last;
rewrite ^/arena/[a-zA-Z0-9_+-]+/?$ /arena/contest.php last;
rewrite ^/arena/[a-zA-Z0-9_+-]+/scoreboard/?$ /arena/scoreboard.php last;
rewrite ^/arena/[a-zA-Z0-9_+-]+/admin/?$ /arena/admin.php last;
rewrite ^/arena/[a-zA-Z0-9_+-]+/practice/?$ /arena/practice.php last;

# pass the PHP scripts to FastCGI server listening on $FPM_PORT.
location ~ \.php$ {
    root           /var/www/omegaup.com;
    fastcgi_pass   $FPM_PORT;
    fastcgi_index  index.php;
    fastcgi_param  SCRIPT_FILENAME  /var/www/omegaup.com\$fastcgi_script_name;
    include        fastcgi_params;
}

# deny access to .htaccess files, if Apache's document root
# concurs with nginx's one
location ~ /\.ht {
    deny  all;
}
}
EOF
	sudo mv default.conf /etc/nginx/conf.d/
	
	if [ -f /etc/nginx/sites-enabled/default ]; then
		sudo mv /etc/nginx/sites-enabled/default /etc/nginx/sites-disabled
	fi
	
	sudo /etc/init.d/nginx restart
fi

# Set up ssh/git.
if [ ! -f ~/.ssh/github.com ]; then
	if [ ! -d ~/.ssh ]; then
		mkdir ~/.ssh
	fi
	cat >> ~/.ssh/config << EOF
Host github.com
IdentityFile /home/$USER/.ssh/github.com
User git
EOF
	git config --global user.name "$GIT_USERNAME"
	git config --global user.email "$GIT_EMAIL"
	git config --global credential.helper cache
	git config --global credential.helper 'cache --timeout=3600'
	ssh-keygen -t rsa -C "$GIT_EMAIL" -f ~/.ssh/github.com -N "" > /dev/null
	echo -e "Go to https://github.com/settings/ssh, click on \"Add SSH Key\" and enter:\n"
	cat ~/.ssh/github.com.pub
	echo -e "\n"
	read -p "Press Enter to continue" line
fi

# Clone repository.
if [ ! -d $OMEGAUP_ROOT ]; then
	sudo mkdir $OMEGAUP_ROOT
	sudo chown $USER -R $OMEGAUP_ROOT
	git clone https://github.com/omegaup/omegaup.git $OMEGAUP_ROOT

	# Generate the certificates required.
	cd $OMEGAUP_ROOT
	bin/gencerts.sh

	# Build the sandbox
	cd $OMEGAUP_ROOT/sandbox
	make

	if [ "$SKIP_GRADER" != "1" ]; then 
		# Build common
		cd $OMEGAUP_ROOT/common
		sbt package

		# Build runner
		cd $OMEGAUP_ROOT/runner
		sbt proguard

		# Build grader
		cd $OMEGAUP_ROOT/grader
		sbt proguard
	fi
fi

# Set up the www root.
if [ ! -d $WWW_ROOT ]; then
	# Link the frontend to nginx.
	if [ ! -d `dirname $WWW_ROOT` ]; then
		sudo mkdir -p `dirname $WWW_ROOT`
	fi
	sudo ln -s $OMEGAUP_ROOT/frontend/www $WWW_ROOT
fi

# check mysql

# Install config.php
if [ ! -f $OMEGAUP_ROOT/frontend/server/config.php ]; then
	cd $OMEGAUP_ROOT/frontend/server/
	sed -e "s/\(.*OMEGAUP_DB_USER.*\)'.*'.*$/\1'root');/;s/\(.*OMEGAUP_DB_PASS.*\)'.*'.*/\1'$MYSQL_PASSWORD');/" config.php.sample > config.php
	grep -v OMEGAUP_DB_ config.php > config.pre1
fi

# Set up the log.
if [ ! -f /var/log/omegaup/omegaup.log ]; then
	sudo mkdir -p /var/log/omegaup/
	sudo touch /var/log/omegaup/omegaup.log
	sudo chown www-data.www-data /var/log/omegaup/omegaup.log
fi

#chek php config.ini, set values for development

#check writable folders

#check and write config

#install database Omegaup

echo "Setting DB to UTC"
mysql -uroot -p$MYSQL_PASSWORD -e " SET GLOBAL time_zone = '+00:00'; "

if [ ! `mysql -uroot -p$MYSQL_PASSWORD --batch --skip-column-names -e "SHOW DATABASES LIKE '$MYSQL_DB_NAME'" | grep $MYSQL_DB_NAME` ]; then
	echo "Installing DB"
	mysql -uroot -p$MYSQL_PASSWORD -e "CREATE DATABASE $MYSQL_DB_NAME;" 
	mysql -uroot -p$MYSQL_PASSWORD $MYSQL_DB_NAME < $OMEGAUP_ROOT/frontend/private/bd.sql
	
	echo "Installing States and Countries"
	mysql -uroot -p$MYSQL_PASSWORD $MYSQL_DB_NAME < $OMEGAUP_ROOT/frontend/private/countries_and_states.sql
fi

#update config.php

#test curl

#test index with curl

#setup tests
if [ ! -f $OMEGAUP_ROOT/frontend/tests/test_config.php ]; then
	cp $OMEGAUP_ROOT/frontend/tests/test_config.php.sample $OMEGAUP_ROOT/frontend/tests/test_config.php
fi

if [ ! -f $OMEGAUP_ROOT/frontend/tests/controllers/omegaup.log ]; then
	touch $OMEGAUP_ROOT/frontend/tests/controllers/omegaup.log
fi

if [ ! -d $OMEGAUP_ROOT/frontend/tests/controllers/problems ]; then
	mkdir $OMEGAUP_ROOT/frontend/tests/controllers/problems
fi

if [ ! -d $OMEGAUP_ROOT/frontend/tests/controllers/submissions ]; then
	mkdir $OMEGAUP_ROOT/frontend/tests/controllers/submissions
fi

# Execute tests
if [ "$SKIP_PHPUNIT" != "1" ]; then
	OLDPATH=`pwd`
	cd $OMEGAUP_ROOT/frontend/tests/
	phpunit controllers/
	phpunit server/
	cd $OLDPATH
fi

echo SUCCESS
