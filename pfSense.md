# pfSense

## Déploiement d'un tunnel IPSec

- Laisser la plupart des options par défaut, juste bien choisir la même clé partagée entre les deux bouts du tunnel.

- "Local Network" et "Remote Network" = Les deux LAN des deux pfSense.

- Penser à bien corriger l'algorithme dans la P2, qui n'est pas toujours le bon et pas toujours avec la bonne longueur de clé notamment.

- Bien vérifier les octets entrés et sortis sur l'interface dans "Status" pour s'assurer du bon fonctionnement du tunnel.

## Suricata

### Insallation 

Système => Gestionnaire de paquets => Paquets disponibles => Chercher le paquet puis confirmer l'installation.

### Utilisation

Services => Suricata => Ajouter l'interface puis les règles désirées.

### Erreurs et problèmes

1) "[...] another instance is already running" à l'installation

    => Voir si il n'y a pas un "pkg" qui tourne ou un fichier .lock dans /tmp en utilisant le shell intégré.

    => Vérifier que le pfSense touche bien internet en essayant un "ping 8.8.8.8" depuis la console.

    => Si le ping ne marche pas, réassigner l'adresse WAN en variant les paramètres sur le DHCP v4 + v6 notamment.

2) Logs remplies par des erreurs liées à la checksum UDPv4


    => Désactiver les checksums dans : 
    System / Advanced / Networking / Disable Hardware Checksum Offloading



## Divers


- Passer par l'adresse du LAN pour le management sur l'interface web, c'est la meilleure pratique et la seule autorisée par défaut de toute façon.

- Pour vérifier que les réseaux internes marchent bien sans avoir le droit au ping il suffit de les pinger quand même puis faire un **"arp -a"** dedans pour vérifier que la table ARP se remplit bien pour chacun.
