# Flipper Zero, protocoles et fréquences par catégorie d'objet

> Sources : documentation officielle Flipper (docs.flipper.net, liste des vendeurs Sub-GHz, documentation du firmware Unleashed, standards industriels publics (ISO, SIA, CEN, ITU-T).

## Clés de lecture

1. **Portée physique** : Sub-GHz = radio réelle, quelques mètres à dizaines de mètres. NFC/125kHz = couplage inductif, contact ou quelques cm. IR = ligne de vue directe, lumière.

2. **Statique vs rolling code** : un signal statique répète toujours la même trame, capturable et rejouable à l'identique. Un rolling code intègre un compteur synchronisé avec une clé partagée, chaque trame est unique, rejouer une capture ne fait rien.

3. **Read vs Emulate** : Flipper peut presque toujours *lire*. Pouvoir *émuler/rejouer* est une capacité séparée et plus limitée, un tag peut être lisible sans être clonable. Concrètement : lire, c'est juste écouter passivement ce que l'objet original transmet (comme regarder une clé sans pouvoir la copier). Émuler, c'est faire en sorte que le Flipper se fasse passer pour l'objet original face à un lecteur (comme reproduire la clé). Un badge chiffré peut très bien afficher son numéro de série à l'écran du Flipper (lecture réussie) sans que le Flipper puisse ensuite se faire passer pour ce badge auprès d'un vrai lecteur (émulation impossible ou partielle).

4. **UID-only vs authentification complète** : sur les cartes chiffrées (DESFire, iCLASS SE), Flipper ne clone que le numéro de série public, jamais le contenu chiffré. Ça ne fonctionne que si le lecteur en face vérifie uniquement l'UID sans authentification cryptographique.

5. **Écrire un clone (T5577)** : cloner un badge 125 kHz nécessite d'écrire les données sur un tag vierge réinscriptible, le T5577 étant le standard de facto. Lire seul ne suffit pas à obtenir un clone physique utilisable sans le Flipper.

6. **Firmware stock vs custom (Unleashed/RogueMaster)** : le firmware officiel refuse de rejouer tout ce qu'il classe "locked/dynamic", même reçu techniquement. Les firmwares communautaires débloquent certains comportements jugés trop sensibles par défaut, et ajoutent du décodage (TPMS, protocoles additionnels).

---

## Ouvrants résidentiels (garages, portails particuliers)

> Colonne "Firmware" : **Stock** = fonctionne avec le firmware officiel Flipper. **Unleashed** = nécessite le firmware communautaire Unleashed/RogueMaster/Momentum

| Objet | Protocole/marque | Fréquence | Comportement | Firmware |
|---|---|---|---|---|
| Came 12bit, 24bit | Came fixe | 433.92 MHz, 868 MHz | Statique, clone complet | Stock |
| Came ATOMO, TOP44R, TWIN, Space | Came rolling | 433.92 MHz, 868 MHz | Rolling, lecture seule | Stock |
| Came TWEE | Pseudo-dynamique ("New Fixed Code") | 433.92 MHz | Ni vraiment rolling ni vraiment statique, comportement intermédiaire | Unleashed |
| Nice FLO 12/24bit | Nice fixe | 433.92 MHz | Statique, clone complet | Stock |
| Nice FLOR-S, Mhouse, One, Smilo | Nice rolling | 433.92 MHz | Rolling, lecture seule | Stock |
| Chamberlain/LiftMaster Security+ (toute génération) | Rolling propriétaire | 310/315/390 MHz | Rolling, lecture seule | Stock |
| Chamberlain 7/8/9-code | Fixe DIP-switch | 390 MHz | Statique, clone complet | Stock |
| LiftMaster (DIP-switch) | Fixe DIP-switch | 310/318/390 MHz | Statique, clone complet | Stock |
| Marantec, Marantec 24 | Marantec fixe (49 bits) | 433.92 MHz, 868 MHz | Statique, clone complet | Stock |
| Hörmann HSM | Ancien modèle spécifique | 433.92 MHz | Statique, clone complet | Stock |
| Hörmann BiSecur | Rolling/AES probable | 868 MHz (non confirmé) | Support marqué "beta", pas un clone fiable | Unleashed (beta) |
| Somfy Keytis RTS, Telis RTS | Somfy RTS | 433.42 MHz | Rolling, lecture seule | Stock |
| DoorHan | Rolling propriétaire | 433.92 MHz | Rolling, lecture seule | Stock |
| FAAC RC, XT, SLH, Spa | Rolling propriétaire (KeeLoq secure) | 433.92 MHz | Rolling, lecture seule | Stock |
| Ansonic, Berner, Clemsa, Elka, Holtek HT12x | Fixe propriétaire | 433.92 MHz | Statique, clone complet | Stock |
| Holtek (base) | Fixe propriétaire | 418.00 MHz | Statique, clone complet | Stock |
| Nero Radio | Fixe propriétaire | 434.42 MHz | Statique, clone complet | Stock |
| Nero Sketch | Fixe propriétaire | 433.92 MHz | Statique, clone complet | Stock |
| Clemsa Mutancode | Rolling | 433.92 MHz, 868 MHz | Rolling, lecture seule | Stock |
| Génériques chinois DIP-switch | Protocole "Princeton" | 315/433.92/868.35 MHz | Statique, clone complet, très répandu | Stock |
| Linear, Firefly | Fixe propriétaire | 300 MHz | Statique, clone complet | Stock |
| Linear Delta 3, Linear Megacode | Fixe propriétaire | 318 MHz | Statique, clone complet | Stock |
| Alutech AT-4N | KeeLoq-based | 433.92 MHz | Rolling, lecture seule | Unleashed |
| Beninca ARC (TOGO2VA) | Dynamic AES128 (128 bits) | 433.92 MHz | Rolling, très sécurisé, lecture seule | Unleashed |
| BFT Mitto | KeeLoq-based, seed du numéro de série (64 bits) | 433.92 MHz | Rolling, lecture seule | Unleashed |
| Ditec GOL4 (+ BIXLG4/BIXLS2/BIXLP2) | Dynamic (54 bits) | 433.92 MHz | Rolling, lecture seule | Unleashed |
| Erreka | KeeLoq-based | 433.92 MHz | Rolling, lecture seule | Unleashed |
| KingGates Stylo 4K | KeeLoq-based | 433.92 MHz | Rolling, lecture seule | Unleashed |
| Roger | Fixe propriétaire | 433.92 MHz | Statique, clone complet | Unleashed |
| V2 Phoenix (Phox) | Dynamic (52 bits) | 433.92 MHz | Rolling, mode statique activable côté récepteur | Unleashed |
| Sommer (SOMloq) | KeeLoq-based | 434.42 MHz, 868.80 MHz | Rolling, lecture seule | Unleashed |
| Jarolift | KeeLoq-based (volets roulants) | 433.92 MHz | Rolling, lecture seule | Unleashed |
| Dooya | Fixe (volets roulants) | 433.92 MHz | Statique, clone complet | Unleashed |
| Power Smart | Fixe (volets/stores) | 433.92 MHz | Statique, clone complet | Unleashed |
| Prastel | Statique (25/42 bits) | 433.92 MHz, 868 MHz | Statique, clone complet | Unleashed |
| Airforce | Statique (18 bits) | 433.92 MHz, 868 MHz | Statique, clone complet | Unleashed |
| Magellan | Rolling propriétaire (barrière) | 433.92 MHz | Rolling, lecture seule | Unleashed |

## Ouvrants collectifs/pro (immeubles, entreprises)

| Objet | Protocole | Fréquence/support | Comportement |
|---|---|---|---|
| Digicode/interphone à télécommande | Souvent Princeton ou fixe propriétaire | 433.92 MHz | Statique fréquent, clone possible |
| Barrière parking souterrain, badge de proximité | RFID 125 kHz ou NFC 13.56 MHz | Contact/quelques cm | Voir tableau Badges |
| Barrière péage autoroute | DSRC dédié, standard CEN/ETSI EN 300 674 | 5.8 GHz (Europe) | Hors de portée totale du Flipper |
| Portique/tourniquet entreprise | Wiegand, standard normalisé uniquement en 26-bit (SIA AC-01), formats 34/37-bit variables par fabricant | Bus filaire D0/D1, pas de fréquence radio | Module GPIO + accès physique au câblage requis |

## Badges et cartes (NFC/RFID)

| Objet | Puce | Fréquence | Comportement | Firmware |
|---|---|---|---|---|
| Badge EM400x/410x/420x | EM41xx | 125 kHz | Clone complet | Stock |
| Badge HID Prox | HID | 125 kHz | Clone complet | Stock |
| Badge Indala, AWID, Viking, Jablotron, Paradox, PAC Stanley, Keri, Gallagher, NexWatch, Farpointe Pyramid | Divers propriétaires | 125 kHz | Clone complet | Stock |
| Badge Securakey, Guardall G-Prox II, Electra, HID Ext | Divers propriétaires | 125 kHz | Clone complet, généralement clonable sur T5577 tant qu'il n'y a pas de cryptographie | Unleashed |
| Puce animale (vétérinaire) | FDX-B, ISO 11784/11785 | 134.2 kHz | Lecture seule, pas d'émulation | Stock |
| Puce animale (variante) | FDX-A / FECAVA | ~125 kHz (non recoupé précisément) | Lecture seule | Unleashed |
| Badge bureau standard | Mifare Classic 1K/4K | 13.56 MHz | Clone total si clés faibles/défaut | Stock |
| Badge bureau récent | Mifare Ultralight/NTAG21x | 13.56 MHz | Read/write souvent possible | Stock |
| Badge sécurisé entreprise | Mifare DESFire, iCLASS SE/Seos | 13.56 MHz | Émulation UID seule | Stock |
| Carte transport | Calypso ou Mifare DESFire | 13.56 MHz | Lecture publique seule | Stock |
| Carte FeliCa (transport Japon/Asie) | FeliCa | 13.56 MHz | Support partiel, surtout lecture | Stock |
| Carte bancaire sans contact | EMV | 13.56 MHz | Lecture publique, pas de clonage exploitable | Stock |
| Passeport biométrique | ICAO 9303, BAC/PACE | 13.56 MHz | Illisible sans clé de session MRZ | Stock |
| Clé/interphone à pastille | iButton Dallas (DS199x, DS1971), CYFRAL, Metakom, TM2004, RW1990 | Contact | Clone complet | Stock |

## Véhicules

| Objet | Protocole | Fréquence | Comportement |
|---|---|---|---|
| Clé voiture ancienne (avant ~2000) | Fixe propriétaire | 433/315 MHz | Read + replay sur les tout premiers modèles |
| Clé voiture 2000-2010+ | KeeLoq rolling ou équivalent constructeur | 433/315 MHz | Read seul |
| Transpondeur antidémarrage | RFID 125 kHz propriétaire | 125 kHz | Read UID seul |
| TPMS (pression pneus) | Propriétaire constructeur | 315 MHz (Amérique du Nord), 433 MHz (Europe) | Read/sniff en stock, décodage plus poussé sur firmware custom |
| Keyless entry passive | LF activation + UHF réponse | 125 kHz + 433 MHz | Hors de portée pratique, attaque relais nécessite deux appareils synchronisés |

## Infrarouge

| Objet | Protocole natif Flipper | Fréquence porteuse |
|---|---|---|
| TV, box, ampli, lecteur | NEC family, Kaseikyo, RCA, RC5, RC6, Samsung, SIRC | ~38 kHz |
| Climatiseur | Souvent hors standard, trame d'état complète | ~38 kHz, Learn requis |
| Projecteur salle de réunion | Généralement NEC | ~38 kHz |

## Alarmes et capteurs sans fil résidentiels

| Objet | Protocole | Fréquence | Comportement | Firmware |
|---|---|---|---|---|
| Détecteur mouvement/ouverture (kit alarme grand public) | OOK fixe le plus souvent | 433/868 MHz | Read/sniff fréquent, replay dépend du modèle | Stock |
| Sirène extérieure sans fil | OOK fixe | 433 MHz | Read + replay souvent possible | Stock |
| Sonnette sans fil non connectée | Princeton ou équivalent fixe | 433 MHz | Read + replay quasi systématique | Stock |
| Station météo (Oregon Scientific, Acurite) | OOK décodé nativement par le firmware | 433/868 MHz | Read/decode direct | Stock |
| Alarme de vélo | Hollarm, GangQi | 433.92 MHz | Statique, clone complet | Unleashed |
| Capteurs et sonnettes | Honeywell, Honeywell WDB | 433.92 MHz | Variable selon modèle | Unleashed |

## Domotique/IoT connecté

| Objet | Protocole | Fréquence | Comportement | Firmware |
|---|---|---|---|---|
| Prise/interrupteur sans fil grand public | Intertechno V3, ou OOK fixe/semi-fixe générique | 433 MHz | Read + replay fréquent sur l'entrée de gamme | Stock/Unleashed selon marque |
| LED RGB sans fil | Feron | 433.92 MHz | Read + replay | Unleashed |
| Générique chinois (variantes) | SMC5326, Hay21 | 433.92 MHz | Statique, clone complet | Unleashed |
| Ampoule/objet Zigbee grand public | Zigbee, 802.15.4 | 2.4 GHz (usage principal) | Hors de portée native du Flipper | N/A |
| Compteur communicant/capteur industriel Zigbee | Zigbee, 802.15.4 sub-GHz | 902-928 MHz (Amérique du Nord/Australie), 868-870 MHz (Europe) | Hors de portée native du Flipper | N/A |
| Objet Z-Wave (Europe) | Z-Wave, ITU-T G.9959 | 868.42 MHz | Hors de portée native | N/A |
| Objet Z-Wave (US/CA/MX) | Z-Wave | 908.42 MHz | Hors de portée native | N/A |
| Objet Z-Wave (AU/NZ) | Z-Wave | 921.4 MHz | Hors de portée native | N/A |
| Tracker/cadenas BLE | BLE GATT | 2.4 GHz | Scan/identification basique via l'app, pas un vrai outil d'exploitation BLE | N/A |

## Filaire (module GPIO requis)

| Bus | Usage typique |
|---|---|
| UART | Debug console, extraction de logs boot |
| I2C | Lecture de puces mémoire, capteurs |
| SPI | Extraction/reprogrammation de flash externe |
| Wiegand | Sniffing/replay d'un badge déjà lu par un lecteur légitime, accès physique au câblage requis |
