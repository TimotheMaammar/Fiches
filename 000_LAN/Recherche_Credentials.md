# Linux

    printenv
    history
    
    find / -name "*id_rsa*" 2>/dev/null
    find / -name "*bash_history*" 2>/dev/null
    
    grep -RniE "user|password|passwd|pass|pw|cred" / 2>/dev/null
    grep -RniE "auth|authentication|authorization|bearer|secret|token|password|pw|cred|user|username" / 2>/dev/null 
    
    find / -type f -mtime 7 -exec ls -la {} \; 2>/dev/null


# Windows

```
Get-ChildItem -Path "\\DOMAIN_FQDN\SYSVOL" -Recurse -ErrorAction SilentlyContinue -Include *.bat,*.cmd,*.com,*.vbs,*.vbe,*.ps1,*.wsf,*.kix | % {Select-String -path $_ -pattern /user,/RU,cred,pass,pw}

Get-ChildItem -Path C:\ -Include *.log -File -Recurse -ErrorAction SilentlyContinue 

Get-ChildItem -Path C:\Users -File -Recurse -ErrorAction SilentlyContinue
```

Script pour énumérer une liste de shares dont la racine n'est pas accessible :

```
# Récupération des shares : 
#    nxc smb IP -u <USER> -p '<MDP>' --shares

$filePath = ".\shares.txt"    
$names = Get-Content $filePath -Encoding UTF8

foreach ($name in $names) {
    if (Test-Path "\\DOMAINE\$name") {
        Get-ChildItem -Path "\\DOMAINE\$name" -Recurse -ErrorAction SilentlyContinue -Include *.txt,*.bat,*.cmd,*.com,*.vbs,*.vbe,*.ps1,*.wsf,*.kix | % {Select-String -path $_ -pattern /user,/RU,cred,pass,pw,mdp,Toto,Opera,opera-energie.fr,mail}
    } else {
        Write-Host "Le chemin \\DOMAINE\$name est invalide."
    }
}
```
