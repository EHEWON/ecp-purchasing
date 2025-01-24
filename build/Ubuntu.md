# 部署文档 (非 Docker 部署)

## 概述

本文档详细描述了如何在全新的 Linux 系统上从头开始部署 EzWork 项目。EzWork 项目由 Python 后端、PHP 前端和 MySQL 数据库组成。以下将详细说明每个组件的安装与配置。

## 环境要求

- Linux 操作系统（CentOS）
- 必要的工具：`git`, `curl`, `wget`
- 运行以下命令前，请确保您有 sudo 权限。

## 步骤概述

1. 更新系统和安装必要的工具
2. 安装 Python 和相关包
3. 安装 PHP 和必要的扩展
4. 安装 MySQL 数据库
5. 下载并配置 EzWork 项目
6. 启动服务并配置数据库

## 1. 更新系统和安装必要的工具

首先，更新系统并安装常用工具：



## 2. 安装 PHP 和必要的扩展

### 安装 PHP 8.2

```bash
sudo apt update -y
sudo apt search openssl
sudo apt install -y vim curl wget tar bzip2 unzip  passwd  hostname net-tools rsync man telnet 
sudo apt -y install autoconf curl  gcc gmp-dev 
sudo apt install -y  libjpeg-dev libpng-dev sqlite-dev  autoconf automake libtool --allowerasing
sudo apt install -y libxslt1.1  libxslt-dev libxml2 libxml2-dev libssl-dev  pcre-dev zlib zlib-dev 
wget -O /mnt/php-8.2.24.tar.gz https://www.php.net/distributions/php-8.2.24.tar.gz --no-check-certificate
wget -O /mnt/openresty-1.19.9.1.tar.gz https://openresty.org/download/openresty-1.19.9.1.tar.gz
wget -O /mnt/freetype-2.10.0.tar.gz https://download.savannah.gnu.org/releases/freetype/freetype-2.10.0.tar.gz
wget -O /mnt/jpegsrc.v9e.tar.gz  http://www.ijg.org/files/jpegsrc.v9e.tar.gz
wget -O /mnt/xlswriter-1.5.1.tgz https://pecl.php.net/get/xlswriter-1.5.1.tgz
wget -O /mnt/redis-5.3.5.tgz https://pecl.php.net/get/redis-5.3.5.tgz
wget -O /mnt/cmake-3.22.1.zip https://github.com/Kitware/CMake/releases/download/v3.22.1/cmake-3.22.1.zip
wget -O /mnt/libzip-1.11.1.tar.gz  https://libzip.org/download/libzip-1.11.1.tar.gz --no-check-certificate
wget -O /mnt/oniguruma-6.9.4.tar.gz  https://github.com/kkos/oniguruma/archive/v6.9.4.tar.gz --no-check-certificate
wget -O /mnt/libpng-1.5.30.tar.gz  https://sourceforge.net/projects/libpng/files/libpng15/1.5.30/libpng-1.5.30.tar.gz --no-check-certificate
wget -O /mnt/libsodium-1.0.18.tar.gz https://github.com/jedisct1/libsodium/releases/download/1.0.18-RELEASE/libsodium-1.0.18.tar.gz \
&& cd /mnt/ \
&& tar zxf libsodium-1.0.18.tar.gz \
&& cd libsodium-1.0.18 \
&& ./configure --libdir=/lib64 \
&& make && make install \
&& rm /mnt/libsodium-1.0.18.tar.gz && rm -rf /mnt/libsodium-1.0.18
cd /mnt/ && tar -zxvf jpegsrc.v9e.tar.gz &&  cd jpeg-9e && ./configure --enable-shared && make && make install
wget https://github.com/kkos/oniguruma/archive/v6.9.4.tar.gz -O oniguruma-6.9.4.tar.gz 
cd /mnt/ && tar -zxvf oniguruma-6.9.4.tar.gz && cd oniguruma-6.9.4/ && ./autogen.sh && ./configure --prefix=/usr --libdir=/lib64 \
 && make && make install && rm /mnt/oniguruma-6.9.4.tar.gz && rm -rf /mnt/oniguruma-6.9.4
cd /mnt/ && tar -zxvf jpegsrc.v9e.tar.gz &&  cd jpeg-9e && ./configure --enable-shared && make && make install \
&& rm /mnt/jpegsrc.v9e.tar.gz && rm -rf /mnt/jpeg-9e
cd /mnt/ && tar -zxvf /mnt/libpng-1.5.30.tar.gz &&  cd libpng-1.5.30 && ./configure && make && make install \
&& rm /mnt/libpng-1.5.30.tar.gz && rm -rf /mnt/libpng-1.5.30
cd /mnt/ && unzip cmake-3.22.1.zip && cd cmake-3.22.1 && ./bootstrap && make && make install \
&& rm /mnt/cmake-3.22.1.zip && rm -rf /mnt/cmake-3.22.1
cd /mnt/ && tar -zxvf libzip-1.11.1.tar.gz  && cd libzip-1.11.1 && mkdir build && cd build && cmake .. && make -j4 && make install \
&& rm /mnt/libzip-1.11.1.tar.gz && rm -rf /mnt/libzip-1.11.1
cd /mnt/ && tar -zxvf freetype-2.10.0.tar.gz && cd freetype-2.10.0 && ./configure && make && make install \
&& rm /mnt/freetype-2.10.0.tar.gz && rm -rf /mnt/freetype-2.10.0
cd /mnt/ && \
wget -o /mnt/libc-client2007e_2007f~dfsg-7+b3_amd64.deb  http://ftp.hk.debian.org/debian/pool/main/u/uw-imap/libc-client2007e_2007f~dfsg-7+b3_amd64.deb \
&& apt install /mnt/libc-client2007e_2007f~dfsg-7+b3_amd64.deb   && rm /mnt/libc-client2007e_2007f~dfsg-7+b3_amd64.deb 
cd /mnt/ && wget -o /mnt/uw-mailutils_2007f~dfsg-7+b3_amd64.deb  http://ftp.hk.debian.org/debian/pool/main/u/uw-imap/uw-mailutils_2007f~dfsg-7+b3_amd64.deb \
&& apt install /mnt/uw-mailutils_2007f~dfsg-7+b3_amd64.deb  && rm /mnt/uw-mailutils_2007f~dfsg-7+b3_amd64.deb
cd /mnt/ &&  tar -zxvf /mnt/php-8.2.24.tar.gz && cd php-8.2.24 && \
 ./configure --prefix=/usr/local/php --with-config-file-path=/usr/local/php/etc --with-mysqli --with-pcre-regex \
--with-pdo-mysql --with-pdo-sqlite --with-pear --with-png-dir --with-xmlrpc --with-openssl --with-xsl --with-curl \
--with-zlib --with-zlib --enable-fpm --enable-bcmath --enable-libxml --enable-inline-optimization --enable-gd-native-ttf \
--enable-mbregex --enable-mbstring --enable-opcache --enable-pcntl --with-fpm-user=root --with-fpm-group=root --with-gettext \
--enable-shmop --enable-soap --enable-sockets --enable-sysvsem --enable-xml --enable-zip --with-iconv-dir --with-kerberos \
--enable-mbstring --enable-exif --with-libdir=lib64 --with-libxml-dir --with-freetype-dir --with-gd --with-jpeg-dir \
--with-kerberos --with-imap --with-imap-ssl && make && make install \
&& cp ./php.ini-production /usr/local/php/etc/php.ini \
&& cp /usr/local/php/etc/php-fpm.conf.default /usr/local/php/etc/php-fpm.conf \
&& cp /usr/local/php/etc/php-fpm.d/www.conf.default  /usr/local/php/etc/php-fpm.d/www.conf
cd /mnt/ && tar -zxvf redis-5.3.5.tgz && cd redis-5.3.5 && /usr/local/php/bin/phpize \
&& ./configure --with-php-config=/usr/local/php/bin/php-config \
&& make && make install \
&& echo "extension = redis.so" >>/usr/local/php/etc/php.ini  \
&& rm /mnt/redis-5.3.5.tgz && rm -rf /mnt/redis-5.3.5
cd /mnt/ &&  tar -zxvf xlswriter-1.5.1.tgz &&  cd xlswriter-1.5.1 \
&& /usr/local/php/bin/phpize &&  ./configure --with-php-config=/usr/local/php/bin/php-config \
&& make && make install \
&& echo "extension = xlswriter.so" >>/usr/local/php/etc/php.ini  \
&& rm /mnt/xlswriter-1.5.1.tgz && rm -rf /mnt/xlswriter-1.5.1
cd /mnt/php-8.2.24/ext/sodium  \
&& /usr/local/php/bin/phpize &&  ./configure --with-php-config=/usr/local/php/bin/php-config \
&& make && make install \
&& echo "extension = sodium.so" >>/usr/local/php/etc/php.ini  \
&& rm /mnt/php-8.2.24.tar.gz && rm -rf /mnt/php-8.2.24
sed -i 's|post_max_size = 8M|post_max_size = 80M|g' /usr/local/php/etc/php.ini
sed -i 's|upload_max_filesize = 2M|upload_max_filesize = 80M|g' /usr/local/php/etc/php.ini
```

### 启动 PHP-FPM

```bash
/usr/local/php/sbin/php-fpm -R
```
## 3. 安装 REDIS 数据库
```bash
sudo apt-get update
sudo apt install redis redis-devel -y
echo "requirepass = ecp@2024" >>/etc/redis.conf
sudo systemctl enable redis
sudo systemctl start redis
```

## 4. 安装 MySQL 数据库

### 安装 MySQL

```bash
sudo apt-get update
sudo apt install -y mysql-server
```

### 启动 MySQL 服务

```bash
sudo systemctl start mysqld
sudo systemctl enable mysqld
```

### 配置 MySQL

设置 MySQL root 用户密码并创建数据库：

```bash
sudo mysql_secure_installation

# 登录 MySQL
mysql -uroot -p

# 创建数据库和用户并授权
CREATE DATABASE ezwork;
```

### 初始化数据库

在您项目的 `init.sql` 文件中创建所需的表和初始数据。例如：

将上述 SQL 语句运行到 MySQL 中。

```bash
mysql -uroot -p ezwork < ./init.sql
```

## 5. 下载并配置项目

### 克隆项目代码

```bash
cd /data/www/
sudo git clone git clone https://gitee.com/ehewon/ecp-purchasing.git ecp-purchasing
cd ecp-purchasing
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer
composer install
```




## 6. 启动服务

### 启动 PHP-FPM

确认 PHP-FPM 正在运行：

```bash
sudo systemctl status php-fpm  # CentOS
```

### 启动 Nginx

如果您需要通过 Nginx 作为反向代理来处理 HTTP 请求，可以安装并配置 Nginx：

```bash
sudo apt update
sudo apt install -y nginx
```

### 配置 Nginx
- 须把 xxx.xxx.com 替换成您的域名 ,xxx.pem,xxx_privkey.pem替换成您的SSL证书和私钥，/var/www/public/替换成您的目录地址
在 `/etc/nginx/conf.d/` 目录下创建一个新的配置文件（如 `ezwork`），并添加以下内容：

```nginx
server {
    listen 80;
    server_name xxx.xxx.com;
    server_tokens off;
    index index.html index.htm index.php ;
    root /data/www/ecp-purchasing/public/;
    access_log /var/log/nginx/ecpbase.erui.com_access.log;
    error_log /var/log/nginx/ecpbase.erui.com_error.log;
    location /front {
        add_header Cache-Control "private, no-store, no-cache, must-revalidate, proxy-revalidate";
        access_log on;
        index index.php index.html index.htm;
        alias /data/www/ecp-purchasing/front/;
    }
    location /front/ {
        add_header Cache-Control "private, no-store, no-cache, must-revalidate, proxy-revalidate";
        access_log on;
        index index.php index.html index.htm;
        alias /data/www/ecp-purchasing/front/;
    }

    location /wss {
        proxy_pass http://basewebsock;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection Upgrade;
    }
    location /admin {
        rewrite ^/(.*)$ /index.php?s=$1 last;
    }
    location /frontend {
        rewrite ^/(.*)$ /index.php?s=$1 last;
    }
    location /upload {
        rewrite ^/(.*)$ /index.php?s=$1 last;
    }
    location ~ \.php$ {
        root /data/www/ecp-purchasing/public/;
        include fastcgi.conf;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ .*\.(gif|jpg|jpeg|png|bmp|swf|web)$ {
        expires 30d;
    }
    location ~ .*\.(js|css)?$ {
        expires 1h;
    }
    location = /favicon.ico {
        log_not_found off;
        access_log off;
    }

    location ~ /\. {
        deny all;
    }
}

upstream basewebsock {
    server 127.0.0.1:23090;
}

```

### 启动 Nginx

```bash
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 访问应用

在浏览器中访问您的域名或服务器 IP，比如 `http://your_domain_or_IP`。
