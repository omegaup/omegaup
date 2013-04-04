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

# Get parameters
while getopts "u:m:p:01" optname; do
	case "$optname" in
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
	esac
done

if [ "$GIT_USERNAME" == "" -o "$GIT_EMAIL" == "" ]; then
	show_help
fi

if [ "`which curl`" == "" ]; then
	sudo apt-get install -qq -y curl
fi

# Install everything needed.
if [ "$SKIP_INSTALL" != "1" ]; then
	curl -s http://www.dotdeb.org/dotdeb.gpg | sudo apt-key add - > /dev/null
	cat > dotdeb.list << EOF
deb http://packages.dotdeb.org squeeze all
deb-src http://packages.dotdeb.org squeeze all
EOF
	sudo mv dotdeb.list /etc/apt/sources.list.d
	sudo apt-get update -qq -y
	sudo apt-get install -qq -y expect
	if [ ! -f /usr/sbin/mysqld ]; then
		VAR=$(sudo expect -c "
spawn sudo apt-get -qq -y install mysql-server
expect \"New password for the MySQL \\\"root\\\" user:\"
send \"$MYSQL_PASSWORD\\r\"
expect \"Repeat password for the MySQL \\\"root\\\" user:\"
send \"$MYSQL_PASSWORD\\r\"
expect eof")
		echo "$VAR"
	fi
	sudo apt-get install -qq -y nginx mysql-client php5-fpm php5-cli php5-mysql php-pear php5-mcrypt php5-curl git phpunit g++ fp-compiler unzip openjdk-6-jdk openssh-client make vim
fi

# Install SBT.
if [ ! -f /usr/bin/sbt ]; then
	sudo wget http://repo.typesafe.com/typesafe/ivy-releases/org.scala-sbt/sbt-launch//0.12.3/sbt-launch.jar -O /usr/bin/sbt-launch.jar
	cat > sbt << EOF
#!/bin/sh
java -Xms512M -Xmx1536M -Xss1M -XX:+CMSClassUnloadingEnabled -XX:MaxPermSize=384M -jar \`dirname \$0\`/sbt-launch.jar "\$@"
EOF
	sudo mv sbt /usr/bin/
	sudo chmod +x /usr/bin/sbt
fi

# Add ngnix configuration.
if [ "$SKIP_NGINX" != "1" ]; then
	cat > default.conf << EOF
server {
listen       80;
server_name  localhost;

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

# pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
location ~ \.php$ {
    root           /var/www/omegaup.com;
    fastcgi_pass   127.0.0.1:9000;
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
	read -n 1 -p "Press any key to continue"
fi

# Clone repository.
if [ ! -d $WWW_ROOT ]; then
	sudo mkdir $OMEGAUP_ROOT
	sudo chown $USER -R $OMEGAUP_ROOT
	git clone https://github.com/omegaup/omegaup.git $OMEGAUP_ROOT

	# Generate the certificates required.
	cd $OMEGAUP_ROOT
	bin/gencerts.sh

	# Build the sandbox
	cd $OMEGAUP_ROOT/sandbox
	make

	# Build common
	cd $OMEGAUP_ROOT/common
	sbt package

	# Build runner
	cd $OMEGAUP_ROOT/runner
	sbt proguard

	# Build grader
	cd $OMEGAUP_ROOT/grader
	sbt proguard

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
	cp config.php.sample config.php
	cat config.php | grep -v OMEGAUP_DB_ > config.pre1
fi

#chek php config.ini, set values for development

#check writable folders

#check and write config

#check db connection

#install user and db

#update config.php

#test curl

#test index with curl

#look for phpunit
