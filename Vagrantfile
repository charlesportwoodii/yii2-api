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

    # Install Docker CE
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
    sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"

    sudo apt-get update
    sudo apt-get remove php7.0-fpm php5.6-fpm disque-server -y
    sudo apt-get install -y php7.1-fpm nginx-mainline apt-transport-https ca-certificates curl docker-ce -y
    sudo ldconfig

    # Generate an ed25519 key if one doesn't exists
    if [ ! -f /home/vagrant/.ssh/id_ed25519 ]; then
      cat /dev/zero | ssh-keygen -t ed25519 -f /home/vagrant/.ssh/id_ed25519 -q -N ""
    fi

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

    # Update composer
    /home/vagrant/.bin/composer self-update

    # Install the website
    cd /var/www
    rm -rf /var/www/vendor
    /home/vagrant/.bin/composer install -ovn

    # Pull down the Disque and MailCatcher docker images
    docker pull charlesportwoodii/xenial:disque
    docker pull schickling/mailcatcher

    # Start the Disque container
    docker run -d -p 7711:7711 --name disque charlesportwoodii/xenial:disque

    # Start the MailCatcher container
    docker run -d -p 1025:1025 -p 1080:1080 --name mailcatcher schickling/mailcatcher
  SHELL
end