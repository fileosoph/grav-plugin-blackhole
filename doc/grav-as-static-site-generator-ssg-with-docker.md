# Use Grav as static site generator with a docker

To use grav as static site generator(SSG) such as jekyll do the following steps.

You have already Docker,Docker-Compose and Phpstorm installed.

Remarks: this is a draft. It works but is currently not fully explained. If you are firm with docker an grav, you will understand the draft.


## Local installation of grav

* Download grav latest stable as zip from: https://getgrav.org/download/core/grav-admin/1.6.31

`wget https://getgrav.org/download/core/grav-admin/1.6.31`

* unpack the zip archive somewhere locally.

`unzip grav-admin-v1.6.31.zip`

* navigate to new directory

`cd grav-admin`

### Create a docker-compose.yml 

* create the following docker-compose.yml

`sudo nano docker-compose.yml`

* paste the following content

```yaml
version: '3'
services:
  filosoph-grav:
    image: webdevops/php-apache-dev:7.4
    volumes:
      - $PWD:/app
    environment:
      WEB_DOCUMENT_ROOT: /app
    ports:
      - 8080:80
```

save the content with `ctrl+o` command

### start the docker-compose

* Type in your shell

`docker-compose up`

this will start your grav installation from the docker image und you will be able to open the site with 127.0.0.1:8080.

### Login into docker to work with bash.

* open a new local shell/bash 
* `docker ps`
* find out the correct NAME for your container. (in my instance it is 'grav-admin_filosoph-grav_1')
* `docker exec -it NAME` (in my instance it is 'grav-admin_filosoph-grav_1')
* go to the webdirectory width `cd /app`


### .htaccess in docker image

* We need the grav htaccess

`cp webserver-configs/htaccess.txt .htaccess`


### First run Grav in Browser

* open browser and `http://127.0.0.1:8080`
* Please create an admin-account during filling your new credentials. 
* Submit Create User

Now you are (hopefully) logged in. A successful installation is important.


### (NOT YET) Install the blackhole plugin via grav admin interface

during the fileosoph:grav-plugin-blackhole is not merged into the grav-registered plugin, use the "install the blackhole plugin via docker console"

* navigate to http://127.0.0.1:8080/admin/plugins
* press the add-button in top of the page
* find the blackhole plugin and install

### install the blackhole plugin via docker console

* take the shell where you logged in with bash looks something like root@c2584507bedb:

`cd user/plugins` // looks like root@c2584507bedb:/app/user/plugins#

`wget https://github.com/fileosoph/grav-plugin-blackhole/archive/master.zip`

`unzip master.zip ` will extract the plugin into grav-plugin-blackhole

`mv grav-plugin-blackhole-master blackhole` will rename the directory into blackhole


## Ready to generate static sites

`cd /app`

`bin/plugin blackhole generate http://localhost:8080 --output-url https://my-output-url.de --output-path ./build-my-frist-output -a -f --verbose-mode --strip-port 8080`


