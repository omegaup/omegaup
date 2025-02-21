	user  nginx;
	worker_processes  1;

	events {
	    worker_connections  1024;
	}

	http {
	    include       mime.types;
	    default_type  application/octet-stream;

	    sendfile        on;
	    keepalive_timeout  65;

	    server {
		listen       80;
		server_name  localhost;

		location / {
		    root   /var/www;
		    index  index.php index.html;
		}

		# redirect server error pages to the static page /50x.html
		#
		error_page   500 502 503 504  /50x.html;
		location = /50x.html {
		    root   html;
		}

		# WebSockets.
		location ~ /api/contest/events/[a-zA-Z0-9_-]+/?$ {
		    proxy_pass http://localhost:39613;
		    proxy_http_version 1.1;
		    proxy_set_header Upgrade $http_upgrade;
		    proxy_set_header Connection "upgrade";
		    proxy_set_header Host $host;
		}

		# rewrites.
		rewrite ^/contest/(.*)$ /contest.php?alias=$1 last;
		rewrite ^/api/(.*)$ /api/ApiEntryPoint.php last;
		rewrite ^/arena/?$ /arena/index.php last;
		rewrite ^/arena/[a-zA-Z0-9_+-]+/?$ /arena/contest.php last;
	 	rewrite ^/arena/[a-zA-Z0-9_+-]+/scoreboard/?$ /arena/scoreboard.php last;
		rewrite ^/arena/[a-zA-Z0-9_+-]+/admin/?$ /arena/admin.php last;
		rewrite ^/arena/[a-zA-Z0-9_+-]+/practice/?$ /arena/practice.php last;

		# pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
		location ~ \.php$ {
		    root           /var/www;
		    fastcgi_pass   127.0.0.1:9000;
		    fastcgi_index  index.php;
		    fastcgi_param  SCRIPT_FILENAME  /var/www$fastcgi_script_name;
		    include        fastcgi_params;
		}

		# deny access to .htaccess files, if Apache's document root
		# concurs with nginx's one
		location ~ /\.ht {
		    deny  all;
		}
	    }
	}