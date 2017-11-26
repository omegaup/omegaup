pid /tmp/nginx.pid;
worker_processes 1;

error_log /tmp/error.log debug;

events {
  worker_connections 1024;
}

http {
  client_body_temp_path /tmp/client_body;
  fastcgi_temp_path /tmp/fastcgi_temp;
  proxy_temp_path /tmp/proxy_temp;
  scgi_temp_path /tmp/scgi_temp;
  uwsgi_temp_path /tmp/uwsgi_temp;
  access_log /tmp/access.log;
  error_log /tmp/error.log;

  include /etc/nginx/mime.types;

  include ${OMEGAUP_ROOT}/stuff/travis/nginx/sites-enabled/*.conf;

  upstream php {
    server 127.0.0.1:9000;
  }
}
