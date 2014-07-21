#!/usr/bin/env bash

#apt-get update
apt-get install -y apache2 php5 libapache2-mod-php5 php5-mcrypt

rm -rf /var/www/html
ln -fs /vagrant /var/www/html

cat > /etc/apache2/sites-available/000-default.conf << EOF
<VirtualHost *:80>

    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html

    <Directory /var/www/html>
    AllowOverride all
    </Directory>
    
    ErrorLog /var/log/apache2/error.log
    CustomLog /var/log/apache2/access.log combined
</VirtualHost>
EOF

php5enmod mcrypt
a2enmod rewrite
service apache2 restart

curl -sS https://getcomposer.org/installer | php -- --install-dir=/vagrant
cd /vagrant
php composer.phar install