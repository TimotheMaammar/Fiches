# Caido

## Mise en place sur VPS

Caido a aussi une version CLI pour pouvoir l'héberger à distance. Très utile sur un VPS pour faire tourner des scans la nuit par exemple. 

Voir https://github.com/caido/caido/releases/ pour avoir la dernière version.     
Voir https://addons.mozilla.org/fr/firefox/addon/pwnfox/ si besoin de réinstaller PwnFox.     

Mise en place : 

```
mkdir Caido
cd Caido

wget https://caido.download/releases/v0.56.0/caido-cli-v0.56.0-linux-x86_64.tar.gz

tar xvzf caido-cli-*.gz

chmod +x ./caido-cli

tmux new -s Caido

./caido-cli
```

Connexion à distance : 

```
ssh -L 9999:127.0.0.1:8080 user@vps
firefox http://localhost:9999/
```

## Tips en vrac

- Ctrl+K pour naviguer rapidement
- Pas de Collaborator natif mais le plugin officiel QuickSSRF permet de faire pareil
- Le plugin AuthMatrix est l'équivalent du Autorize de Burp
- Le plugin EvenBetter rajoute pas mal de fonctionnalités utiles

## Liens utiles

- Documentation HTTPQL : https://docs.caido.io/app/reference/httpql.html



