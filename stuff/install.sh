#!/bin/bash
set -evx

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
KEYSTORE_PASSWORD=omegaup
MYSQL_DB_NAME=omegaup
UBUNTU=`uname -a | grep -i ubuntu | wc -l`
WHEEZY=`grep 'Debian GNU/Linux 7' /etc/issue | wc -l`
SAUCY=`grep 'Ubuntu 13.10' /etc/issue | wc -l`
TRUSTY=`grep 'Ubuntu 14.04' /etc/issue | wc -l`
HOSTNAME=localhost
MINIJAIL_ROOT=/var/lib/minijail

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
		"h")
			show_help
			;;
	esac
done

# Install _crucial_ stuff first.
if [ ! -f /usr/bin/vim ]; then
	sudo apt-get install -qq -y vim
fi

if [ ! -f /usr/bin/curl ]; then
	sudo apt-get install -qq -y curl
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
		wget -O - http://dl.hhvm.com/conf/hhvm.gpg.key | sudo apt-key add -
		echo deb http://dl.hhvm.com/ubuntu trusty main | sudo tee /etc/apt/sources.list.d/hhvm.list
	elif [ "$WHEEZY" != "1" ]; then
		curl -s http://www.dotdeb.org/dotdeb.gpg | sudo apt-key add - > /dev/null
		cat > dotdeb.list << EOF
deb http://packages.dotdeb.org squeeze all
deb-src http://packages.dotdeb.org squeeze all
EOF
		sudo mv dotdeb.list /etc/apt/sources.list.d
	fi
	
	sudo apt-get update -qq -y
	
	sudo apt-get install -qq -y nginx mysql-client git phpunit phpunit-selenium php5-fpm g++ fp-compiler unzip openssh-client make zip libcap-dev libgfortran3 ghc libelf-dev hhvm-nightly
	sudo apt-get install -qq -y openjdk-7-jdk || sudo apt-get install -qq -y openjdk-6-jdk
	
	if [ ! -f /usr/sbin/mysqld ]; then
		sudo DEBIAN_FRONTEND=noninteractive apt-get install -q -y mysql-server
		sleep 5
		mysqladmin -u root password $MYSQL_PASSWORD
	fi
fi

# Install SBT.
if [ ! -f /usr/bin/sbt ]; then
	sudo wget -q http://repo.typesafe.com/typesafe/ivy-releases/org.scala-sbt/sbt-launch/0.13.1/sbt-launch.jar -O /usr/bin/sbt-launch.jar
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

	# Update the submodules
	pushd $OMEGAUP_ROOT
	git submodule update --init

	# Generate the certificates required.
	bin/certmanager init --password $KEYSTORE_PASSWORD

	# Build minijail
	cd $OMEGAUP_ROOT/minijail
	make

	# Grab all sbt dependencies -- including MySQL.
	cd $OMEGAUP_ROOT/backend
	sbt update

	popd
fi

# Set up the minijail.
if [ ! -d $MINIJAIL_ROOT ]; then
	sudo mkdir -p $MINIJAIL_ROOT/{bin,dist,scripts,lib}
	sudo cp $OMEGAUP_ROOT/bin/{karel,kcl} $MINIJAIL_ROOT/bin/
	sudo cp $OMEGAUP_ROOT/minijail/{minijail0,libminijailpreload.so,ldwrapper} $MINIJAIL_ROOT/bin/
	sudo cp $OMEGAUP_ROOT/stuff/minijail-scripts/* $MINIJAIL_ROOT/scripts/
	sudo cp $OMEGAUP_ROOT/stuff/libkarel.py $MINIJAIL_ROOT/lib/

	pushd $MINIJAIL_ROOT
		sudo python $OMEGAUP_ROOT/stuff/mkroot
	popd
fi

# Install the grader service.
if [ ! -f /etc/init.d/omegaup ]; then
	sudo cp $OMEGAUP_ROOT/stuff/omegaup.service /etc/init.d/omegaup
	sed -e "s/db.user\s*=.*$/db.user=root/;s/db.password\s*=.*$/db.password=$MYSQL_PASSWORD/;s/\(.*\.password\)\s*=.*$/\1=$KEYSTORE_PASSWORD/" $OMEGAUP_ROOT/backend/grader/omegaup.conf.sample > $OMEGAUP_ROOT/bin/omegaup.conf
	sudo update-rc.d omegaup defaults
	cp ~/.ivy2/cache/mysql/mysql-connector-java/jars/mysql-connector-java-5.1.29.jar $OMEGAUP_ROOT/bin
	cp $OMEGAUP_ROOT/backend/grader/omegaup.jks $OMEGAUP_ROOT/bin
	sudo mkdir -p /var/log/omegaup
	sudo touch /var/log/omegaup/service.log
	sudo chown omegaup.omegaup /var/log/omegaup/service.log
	sudo sh -c 'echo "omegaup ALL = NOPASSWD: /var/lib/minijail/bin/minijail0" >> /etc/sudoers'
	if [ "`grep '\/lib\/security\/nss.cfg' /etc/java-7-openjdk/security/java.security`" != "" ]; then
		sed -e 's/.*\/lib\/security\/nss.cfg/security.provider.9=sun.security.ec.SunEC/' /etc/java-7-openjdk/security/java.security > ~/.java.security
		sudo mv ~/.java.security /etc/java-7-openjdk/security/java.security
	fi
	sudo service omegaup start
fi

# Set up the www root.
if [ ! -d $WWW_ROOT ]; then
	# Link the frontend to nginx.
	if [ ! -d `dirname $WWW_ROOT` ]; then
		sudo mkdir -p `dirname $WWW_ROOT`
	fi
	sudo ln -s $OMEGAUP_ROOT/frontend/www $WWW_ROOT
	# Images directory
	if [ ! -d $WWW_ROOT/img ]; then
		sudo mkdir $WWW_ROOT/img
	fi
	sudo chown www-data.www-data $WWW_ROOT/img
fi

# Add ngnix configuration.
if [ "$SKIP_NGINX" != "1" ]; then
	FPM_PORT=127.0.0.1:9000
	cat > default.conf << EOF
server {
listen       80;
server_name  .$HOSTNAME;
client_max_body_size 0;
root   $OMEGAUP_ROOT/frontend/www;

location / {
    index  index.php index.html;
}

include $OMEGAUP_ROOT/frontend/server/nginx.rewrites;

# pass the PHP scripts to FastCGI server listening on $FPM_PORT.
location ~ \.(hh|php)$ {
    fastcgi_keep_conn on;
    fastcgi_pass   $FPM_PORT;
    fastcgi_index  index.php;
    fastcgi_param  SCRIPT_FILENAME  \$document_root\$fastcgi_script_name;
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

	sudo service php5-fpm stop
	sudo update-rc.d -f php5-fpm remove
	sudo service hhvm restart
	sudo update-rc.d hhvm defaults
	sudo service nginx restart
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
	pushd $OMEGAUP_ROOT/frontend/server/
	cat > config.php << EOF
<?php
define('OMEGAUP_DB_USER', 'root');
define('OMEGAUP_DB_PASS', '$MYSQL_PASSWORD');
define('OMEGAUP_DB_NAME', '$MYSQL_DB_NAME');
EOF
	popd
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
	# omegaup:omegaup
	# user:user
	mysql -uroot -p$MYSQL_PASSWORD $MYSQL_DB_NAME -e 'INSERT INTO Users(username, name, password, verified) VALUES("omegaup", "omegaUp admin", "$2a$08$tyE7x/yxOZ1ltM7YAuFZ8OK/56c9Fsr/XDqgPe22IkOORY2kAAg2a", 1), ("user", "omegaUp user", "$2a$08$wxJh5voFPGuP8fUEthTSvutdb1OaWOa8ZCFQOuU/ZxcsOuHGw0Cqy", 1);'
	mysql -uroot -p$MYSQL_PASSWORD $MYSQL_DB_NAME -e 'INSERT INTO Emails (email, user_id) VALUES("admin@omegaup.com", 1), ("user@omegaup.com", 2);'
	mysql -uroot -p$MYSQL_PASSWORD $MYSQL_DB_NAME -e 'UPDATE Users SET main_email_id=user_id;'
	mysql -uroot -p$MYSQL_PASSWORD $MYSQL_DB_NAME -e 'INSERT INTO User_Roles VALUES(1, 1, 0);'
	
	echo "Installing States and Countries"
	mysql -uroot -p$MYSQL_PASSWORD $MYSQL_DB_NAME < $OMEGAUP_ROOT/frontend/private/countries_and_states.sql

	echo "Installing test db"
	mysql -uroot -p$MYSQL_PASSWORD -e "CREATE DATABASE \`$MYSQL_DB_NAME-test\`;" 
	mysql -uroot -p$MYSQL_PASSWORD $MYSQL_DB_NAME-test < $OMEGAUP_ROOT/frontend/private/bd.sql
	mysql -uroot -p$MYSQL_PASSWORD $MYSQL_DB_NAME-test < $OMEGAUP_ROOT/frontend/private/countries_and_states.sql
fi

#test curl

#test index with curl

#setup tests
if [ ! -f $OMEGAUP_ROOT/frontend/tests/test_config.php ]; then
	cat > $OMEGAUP_ROOT/frontend/tests/test_config.php << EOF
<?php
define('OMEGAUP_DB_USER', 'root');
define('OMEGAUP_DB_PASS', '$MYSQL_PASSWORD');
define('OMEGAUP_DB_NAME', '$MYSQL_DB_NAME-test');
EOF
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
	if [ "`grep '\/usr\/share\/php' /etc/hhvm/php.ini | wc -l`" -eq 0 ]; then
		cat | sudo tee /etc/hhvm/php.ini << EOF
; php options
include_path = /usr/share/php:.

; hhvm specific
hhvm.log.level = Warning
hhvm.log.always_log_unhandled_exceptions = true
hhvm.log.runtime_error_reporting_level = 8191
hhvm.mysql.typed_results = false
EOF
	fi
	pushd $OMEGAUP_ROOT/frontend/tests/
	hhvm /usr/bin/phpunit controllers/
	hhvm /usr/bin/phpunit server/
	popd
fi

echo SUCCESS
