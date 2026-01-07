#!/bin/bash

# Update package lists and upgrade existing packages
sudo apt update
sudo apt upgrade -y

# Install required packages
sudo apt install -y software-properties-common

# Add the PHP repository
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# List of all PHP 8.1 modules
php_modules=(
    php8.1-cgi
    php8.1-cli
    php8.1-common
    php8.1-curl
    php8.1-mysql
    php8.1-pgsql
    php8.1-sqlite3
    php8.1-ssh2
    php8.1-uuid
    php8.1-xml
    php8.1-yac
    php8.1-yaml
    php8.1-zip
)

# Install PHP 8.1 and all modules
sudo apt install -y php8.1 "${php_modules[@]}"



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