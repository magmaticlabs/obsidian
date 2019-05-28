# Docker Deployment

This project ships with a simple docker environment to quickly get a working 
instance of the codebase up and running for development and testing. It is 
not intended to be used as a means of deploying a production instance of 
the application.  

## Usage  

The `obsidian` script in the `.docker` directory is used to set up the 
environment, and control the containers. 

**Prerequisites**  

 - The `docker` and `docker-compose` packages are installed on your system
 - Your user account is in the `docker` group
 - The `docker` daemon is running

**Available Commands**  

 - `./obsidian build` - Build the obsidian docker image
 - `./obsidian up`    - Bring all the containers up
 - `./obsidian down`  - Bring all the containers down
 - `./obsidian shell` - Get a shell inside the obsidian container
 - `./obsidian root`  - Get a root shell inside the obsidian container
 - `./obsidian logs`  - View output from the nginx and mariadb containers

The first time you run the `./obsidian up` command, it will generate an SSL 
cert for the nginx container. You can add the rootCA.pem cert to your 
browser's trust store to remove the untrusted cert warning.  

When you run the `./obsidian down` command, it will take all the containers 
offline, including the database storage volume, which will wipe the database. 
Be sure you don't need any of the data in the database before you bring the 
containers down. 