#!/usr/bin/php
<?php
// by Jean-Bernard Addor 2011
if (1 != assert_options(ASSERT_ACTIVE) or 1 != assert_options(ASSERT_WARNING)):
  trigger_error('Assertion ignored');
endif;


$return_var = 0;
echo passthru('mkdir --parents '.'/tmp'.getcwd(), $return_var);
assert ('0 == $return_var');
// assert : directory must be writable and executable

// $process = proc_open("env PHP_FCGI_CHILDREN=15 php-cgi -b /tmp".getcwd()."/php.socket", $descriptorspec, $pipes);
// env should be modified here, if this is really needed
$php_process = proc_open("php-cgi -b /tmp".getcwd()."/php.socket",
  array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $php_pipes);
assert('FALSE != $php_process');

file_put_contents("/tmp".getcwd()."/nginx.conf", '
worker_processes  1;
error_log  /dev/stderr;
pid        /tmp'.getcwd().'/nginx.pid;
events {
    worker_connections  1024;
}

http {
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;
    client_max_body_size 10m;
    sendfile        on;
    gzip  on;
    keepalive_timeout  65;

    server {
        listen 8080;
        server_name 127.0.0.1;
        server_tokens off;
        root '.getcwd().'/web;
        index index.php index.html index.htm;

        access_log  /dev/stdout;

        location / {
                try_files $uri /index.php;
        }

        location ^~ /frontend_dev.php/ {
                try_files $uri /frontend_dev.php;
                # try_files $uri /frontend_dev.php?q=$uri&$args /index.php?q=$uri&$args;
        }

        location ^~ /sf/ {
          root '.getcwd().'/lib/vendor/symfony/data/web/;
        }

        location ~ \.php$ {
                include /etc/nginx/fastcgi_params;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                fastcgi_pass  unix:/tmp'.getcwd().'/php.socket; # 127.0.0.1:9000;
        }

        location ~* \.(js|css|png|jpg|jpeg|gif|ico)$ {
                expires max;
                log_not_found off;
        }
    }
}
');
// connections should only be accepted from localhost! (security issue)

echo passthru('nginx -c /tmp'.getcwd().'/nginx.conf -t', $return_var);
assert ('0 == $return_var');
usleep(200000); echo "Launching NGINX\n";
// echo passthru('nginx -c /tmp'.getcwd().'/nginx.conf', $return_var);
// assert ('0 == $return_var');

$nginx_process = proc_open('nginx -c /tmp'.getcwd().'/nginx.conf',
  array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $nginx_pipes);
assert('FALSE != $nginx_process'); // was blocking with passthru!! PHP 5.3.2-1ubuntu4.9
usleep(200000); // to be sure that stdout and stderr are printed

echo "Waiting for ctrl-c (", posix_getpid(), ")\n";
$oldset = array();
pcntl_sigprocmask(SIG_BLOCK, array(SIGHUP, SIGINT), $oldset);
pcntl_sigwaitinfo(array(SIGHUP, SIGINT, SIGUSR1));
pcntl_sigprocmask(SIG_SETMASK, $oldset);

echo "\nShutting doen NGINX\n";
echo passthru('nginx -c /tmp'.getcwd().'/nginx.conf -s stop', $return_var);
assert ('0 == $return_var');

echo "\nShutting down php-cgi (fcgi)\n";
$php_proc_terminate = proc_terminate($php_process);
$php_proc_close = proc_close($php_process);
assert(-1 != $php_proc_close);

// just for cleaness
$nginx_proc_terminate = proc_terminate($nginx_process);
$nginx_proc_close = proc_close($nginx_process);
assert(-1 != $nginx_proc_close);

?>
