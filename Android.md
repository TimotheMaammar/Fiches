# Android Studio

## Lancer ADB depuis un téléphone virtuel donné 

	C:\Tools\platform-tools\adb.exe start-server

	C:\Tools\platform-tools\adb.exe devices -l
	C:\Tools\platform-tools\adb.exe -s $ID shell
	C:\Tools\platform-tools\adb.exe -s $ID shell getprop

## Récupérer des packages

	C:\Tools\platform-tools\adb.exe shell pm list packages
	C:\Tools\platform-tools\adb.exe pull /data/app/com.example.someapp-2.apk path/to/desired/destination
## Contournement de SSL Pinning avec Frida 

1) Faire un portable sur Android Studio
2) Installer le fichier APK de l'application voulue depuis https://apkpure.fr/ par exemple
3) Installer Node : https://nodejs.org/en/download
4) Installer RMS : https://github.com/m0bilesecurity/RMS-Runtime-Mobile-Security 
	=> **npm install -g rms-runtime-mobile-security**
5) Installer un serveur Frida : https://github.com/frida/frida/releases 
	=> **xz -d frida-server-X.Y.Z-android-x86_64.xz**
6) Lancer Burp et télécharger le certificat 
7) Pousser les fichiers présentés plus bas et mettre Burp en proxy
8) Lancer rms (commande "rms") puis aller sur l'URL donnée (ex : http://127.0.0.1:5491/)
9) Choisir "Android" puis le bon package à lancer
10) Choisir "Load Default Frida Scripts" puis prendre le numéro 18
11) Faire "Start RMS"

Éléments à pousser puis à exécuter avant de lancer RMS : 

	C:\Tools\platform-tools\adb.exe push C:\Tools\frida-server-16.2.1-android-x86_64 /data/local/tmp
	
	C:\Tools\platform-tools\adb.exe push C:\Tools\cert-der.crt /data/local/tmp
	
	C:\Tools\platform-tools\adb.exe shell "/data/local/tmp/frida-server-16.2.1-android-x86_64 &"


Ligne Burp à exécuter au préalable : 

	C:\Tools\platform-tools\adb.exe shell settings put global http_proxy 192.168.1.21:8080

Sur Burp, bien cocher "All interfaces" et "Support invisible proxying" (dans Request handling).

On a la possibilité de tout logger en allant dans l'onglet "API Monitor".

# Divers

## Installation de MobSF 

[https://github.com/MobSF/Mobile-Security-Framework-MobSF](https://github.com/MobSF/Mobile-Security-Framework-MobSF)

	docker pull opensecurity/mobile-security-framework-mobsf:latest
	docker run -it --rm -p 9999:8000 opensecurity/mobile-security-framework-mobsf:latest

Aller sur http://localhost:9999/login/?next=/ et entrer les credentials 

Credentials = mobsf / mobsf par défaut

## Pentest d'un device physique bridé 

### Flashing d'une tablette

1) Passer en mode Recovery et noter le numéro du modèle
2) Reboot to bootloader
3) Connecter la tablette au PC
4) Trouver une ROM sur https://samfw.com/ ou ailleurs
5) Télécharger le ZIP puis l'extraire
6) Télécharger Odin et l'exécuter 
7) Déplacer les fichiers

### Mode Recovery sur une tablette

Power + Volume Up + Home pendant 5 secondes lorsque la tablette est éteinte

Options potentiellement intéressantes pour contourner : 
- Apply update from ADB
- Apply update from SD card
- Mount /system

### Trucs utiles à télécharger

- https://termux.fr.uptodown.com/android => Terminal 
- https://f-droid.org/fr/ => Store open-source
- Meterpreter 
- Un autre navigateur que celui qui est installé pour vérifier si certains blocages ne sont pas portés par le navigateur

