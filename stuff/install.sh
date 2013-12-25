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
SAUCY=`grep 'Ubuntu 13.10' /etc/issue | wc -l`
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
		"0")
			SKIP_INSTALL=1
			;;
		"1")
			SKIP_NGINX=1
			;;
		"2")
			SKIP_GRADER=1
			;;
		"h")
			show_help
			;;
	esac
done

# Install _crucial_ stuff first.
if [ ! -f /usr/bin/curl ]; then
	sudo apt-get install -qq -y curl
fi

if [ ! -f /usr/bin/vim ]; then
	sudo apt-get install -qq -y vim
fi

# Ensure users have been added.
sudo useradd omegaup >/dev/null 2>&1 || echo
sudo useradd www-data >/dev/null 2>&1 || echo

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
	sudo apt-get install -qq -y php5-json || echo
	
	# Restart php-fpm so it picks php5-curl and php5-mcrypt.
	if [ "$SAUCY" = "1" ]; then
		# Saucy has some bugs with the installation of these packages :(
		if [ ! -f /etc/php5/fpm/conf.d/20-curl.ini ]; then
			echo "extension=curl.so" > 20-curl.ini
			sudo cp 20-curl.ini /etc/php5/cli/conf.d
			sudo mv 20-curl.ini /etc/php5/fpm/conf.d
		fi
		if [ ! -f /etc/php5/fpm/conf.d/20-mcrypt.ini ]; then
			echo "extension=mcrypt.so" > 20-mcrypt.ini
			sudo cp 20-mcrypt.ini /etc/php5/cli/conf.d
			sudo mv 20-mcrypt.ini /etc/php5/fpm/conf.d
		fi
		sudo service php5-fpm restart
		sudo service nginx restart
	else
		sudo /etc/init.d/php5-fpm restart
	fi
fi

# Install SBT.
if [ ! -f /usr/bin/sbt ]; then
	sudo wget -q http://repo.typesafe.com/typesafe/ivy-releases/org.scala-sbt/sbt-launch//0.12.3/sbt-launch.jar -O /usr/bin/sbt-launch.jar
	cat > sbt << EOF
#!/bin/sh
java -Xss1M -XX:+CMSClassUnloadingEnabled -XX:MaxPermSize=384M -jar \`dirname \$0\`/sbt-launch.jar "\$@"
EOF
	sudo mv sbt /usr/bin/
	sudo chmod +x /usr/bin/sbt
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
	# Images directory
	sudo mkdir $WWW_ROOT/img
	sudo chown www-data.www-data $WWW_ROOT/img
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
    root   $WWW_ROOT;
    index  index.php index.html;
}

# redirect server error pages to the static page /50x.html
#
error_page   500 502 503 504  /50x.html;
location = /50x.html {
    root   html;
}

include $OMEGAUP_ROOT/frontend/server/nginx.rewrites;

# pass the PHP scripts to FastCGI server listening on $FPM_PORT.
location ~ \.php$ {
    root           $WWW_ROOT;
    fastcgi_pass   $FPM_PORT;
    fastcgi_index  index.php;
    fastcgi_param  SCRIPT_FILENAME  $WWW_ROOT\$fastcgi_script_name;
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

# Set up runtime directories.
if [ ! -d /var/lib/omegaup ]; then
	sudo mkdir -p /var/lib/omegaup/{compile,grade,input,problems,submissions}
	sudo chown www-data.www-data /var/lib/omegaup/{problems,submissions}
	sudo chown omegaup.omegaup /var/lib/omegaup/{compile,grade,input}
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
