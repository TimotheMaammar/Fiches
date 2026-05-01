# Favicon

## Shodan

Shodan possède un outil permettant de calculer le hash d'un favicon :
- https://blog.shodan.io/deep-dive-http-favicon/

Utile pour détecter d'éventuels serveurs de préproduction ou déploiements oubliés.

Installation (Linux) :

```
curl -fsSL https://updates-static.shodan.io/tools/favscan/favscan-linux-x86_64 -o favscan
chmod +x favscan
```

Utilisation :

```
./favscan www.google.com
./favscan ./favicon.ico
[...]
```

Requête à lancer ensuite dans Shodan :

    http.favicon.hash:708578229

URL finale pour aller plus vite :
    
    https://www.shodan.io/search/report?query=http.favicon.hash%3A708578229
  
