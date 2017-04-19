# Getting started with Vagrant

## Installation

### Windows
* [Install Vagrant](https://www.vagrantup.com/docs/installation/).
* [Install Virtual Box](https://www.virtualbox.org/wiki/Downloads).
* Open a command prompt and navigate to _airmeedevsdir_\php-sdk\infrastructure\vagrant

### Ubuntu
* Run `sudo apt-get install vagrant`
* Enjoy the simplicity!
* Open a terminal and navigate to _airmeedevsdir_/php-sdk/infrastructure/vagrant`

## Adding SSH keys

Under `/php-sdk/infrastructure/ansible/authorized_keys`, create a new file `YOURUSERNAME.azk` containing your SSH public key (in authorized_keys format). This file will be git-ignored by default.

## Starting the server
Run the command `vagrant --port-suffix=01 up`. This will take a while to run the first time you do it as it installs various packages in a self-contained virtual environment.

Once this has completed, you will have a virtual machine with its port 22 bound to your host machine's port 2201 (or 22 + whatever you put as the value of `port-suffix` in the command, in case your port 2201 is already in use). You can SSH into this machine with the username and SSH key you supplied.

A webserver is also bound on port 8101 (or 81 + your `port-suffix`) which serves some documentation built using Sphinx.