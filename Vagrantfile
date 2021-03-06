# -*- mode: ruby -*-
# vi: set ft=ruby :


# Vagrantfile API/syntax version.
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  #config.vm.box = "debian/jessie64"
  config.vm.box_version = "8.6.1"
  config.vm.box = "debian/contrib-jessie64" # Box with Virtualbox Guest Additions
 
   #Dev config 
   config.vm.define "ogam_dev" do |ogam_dev|
   end
   config.vm.provider "virtualbox" do |v|
      v.memory = 3072
      v.cpus = 4
      v.name = "ogam_dev"
   end

  config.vm.network "private_network", ip: "192.168.50.4"
  config.vm.hostname = "ogam.ign.fr"
  config.vm.network :forwarded_port, host: 8081, guest: 8080,auto_correct: true
  config.vm.network :forwarded_port, host: 5433, guest: 5432,auto_correct: true
  config.vm.network :forwarded_port, host: 8000, guest: 80,auto_correct: true

   
  # disable the default root
  config.vm.synced_folder ".", "/vagrant", disabled: true
  
  # Sync the vagrant dir
  config.vm.synced_folder ".", "/vagrant/ogam", disabled: false, create:true
  
  config.vm.synced_folder "website/htdocs",'/vagrant/ogam/website/htdocs', owner:"www-data",group: "www-data",:mount_options => ["dmode=777","fmode=777"]

  
  #
  # Middleware installation
  # The following provisions are executed as "root" 
  #

  config.vm.provision "bootstrap", type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/bootstrap.sh"
 
  config.vm.provision "install_java_tomcat", type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/install_java_tomcat.sh"

  config.vm.provision "install_apache", type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/install_apache.sh"
  
  config.vm.provision "install_mapserv", type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/install_mapserv.sh"
  
  config.vm.provision "install_tilecache", type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/install_tilecache.sh"

  config.vm.provision "install_postgres", type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/install_postgres.sh"

  config.vm.provision "install_db", type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/install_db.sh"
 
  config.vm.provision "install_sencha_cmd_6", type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/install_sencha_cmd_6.sh"
  
  config.vm.provision "install_dev_tools", type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/install_dev_tools.sh"
  
  config.vm.provision "fix_var_perf", run:"always", type: "shell", inline: "localpath=$1 ; sharedpath=$2 ; /vagrant/ogam/vagrant_config/scripts/build_locale_dir.sh $localpath && mount -o bind $localpath $sharedpath", args:['/home/vagrant/ogam/var/cache','/vagrant/ogam/website/htdocs/server/ogamServer/app/cache']
  
  #
  # Application deployment
  # The following provisions are executed as "vagrant" 
  #
  config.vm.provision "install_gradle", privileged: false, type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/install_gradle.sh"
  
  config.vm.provision "install_composer_libraries", privileged: false, type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/install_composer_libraries.sh"
  
  #for windows and vboxfs (no nfs) sharing, improve perf, TODO: download (rsync-back ?) on host the vendor dir for ide ie
  config.vm.provision "move_vendor_dir", privileged:true, type: "shell", inline:"/vagrant/ogam/vagrant_config/scripts/build_locale_dir.sh $1 && mv $2/* $1", args:['/home/vagrant/ogam/vendor/', '/vagrant/ogam/website/htdocs/server/ogamServer/vendor']
  config.vm.provision "fix_vendor_perf", run:"always", type: "shell", inline: "localpath=$1 ; sharedpath=$2; mount -o bind $localpath $sharedpath", args:['/home/vagrant/ogam/vendor', '/vagrant/ogam/website/htdocs/server/ogamServer/vendor']

  config.vm.provision "build_ogam_services", privileged: false, type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/build_ogam_services.sh"
  
  config.vm.provision "build_ogam_desktop", privileged: false, type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/build_ogam_desktop.sh"

  config.vm.provision "build_ogam_server", privileged: false, type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/build_ogam_server.sh"
   
  #
  # Documentation & Code quality & Developers provisions
  # The following provisions are executed as "vagrant" and are only run when called explicitly 
  #
    
  if ARGV.include? '--provision-with'
  	config.vm.provision "run_phpdoc", privileged: false, type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/run_phpdoc.sh"
  end
  
  if ARGV.include? '--provision-with'
  	config.vm.provision "run_jsdoc", privileged: false, type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/run_jsdoc.sh"
  end
  
  if ARGV.include? '--provision-with'
  	config.vm.provision "run_phpunit", privileged: false, type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/run_phpunit.sh"
  end
  
  if ARGV.include? '--provision-with'
    config.vm.provision "run_phpcheckstyle", privileged: false, type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/run_phpcheckstyle.sh"
  end
  
  if ARGV.include? '--provision-with'
    config.vm.provision "update_metadata", type: "shell", inline: "/vagrant/ogam/vagrant_config/scripts/update_metadata.sh"
  end
  
  #
  # Always restart Apache
  #
  
  config.vm.provision "shell", inline: "service apache2 restart", run: "always"
  
  config.vm.provision "shell", inline: "echo 'Le site est pret a etre utilise sur http://192.168.50.4'", run: "always"
  
end
