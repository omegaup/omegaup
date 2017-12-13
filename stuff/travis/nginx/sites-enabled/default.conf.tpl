server {
	listen 8000 default_server;
	listen [::]:8000 default_server ipv6only=on;

	access_log /tmp/access.log;
	error_log /tmp/error.log;

	root ${OMEGAUP_ROOT}/frontend/www;
	index index.php index.html;

	location / {
		index index.php index.html;
	}

	location ~* "\.php(/|$)" {
		fastcgi_index index.php;
		fastcgi_keep_conn on;

		fastcgi_param QUERY_STRING $query_string;
		fastcgi_param REQUEST_METHOD $request_method;
		fastcgi_param CONTENT_TYPE $content_type;
		fastcgi_param CONTENT_LENGTH $content_length;

		fastcgi_param SCRIPT_FILENAME $request_filename;
		fastcgi_param SCRIPT_NAME $fastcgi_script_name;
		fastcgi_param REQUEST_URI $request_uri;
		fastcgi_param DOCUMENT_URI $document_uri;
		fastcgi_param DOCUMENT_ROOT $document_root;
		fastcgi_param SERVER_PROTOCOL $server_protocol;

		fastcgi_param GATEWAY_INTERFACE CGI/1.1;
		fastcgi_param SERVER_SOFTWARE nginx/$nginx_version;

		fastcgi_param REMOTE_ADDR $remote_addr;
		fastcgi_param REMOTE_PORT $remote_port;
		fastcgi_param SERVER_ADDR $server_addr;
		fastcgi_param SERVER_PORT $server_port;
		fastcgi_param SERVER_NAME $server_name;

		fastcgi_param HTTPS $https;

		fastcgi_param REDIRECT_STATUS 200;

		fastcgi_pass 127.0.0.1:9000;
	}
	include ${OMEGAUP_ROOT}/frontend/server/nginx.rewrites;
}
