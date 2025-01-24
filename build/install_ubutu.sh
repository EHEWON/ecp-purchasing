############################################
# Build in Golang
# Run npm run build-healthcheck-armv7 in the host first, another it will be super slow where it is building the armv7 healthcheck
# Check file: builder-go.dockerfile
#libjpeg-devel.x86_64 libjpeg.x86_64 libmemcached libmemcached-devel
#https://mirrors.tuna.tsinghua.edu.cn/remi/enterprise/9/php82/x86_64/php-imap-8.2.24-1.el9.remi.x86_64.rpm
############################################
set $pwd=pwd
sudo apt update -y
sudo apt search openssl
sudo apt install -y vim curl wget tar bzip2 unzip vim-enhanced passwd sudo sudo apt-utils hostname net-tools rsync man telnet  --allowerasing
sudo apt -y install autoconf curl freetype-dev.x86_64 freetype.x86_64  gcc gmp-dev libcurl libcurl-dev  --allowerasing
sudo apt install -y  libjpeg-dev libpng-dev sqlite-dev  autoconf automake libtool --allowerasing
sudo apt install -y libxslt1.1  libxslt-dev libxml2 libxml2-dev libssl-dev pcre pcre-dev zlib zlib-dev gcc-c++ libwebp-dev libwebp  --allowerasing
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
cd /mnt/ && tar -zxvf openresty-1.19.9.1.tar.gz &&  cd openresty-1.19.9.1 \
&& ./configure --prefix=/usr/local/openresty --with-cc-opt=-O2 --with-luajit  --with-http_gzip_static_module --with-http_realip_module \
--with-http_stub_status_module --with-http_v2_module --with-http_ssl_module --with-stream \
--with-stream_ssl_module --with-stream_ssl_preread_module \
&& gmake && gmake install
wget -O /mnt/mysql-5.7.44-el7-x86_64.tar.gz https://downloads.mysql.com/archives/get/p/23/file/mysql-5.7.44-el7-x86_64.tar.gz  --no-check-certificate \
cd /mnt/ &&  tar -zxvf mysql-5.7.44-el7-x86_64.tar.gz && cp -r mysql-5.7.44-el7-x86_64 /usr/local/mysql
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
sudo apt install -y libncursesw6 libncurses-dev
ln -s  /usr/lib64/libncurses.so.6 /usr/lib64/libncurses.so.5
ln -s  /usr/lib64/libtinfo.so.6 /usr/lib64/libtinfo.so.5
ldconfig
cp /usr/local/mysql/support-files/mysql.server /etc/init.d/mysqld
/usr/local/mysql/bin/mysqld --initialize-insecure  -uroot
cat /dev/null >/usr/local/mysql/logs/mysql.log

/etc/init.d/mysqld start
/usr/local/mysql/bin/mysqld  --user=root --explicit_defaults_for_timestamp
sudo sudo apt install redis redis-devel -y
echo "requirepass  ecp@2024" >>/etc/redis.conf
sudo systemctl enable redis
sudo systemctl start redis
pip3 install supervisor
echo -e " [Unit] \
Description=Supervisor daemon  \
[Service]  \
Type=forking  \
ExecStart=/usr/bin/supervisord -c /etc/supervisor/supervisord.conf  \
ExecStop=/usr/bin/supervisorctl -c /etc/supervisor/supervisord.conf shutdown  \
ExecReload=/usr/bin/supervisorctl -c /etc/supervisor/supervisord.conf reload  \
ExecStatus=/usr/bin/supervisorctl -c /etc/supervisor/supervisord.conf status  \
ExecRestart=/usr/bin/supervisorctl -c /etc/supervisor/supervisord.conf restart  \
KillMode=process  \
Restart=on-failure  \
RestartSec=30s  \
User=root  \
[Install]  \
WantedBy=multi-user.target" >>/usr/lib/systemd/system/supervisord.service
systemctl daemon-reload
systemctl enable supervisord.service