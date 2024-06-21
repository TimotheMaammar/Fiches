# Serveur web AWS

## Connexion SSH 

    takeown -f .\LightsailDefaultKey-eu-west-3.pem
    icacls.exe .\LightsailDefaultKey-eu-west-3.pem /reset
    icacls.exe .\LightsailDefaultKey-eu-west-3.pem /GRANT:R "tim:(R)"
    icacls.exe .\LightsailDefaultKey-eu-west-3.pem /inheritance:r
    ssh ubuntu@serveur_aws -i .\LightsailDefaultKey-eu-west-3.pem

## Docker 

    sudo apt-get update
    
    sudo apt-get install ca-certificates curl
    
    sudo install -m 0755 -d /etc/apt/keyrings
    
    sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
    
    sudo chmod a+r /etc/apt/keyrings/docker.asc

    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
      $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
      sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

    sudo apt-get update

    sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin 

    sudo docker run hello-world

## Nginx + PHP 

    sudo apt install nginx
    sudo apt-get install php8.1-fpm -y
    sudo systemctl status php8.1-fpm
    sudo systemctl is-active php8.1-fpm.service
    sudo vim /etc/nginx/sites-available/default

=> Changer le socket en “fastcgi_pass unix:/run/php/php8.1-fpm.sock"

    sudo vim /etc/nginx/nginx.conf
    sudo nginx -t
    sudo systemctl restart nginx
    sudo systemctl restart php8.1-fpm


## PostgreSQL

    sudo apt update
    sudo apt install python3-venv python3-dev libpq-dev postgresql postgresql-contrib nginx curl
    
    sudo -u postgres psql

    CREATE DATABASE bdd;
    CREATE USER admin WITH PASSWORD 'Password123';
    ALTER ROLE admin SET client_encoding TO 'utf8';
    ALTER ROLE admin SET default_transaction_isolation TO 'read committed';
    ALTER ROLE admin SET timezone TO 'UTC';

    GRANT ALL PRIVILEGES ON DATABASE bdd TO admin;
    \c ma_bdd
    CREATE TABLE users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL
    );

    INSERT INTO users (username, password) VALUES ('user1', 'password1');
    INSERT INTO users (username, password) VALUES ('user2', 'password2');


## Flask + Jinja

    pip3 install flask --ignore-installed
    pip3 install Jinja2

    mkdir templates
    vim templates/page.html
    vim run.py

    flask --app run run --host=0.0.0.0
