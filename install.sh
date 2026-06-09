#!/bin/bash

# Update package lists and upgrade existing packages
sudo apt update
sudo apt upgrade -y

# Install required packages
sudo apt install -y software-properties-common

# Add the PHP repository
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# List of all PHP 8.3 modules
php_modules=(
    php8.3-cgi
    php8.3-cli
    php8.3-common
    php8.3-curl
    php8.3-mysql
    php8.3-pgsql
    php8.3-sqlite3
    php8.3-ssh2
    php8.3-uuid
    php8.3-xml
    php8.3-yac
    php8.3-yaml
    php8.3-zip
)

# Install PHP 8.3 and all modules
sudo apt install -y php8.3 "${php_modules[@]}"



sudo mkdir /etc/overwall-node
sudo mv cron.php /etc/overwall-node
sudo mv functions.php /etc/overwall-node
sudo mv index.php /etc/overwall-node
sudo mv install.php /etc/overwall-node
echo "Data Moved."


sudo mv overwall-node-server.service /etc/systemd/system
sudo systemctl daemon-reload
sudo systemctl enable overwall-node-server.service
sudo systemctl start overwall-node-server.service



command="php -f /etc/overwall-node/cron.php"

# Check if the cron job already exists
crontab -l | grep -F "$command" > /dev/null

# Add cron job if it doesn't already exist
if [ $? -ne 0 ]; then
  # Create a new cron job entry for every minute
  (crontab -l ; echo "* * * * * $command") | crontab -
  echo "Cron job added: $command"
else
  echo "Cron job already exists: $command"
fi