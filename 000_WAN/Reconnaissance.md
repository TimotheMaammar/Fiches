Github utile : 
- https://github.com/daffainfo/AllAboutBugBounty/tree/master

Site chinois exhaustif : 
- https://www.ddosi.org/web-attack-cheat-sheet/
- https://www.ddosi.org/web-attack-cheat-sheet-2/

# DNS

## Protocole optimisé simplifié

Voir : https://0xpatrik.com/subdomain-enumeration-2019/ 

1. Recherche initiale

		amass enum -d <DOMAIN>
		amass enum --passive -d <DOMAIN>
		amass enum --active -d <DOMAIN> -p 80,443,8080
	

2. Script Python pour générer les possibilités de sous-domaines en concaténant le domaine ciblé avec la liste CommonSpeak2 : 

		scope = '<DOMAIN>'
		wordlist = open('./commonspeak2.txt').read().split('\n')
	
		for word in wordlist:
		    if not word.strip(): 
		        continue
		    print('{}.{}\n'.format(word.strip(), scope))


3. Commande MassDNS à faire sur le résultat obtenu : 

		massdns -s 15000 -t A -o J -r /mnt/c/Tools/Wordlists/resolvers.txt --flush <DOMAIN>

## Collecte active 

Voir : https://yeswehack.github.io/blog-attachments/2021-12-subdomain-tools-review/ 

### Wordlists 

Divers : https://github.com/danielmiessler/SecLists/tree/master/Discovery/      
Résolveurs : https://github.com/blechschmidt/massdns/blob/master/lists/resolvers.txt      
Sous-domaines : https://github.com/danielmiessler/SecLists/tree/master/Discovery/DNS       
Sous-domaines : https://github.com/assetnote/commonspeak2-wordlists      
Permutations : https://gist.githubusercontent.com/six2dez/ffc2b14d283e8f8eff6ac83e20a3c4b4/     
 
### Bruteforce

	shuffledns -d domain.com -w /mnt/c/Tools/Wordlists/subdomains.txt -r /mnt/c/Tools/Wordlists/resolvers.txt
  
	puredns bruteforce /mnt/c/Tools/Wordlists/subdomains.txt domain.com -w puredns_domain.com_bf.txt -r /mnt/c/Tools/Wordlists/resolvers.txt
  
	python3 Tools/dnscan/dnscan.py -d domain.com -w /mnt/c/Tools/Wordlists/subdomains.txt -L /mnt/c/Tools/Wordlists/resolvers.txt
  
	gobuster dns -d domain.com -w /mnt/c/Tools/Wordlists/subdomains.txt -q -t 100
  
	aiodnsbrute -w /mnt/c/Tools/Wordlists/subdomains.txt -r /mnt/c/Tools/Wordlists/resolvers.txt
  
	amass enum -brute -d domain.com -rf /mnt/c/Tools/Wordlists/resolvers.txt -w /mnt/c/Tools/Wordlists/subdomains.txt
  
	cat /mnt/c/Tools/Wordlists/subdomains.txt | rusolver -d domain.com -r /mnt/c/Tools/Wordlists/resolvers.txt
  
	cat domain.com_subdomains.txt | massdns -r /mnt/c/Tools/resolvers.txt -t A -o S

### Mutations

	altdns -i subdomains.txt -w permutations.txt > output_altdns.txt
  
	DNScewl --tL subdomains.txt -p permutations.txt --level=0 --subs --no-color | tail -n +14 > output_dnscewl.txt
  
	gotator -sub subdomains.txt -perm permutations.txt -depth 1 -numbers 10 -mindup -adv -md -silent > output_gotator.txt
  
	cat subdomains.txt | dmut -d permutations.txt --save-gen > output_dmut.txt
  
	dnsgen subdomains.txt --wordlist permutations_list.txt > output_dnsgen.txt

## Collecte passive 

	amass enum -passive -d domain.com
  
	python3 sublist3r.py -d domain.com | tail -n +25
  
	crobat -s domain.com
  
	chaos -d domain.com -silent
  
	subfinder -d domain.com -all -silent
  
	assetfinder --subs-only domain.com
  
	waybackurls domain.com | unfurl -u domains
  
	gau --subs domain.com | unfurl -u domains
  
	github-subdomains -d domain.com -k -q -t .github_tokens -o result.txt
  
	findomain --quiet -t domain.com
  
	python3 oneforall.py --target domain.com --alive False --brute False --dns False --fmt json --path results/ run && cat results/domain.com.json | jq '.[] | .subdomain'

# SSL


	echo | openssl s_client -showcerts -servername <DOMAIN> -connect <DOMAIN>:443 2>/dev/null | openssl x509 -inform pem -noout -text

	curl --insecure -v https://<DOMAIN> 2>&1 | awk 'BEGIN { cert=0 } /^\* SSL connection/ { cert=1 } /^\*/ { if (cert) print }'

	nmap -p 443 --script ssl-cert <DOMAIN>

	./testssl.sh <URL>
  
# CSP Grabbing

	curl -vvI https://<DOMAIN> 2>&1 | grep security
	curl https://<DOMAIN> 2>&1 | tr ">" "\n" | grep "<meta"
	curl -vv https://<DOMAIN> 2>&1 | grep -i Content-Security

Voir aussi : https://csp-evaluator.withgoogle.com/ 

# VHosts 


	ffuf -H "Host: FUZZ.<DOMAIN>" -u https://<DOMAIN> -w /usr/share/wordlists/seclists/Discovery/DNS/subdomains-top1million-20000.txt -fs XXX

# Extensions

Extensions en vrac : 

	asp, aspx, bat, c, cfm, cgi, conf, config, css, com, dll, env, exe, hta, html, inc, ini, jhtml, js, jsa, jsp, log, mdb, nsf, pcap, pem, php, php2, php3, php4, php5, php6, php7, phar, pl, reg, rb, sh, shtml, sql, swf, txt, tmp, xml, yml, yaml, bak, backup, old, orig, audit, dat, db, dump, key, crt, htaccess, htpasswd, web.config, jsx, coffee, ts, less, scss, sass, tpl, twig, jpg, jpeg, png, gif, bmp, svg, mp3, mp4, avi, mov, zip, tar, gz, bz2, md, db

# Crawling

	go install github.com/projectdiscovery/katana/cmd/katana@latest
	katana -u https://<DOMAIN>

	go install github.com/hakluke/hakrawler@latest
	echo https://<DOMAIN> | hakrawler

	go install github.com/jaeles-project/gospider@latest
	gospider -s "https://<DOMAIN>" -o output -c 20 -d 10
	gospider -S liste_sites.txt -o output -c 20 -d 10

# Screenshots


	cat Domaines.txt | ./aquatone -out ./resultats_aquatone.txt -ports xlarge

	go install github.com/sensepost/gowitness@latest
	C:\Tools\gowitness-2.5.1-windows-amd64.exe scan –cidr 10.10.10.10/24 –threads 20
	C:\Tools\gowitness-2.5.1-windows-amd64.exe single https://www.google.com/
	C:\Tools\gowitness-2.5.1-windows-amd64.exe report generate
	C:\Tools\gowitness-2.5.1-windows-amd64.exe report list

# Technologies

	whatweb https://<DOMAIN>
	
	go install github.com/EasyRecon/wappaGo@latest
	cat domaines.txt | ./wappaGo

	amass enum -d <DOMAIN> -ipv4 -json output.json
	cat output.json | ./wappaGo -amass-input


# CGI

	nikto -C all -h https://<DOMAIN>

# Dossier .git exposé

	python3 /mnt/c/Tools/git-dumper.py https://<DOMAIN>  ./dump

	wget --mirror --convert-links --no-parent --wait=0.5 http://192.168.100.100/.git/
    /mnt/c/Tools/git-extractor.sh .git ./DUMP_GIT
    
    git log 
    git show 
    git gui 

# Smuggling

	git clone https://github.com/defparam/smuggler.git
	cd smuggler
	
	python3 C:\Tools\smuggler\smuggler.py -u https://<DOMAIN>
	cat list_of_hosts.txt | python3 C:\Tools\smuggler\smuggler.py 
