# Sans docker

    curl -fsSL https://raw.githubusercontent.com/tj/n/master/bin/n | bash -s lts

    node -v
    npm -v

    export PATH="/usr/local/bin:$PATH"

    mkdir n8n && cd n8n
    npm init -y
    npm install n8n

    tmux new -s N8N
    npx n8n

# Avec Docker

https://docs.docker.com/engine/install/ubuntu/

    mkdir n8n-docker && cd n8n-docker
    vim docker.sh
    chmod +x docker.sh 
    ./docker.sh
    
    docker pull n8nio/n8n
    docker run -d   --name n8n   -p 8080:5678   -e N8N_HOST=0.0.0.0   -e N8N_PORT=5678   -e N8N_PROTOCOL=http   -e N8N_SECURE_COOKIE=false    docker.n8n.io/n8nio/n8n

# Forwarding

Si la connexion au VPS déconne : 

    ssh -L 8080:127.0.0.1:8080 root@vps
