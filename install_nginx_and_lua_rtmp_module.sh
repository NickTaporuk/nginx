#!/usr/bin/bash

export LUAJIT_LIB=/usr/local/lib
export LUAJIT_INC=/usr/local/include/luajit-2.1
export LD_LIBRARY_PATH=/usr/local/lib/:$LD_LIBRARY_PATH

./configure --prefix=/opt/nginx \
        --with-ld-opt="-Wl,-rpath,/usr/local/bin/luajit" \
        --add-module=/home/nick/nginx_compile/nginx-rtmp-module \
        --add-module=/home/nick/nginx_compile/ngx_devel_kit \
        --add-module=/home/nick/nginx_compile/lua-nginx-module \
        --with-http_flv_module \
        --with-http_ssl_module \
        --with-http_gzip_static_module \
        --with-pcre-jit \
        --error-log-path=/var/log/nginx/error.log \
        --http-log-path=/var/log/nginx/access.log \
        --with-http_geoip_module \
        --with-http_stub_status_module

make -j2
sudo make install

unset LUAJIT_LIB
unset LUAJIT_INC
