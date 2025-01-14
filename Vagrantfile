Vagrant.configure("2") do |config|
  config.vm.box = "bento/ubuntu-22.04"

  # Manager VM configuration
  config.vm.define "ubuntu" do |ubuntu|
    ubuntu.vm.network "private_network", ip: "192.168.33.10"

    # Syncing the folder using rsync
    ubuntu.vm.synced_folder "E:/development/admin/api/vendor", "/home/vagrant/vendor"

    # Additional VM configurations
    ubuntu.vm.provider "virtualbox" do |vb|
      # Set the amount of memory and CPU for the VM
      vb.memory = "1024"
      vb.cpus = 2
    end

    # Provisioning configuration
    ubuntu.vm.provision "shell", inline: <<-SHELL
      # Update and install necessary packages
      sudo apt-get update
      sudo apt-get install -y build-essential
      # Add any other provisioning scripts as needed
    SHELL

    # Enabling SSH for remote access
    ubuntu.ssh.username = "vagrant"
    ubuntu.ssh.password = "vagrant"
    ubuntu.ssh.private_key_path = "~/.vagrant.d/insecure_private_key"
  end
end
