# Reconnaissance

    curl https://crt.sh/?q=domaine.com | grep domaine.com
  	amass enum -passive -d domaine.com
  	subfinder -d domaine.com
  	python3 sublist3r.py -d domaine.com
    assetfinder -subs-only domaine.com
    ffuf -u https://FUZZ.domaine.com -w /usr/share/wordlists/seclists/Discovery/DNS/subdomains-top1million-20000.txt -k

# Tri puis chaînage 

    cat domaines.txt | sort -u > domaines_clean.txt
    cat domaines_clean.txt | ./wappaGo -report -screenshot ./dump
    
# Installation si besoin 

    sudo apt install amass
    
    git clone https://github.com/aboul3la/Sublist3r.git
    cd Sublist3r
    pip install -r requirements.txt

    go install -v github.com/projectdiscovery/subfinder/v2/cmd/subfinder@latest
    go install -v github.com/tomnomnom/assetfinder@latest

# Sites utiles

- https://dnsdumpster.com/
- https://crt.sh/
- https://www.cubdomain.com/
- https://viewdns.info/
- https://web.archive.org/ 

  
