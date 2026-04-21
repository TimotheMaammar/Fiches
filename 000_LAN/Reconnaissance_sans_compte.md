## SMB

	enum4linux -a -u "" -p "" <DC> && enum4linux -a -u "guest" -p "" <DC>
	smbclient -U '%' -L //<DC> && smbclient -U 'guest%' -L //<DC>
	netexec smb <IP> --users
## LDAP

	nmap -n -sV --script "ldap* and not brute" -p 389 <DC>
	ldapsearch -h <IP> -x -b "DC=<DOMAINE>,DC=<EXTENSION>" -s sub "(&(objectclass=user))"  | grep sAMAccountName: | cut -f2 -d" "

## Kerberos

	./kerbrute_linux_amd64 userenum -d <DOMAINE> --dc <DC> usernames.txt
	msf> use auxiliary/gather/kerberos_enumusers
	nmap -p 88 --script=krb5-enum-users --script-args=krb5-enum-users.realm='<DOMAINE>' <IP>
	nmap -p 88 --script=krb5-enum-users --script-args krb5-enum-users.realm='<DOMAINE>',userdb=/wordlists/usernames.txt <IP>

## RPC

	rpcdump.py <IP>
	rpcclient -U "" -N <IP>
	msf> use auxiliary/scanner/dcerpc/endpoint_mapper
	msf> use auxiliary/scanner/dcerpc/hidden
	msf> use auxiliary/scanner/dcerpc/management
	msf> use auxiliary/scanner/dcerpc/tcp_dcerpc_auditor
	
	for i in $(seq 500 1100); do
	    rpcclient -N -U "" <IP> -c "queryuser 0x$(printf '%x\n' $i)" | grep "User Name\|user_rid\|group_rid" && echo "";
	done

## Nmap depuis un résultat Netdiscover

	cat netdiscover.txt | grep -oE "\b([0-9]{1,3}\.){3}[0-9]{1,3}\b" | awk -F. 'BEGIN{ OFS="."} { print $1,$2,$3,"0/24" }' | sort -u > /tmp/netdiscover.txt
	tmux new -s nmap
	nmap -iL /tmp/netdiscover.txt -T5 -oA /audit/recon/nmap_netdiscover_ranges

