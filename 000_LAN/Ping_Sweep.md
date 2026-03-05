# Linux

    for i in {1..254} ; do (ping -c 1 10.10.10.$i | grep "bytes from" &) ; done

    for i in {1..254} ; do for j in {1..254} ; do (ping -c 1 10.10.$i.$j | grep "bytes from" &) ; done

    for i in $(cat wordlist.txt); do (ping -c 1 "$i.domaine.local" 2>/dev/null | grep "bytes from" &) ; done

    cat resultats.txt | awk '{print $4}' | tr -d ":"

    nmap -vv -iL ping_sweep.txt

# Windows 

## Powershell 

    1..254 | % {ping -n 1 -w 100 10.10.10.$_} | Select-String ttl

    1..254 | % { $i=$_; 1..254 | % { $j=$_; if (Test-Connection -ComputerName "10.10.$i.$j" -Count 1 -Quiet) { "10.10.$i.$j OK" } } }
