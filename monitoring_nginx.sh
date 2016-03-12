name module http_stub_status_module
in site config
location /nginx-stats {
        stub_status on;
    }