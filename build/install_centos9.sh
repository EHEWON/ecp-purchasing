############################################
# Build in Golang
# Run npm run build-healthcheck-armv7 in the host first, another it will be super slow where it is building the armv7 healthcheck
# Check file: builder-go.dockerfile
#libjpeg-devel.x86_64 libjpeg.x86_64 libmemcached libmemcached-devel
#https://mirrors.tuna.tsinghua.edu.cn/remi/enterprise/9/php82/x86_64/php-imap-8.2.24-1.el9.remi.x86_64.rpm
############################################
cd /etc/yum.repos.d/ && sed -i 's/mirrorlist/#mirrorlist/g' /etc/yum.repos.d/CentOS-* && sed -i 's|#baseurl=http://mirror.centos.org|baseurl=https://mirrors.aliyun.com|g' /etc/yum.repos.d/CentOS-* \
&& sed -i 's|baseurl=http://vault.centos.org|baseurl=https://mirrors.aliyun.com|g' /etc/yum.repos.d/CentOS-*
set $pwd=pwd
dnf update -y
dnf search openssl
dnf install -y vim curl wget tar bzip2 unzip vim-enhanced passwd sudo yum-utils hostname net-tools rsync man telnet  --allowerasing
dnf -y install autoconf curl freetype-devel.x86_64 freetype.x86_64  gcc gmp-devel libcurl libcurl-devel  --allowerasing
dnf install -y  libjpeg-devel libpng-devel.x86_64 libpng.x86_64 sqlite-devel  autoconf automake libtool --allowerasing
dnf install -y  libxml2 libxml2-devel openssl openssl-devel compat-openssl10 openssl-pkcs11 \
 openssl-perl openssl-libs pcre pcre-devel zlib zlib-devel gcc-c++ libxslt* libwebp-devel libwebp  --allowerasing
wget -O /mnt/php-8.2.24.tar.gz https://www.php.net/distributions/php-8.2.24.tar.gz --no-check-certificate
wget -O /mnt/openresty-1.19.9.1.tar.gz https://openresty.org/download/openresty-1.19.9.1.tar.gz
wget -O /mnt/freetype-2.10.0.tar.gz https://download.savannah.gnu.org/releases/freetype/freetype-2.10.0.tar.gz
wget -O /mnt/jpegsrc.v9e.tar.gz  http://www.ijg.org/files/jpegsrc.v9e.tar.gz
wget -O /mnt/xlswriter-1.5.1.tgz https://pecl.php.net/get/xlswriter-1.5.1.tgz
wget -O /mnt/redis-5.3.5.tgz https://pecl.php.net/get/redis-5.3.5.tgz
wget -O /mnt/cmake-3.22.1.zip https://github.com/Kitware/CMake/releases/download/v3.22.1/cmake-3.22.1.zip
wget -O /mnt/libzip-1.11.1.tar.gz  https://libzip.org/download/libzip-1.11.1.tar.gz --no-check-certificate
wget -O /mnt/oniguruma-6.9.4.tar.gz  https://github.com/kkos/oniguruma/archive/v6.9.4.tar.gz --no-check-certificate
wget -O /mnt/libsodium-1.0.18.tar.gz https://github.com/jedisct1/libsodium/releases/download/1.0.18-RELEASE/libsodium-1.0.18.tar.gz \
&& cd /mnt/ \
&& tar zxf libsodium-1.0.18.tar.gz \
&& cd libsodium-1.0.18 \
&& ./configure --libdir=/lib64 \
&& make && make install \
&& rm /mnt/flibsodium-1.0.18.tar.gz && rm -rf /mnt/libsodium-1.0.18
cd /mnt/ && tar -zxvf jpegsrc.v9e.tar.gz &&  cd jpeg-9e && ./configure --enable-shared && make && make install
wget https://github.com/kkos/oniguruma/archive/v6.9.4.tar.gz -O oniguruma-6.9.4.tar.gz 
cd /mnt/ && tar -zxvf oniguruma-6.9.4.tar.gz && cd oniguruma-6.9.4/ && ./autogen.sh && ./configure --prefix=/usr --libdir=/lib64 \
 && make && make install && rm /mnt/oniguruma-6.9.4.tar.gz && rm -rf /mnt/oniguruma-6.9.4
cd /mnt/ && tar -zxvf jpegsrc.v9e.tar.gz &&  cd jpeg-9e && ./configure --enable-shared && make && make install \
&& rm /mnt/jpegsrc.v9e.tar.gz && rm -rf /mnt/jpeg-9e
cd /mnt/ && unzip cmake-3.22.1.zip && cd cmake-3.22.1 && ./bootstrap && make && make install \
&& rm /mnt/cmake-3.22.1.zip && rm -rf /mnt/cmake-3.22.1
cd /mnt/ && tar -zxvf libzip-1.11.1.tar.gz  && cd libzip-1.11.1 && mkdir build && cd build && cmake .. && make -j4 && make install \
&& rm /mnt/libzip-1.11.1.tar.gz && rm -rf /mnt/libzip-1.11.1
cd /mnt/ && tar -zxvf freetype-2.10.0.tar.gz && cd freetype-2.10.0 && ./configure && make && make install \
&& rm /mnt/freetype-2.10.0.tar.gz && rm -rf /mnt/freetype-2.10.0
#COPY mysql-5.7.44-el7-x86_64.tar.gz /mnt/mysql-5.7.44-el7-x86_64.tar.gz
# cd /mnt/ && tar -zxvf mysql-5.7.44-el7-x86_64.tar.gz && mv mysql-5.7.44-el7-x86_64 /usr/local/mysql
 wget -O /mnt/libc-client-2007f-24.el8.x86_64.rpm https://mirrors.aliyun.com/epel/8/Everything/x86_64/Packages/l/libc-client-2007f-24.el8.x86_64.rpm --no-check-certificate
 cd /mnt/ && rpm -Uvh libc-client-2007f-24.el8.x86_64.rpm && ln -s /usr/lib64/libc-client.so /usr/lib/libc-client.so \
&& rm /mnt/libc-client-2007f-24.el8.x86_64.rpm
 wget -O /mnt/uw-imap-devel-2007f-24.el8.x86_64.rpm https://mirrors.aliyun.com/epel/8/Everything/x86_64/Packages/u/uw-imap-devel-2007f-24.el8.x86_64.rpm --no-check-certificate
 cd /mnt/ && rpm -Uvh uw-imap-devel-2007f-24.el8.x86_64.rpm && rm /mnt/uw-imap-devel-2007f-24.el8.x86_64.rpm
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
cd /mnt/ && tar -zxvf openresty-1.19.9.1.tar.gz &&  cd openresty-1.19.9.1 \
&& ./configure --prefix=/usr/local/openresty --with-cc-opt=-O2 --with-luajit  --with-http_gzip_static_module --with-http_realip_module \
--with-http_stub_status_module --with-http_v2_module --with-http_ssl_module --with-stream \
--with-stream_ssl_module --with-stream_ssl_preread_module \
&& gmake && gmake install
wget https://downloads.mysql.com/archives/get/p/23/file/mysql-5.7.44-el7-x86_64.tar.gz  --no-check-certificate
echo "export PATH=\$PATH:/usr/local/mysql/bin" >>/etc/profile
echo "export PATH=\$PATH:/usr/local/php/bin" >>/etc/profile
cd /mnt/ && /usr/local/php/bin/php -r "copy('https://install.phpcomposer.com/installer', 'composer-setup.php');" \
&& /usr/local/php/bin/php composer-setup.php \
&& /usr/local/php/bin/php -r "unlink('composer-setup.php');" \
&& sudo mv composer.phar /usr/local/bin/composer \
&& /usr/local/bin/composer config --global process-timeout 2000 \
&& /usr/local/bin/composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
&& cd $pwd && cd ../ && /usr/local/php/bin/php /usr/local/bin/composer install
echo -e "[mysqld] \n \
datadir=/usr/local/mysql/data \n \
explicit_defaults_for_timestamp=1 \n \
expire_logs_days = 7 \n \
binlog_format=ROW \n \
query_cache_type =ON \n \
query_cache_size=32M \n \
server-id = 102 \n \
sql_mode=STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION \n \
symbolic-links=0 \n \
[mysqld_safe] \n \
log-error=/usr/local/mysql/logs/mysql.log \n \
pid-file=/usr/local/mysql/logs/mariadb.pid" \
>>/etc/my.cnf
source /etc/profile
chmod -R 777 /usr/local/mysql/data/
mkdir /usr/local/mysql/logs/ && chmod -R 777 /usr/local/mysql/logs/
yum install -y ncurses ncurses-devel
ln -s  /usr/lib64/libncurses.so.6 /usr/lib64/libncurses.so.5
ln -s  /usr/lib64/libtinfo.so.6 /usr/lib64/libtinfo.so.5
ldconfig
cp /usr/local/mysql/support-files/mysql.server /etc/init.d/mysqld
/usr/local/mysql/bin/mysqld --initialize-insecure  -uroot
cat /dev/null >/usr/local/mysql/logs/mysql.log

/etc/init.d/mysqld start
/usr/local/mysql/bin/mysqld  --user=root --explicit_defaults_for_timestamp
sudo yum install redis redis-devel -y
echo "requirepass = ecp@2024" >>/etc/redis.conf
sudo systemctl enable redis
sudo systemctl start redis
pip3 install supervisor