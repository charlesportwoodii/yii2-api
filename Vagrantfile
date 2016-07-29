# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

  # Use a preconfigured Vagrant box
  config.vm.box = "charlesportwoodii/php7_xenial64"
  config.vm.box_check_update = true

  # Mount the directory with sufficient privileges
  config.vm.synced_folder ".", "/var/www", 
    id: "vagrant-root",
    owner: "vagrant", 
    group: "www-data", 
    mount_options: ["dmode=775,fmode=775"]

  # Provisioning
  config.vm.provision "shell", inline: <<-SHELL, privileged: false

    # Upgrade PHP & Nginx
    echo "Upgrading web server packages"
    sudo apt-get update
    sudo apt-get install -y php7.0-fpm nginx-mainline
    sudo ldconfig

    # Update the user's path for the ~/.bin directory
    export BINDIR="$HOME/.bin"
    if [[ ! -d "${BINDIR}" ]]
    then
      # Add ~/.bin to PATH and create the ~/.bin directory
      echo "export PATH=\"\$PATH:\$HOME/.bin\"" >> /home/vagrant/.bashrc
      mkdir -p /home/vagrant/.bin
      chown -R vagrant:vagrant /home/vagrant/.bin

      # Install Composer
      php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
      php -r "if (hash('SHA384', file_get_contents('composer-setup.php')) === '7228c001f88bee97506740ef0888240bd8a760b046ee16db8f4095c0d8d525f2367663f22a46b48d072c816e7fe19959') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
      php composer-setup.php --install-dir=/home/vagrant/.bin --filename=composer
      php -r "unlink('composer-setup.php');"

      # Make sure the composer file has the right permissions on it
      chmod a+x /home/vagrant/.bin/composer
      chown -R vagrant:vagrant /home/vagrant/.bin/composer
    fi
    
    # Make composer do Parallel Downloading
    /home/vagrant/.bin/composer global require hirak/prestissimo

    # Copy the Nginx configuration and restart the web server
    echo "Copying Nginx configuration"
    sudo service nginx stop
    sudo killall nginx

    # Copy the new configuration files in
    sudo cp /var/www/config/.vagrant/http.conf /etc/nginx/conf/conf.d/http.conf
    sudo service nginx start

    # Create the database
    echo "Creating MySQL database if it is not present"
    mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS root;"

    # Copy the necessary configuration files
    echo "Copying _db and _env configuration files"
    cp /var/www/config/.vagrant/config.yml /var/www/config/config.yml

    # Update composer
    /home/vagrant/.bin/composer self-update

    # Install the website
    cd /var/www
    rm -rf /var/www/vendor
    /home/vagrant/.bin/composer install -ovn

    # Run the migration
    chmod a+x /var/www/yii
    ./yii migrate/up --interactive=0
  SHELL
end