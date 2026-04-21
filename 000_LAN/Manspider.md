# Manspider

## Machine individuelle

	manspider <IP> -e bat com vbs ps1 psd1 psm1 pem key rsa pub reg txt cfg conf config vbe kix wsf cmd ini -d <DOMAIN> -u <USER> -p <PASSWORD> -c pass user admin /account network login logon cred /RU mdp

## Liste de shares

	for share in `cat LISTE_SHARES.txt` ; do manspider "$share" -e bat com vbs ps1 psd1 psm1 pem key rsa pub reg txt cfg conf config vbe kix wsf cmd ini -d <DOMAIN> -u <USER> -p "<PASSWORD>" -c pass user admin /account network login logon cred /RU md ; done

## Connexion par hashes

	manspider $IP -e bat com vbs ps1 psd1 psm1 pem key rsa pub reg txt cfg conf config vbe kix wsf cmd ini -d <DOMAIN> -u <USER> -H <YYYYY> -c pass user admin /account network login logon cred /RU mdp
