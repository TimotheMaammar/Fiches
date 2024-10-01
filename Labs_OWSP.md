# lab.wifichallenge.com

Téléchargement de la VM : https://drive.proton.me/urls/Q4WPB23W7R#Qk4nxMH8Q4oQ

## Challenges 

### Reconnaissance

#### Lister les clients et voir le canal de "wifi-global"

Capturer tous les paquets du réseau :

    sudo airmon-ng check kill
    sudo airmon-ng start wlan0
    
    airodump-ng wlan0mon -w scan --manufacturer --wps --band abg
    # --band abg permet de scanner 2,4Ghz et 5Ghz
    
Isoler le canal 44 si besoin : 

    airodump-ng wlan0mon -w scan --manufacturer --wps --band abg -c44


#### Obtenir l'adresse MAC du client "wifi-IT"

On capture les paquets comme avant puis on isole le canal 11 : 

    airodump-ng wlan0mon -w recon --manufacturer --wps --band abg
    airodump-ng wlan0mon -w recon --manufacturer --wps --band abg -c11
    
Création d'une base de données locale avec les résultats de la capture : 
    
    python3 ~/tools/wifi_db/wifi_db.py -d wifi.db ./recon*
    sqlitebrowser wifi.db
    
Il n'y a ensuite plus qu'à chercher le point d'accès voulu dans les différentes tables.

#### Trouver le probe de 78:C1:A7:BF:72:46

Même démarche qu'avant : 

    sudo airmon-ng check kill
    sudo airmon-ng start wlan0
    airodump-ng wlan0mon -w recon --manufacturer --wps --band abg
    python3 ~/tools/wifi_db/wifi_db.py -d wifi.db ./recon*
    sqlitebrowser wifi.db
    
#### Donner le ESSID du point d'accès caché 

On sait que l'adresse MAC du réseau est F0:9F:C2:6A:88:26

Identifier le canal du réseau :

    airodump-ng wlan0mon

Changement de canal et bruteforcing des noms avec mdk4 :

    iwconfig wlan0mon channel 11
    
    cat ~/rockyou-top100000.txt | awk '{print "wifi-" $1}' > ~/wifi-rockyou.txt
    
    mdk4 wlan0mon p -t F0:9F:C2:6A:88:26 -f ~/wifi-rockyou.txt
    


    

    
