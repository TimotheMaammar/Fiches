# Zalgo

Zalgo est un effet visuel obtenu en empilant des "combining marks" Unicode sur des lettres normales. Le texte de base reste de l'ASCII (donc les 128 premiers codepoints de l'Unicode en somme) et ce sont juste les diacritiques empilés par-dessus qui le font déborder. Unicode ne limite pas le nombre de marques empilables sur un caractère, donc on peut en mettre des dizaines, et un seul caractère visible peut peser des centaines de codepoints / octets. Le nom Zalgo vient juste d'un meme de 2009.

## Liste des marques et générateurs

U+0300 => U+036F - Combining Diacritical Marks  - Bloc de base (accents, cédilles, etc.)     
U+1AB0 => U+1AFF - Combining Diacritical Marks Extended - Marques additionnelles     
U+1DC0 => U+1DFF - Combining Diacritical Marks Supplement - Marques additionnelles     
U+20D0 => U+20FF - Combining Diacritical Marks for Symbols - Marques autour de symboles      

Générateurs : 
- https://7h30th3r0n3.fr/ 
- https://lingojam.com/ZalgoText
- https://zalgo.org/

One-liner Python : 

    python -c "import random;b=[chr(c) for c in range(0x300,0x370)];print(''.join(ch+''.join(random.choice(b) for _ in range(50)) for ch in 'admin'))"

## Intérêt en sécurité

C'est principalement un payload de fuzzing, et de détection qu'il y a un truc louche dans le backend. Le décalage à exploiter, ce sont les trois mesures distinctes d'une même string, qui ne sont jamais égales sur du Zalgo :

- Graphèmes : ce que voit l'humain ("z̬̦͠" = 1). C'est ce que compte un "maximum 10 caractères" sur un formulaire par exemple.
- Codepoints : ce que compte la plupart des len() / .length (1 base + N marques).
- Octets (UTF-8) : ce que stocke la base de données et ce que fait transiter le réseau. Une marque pèse 2 octets donc 50 marques pèsent ~100 octets pour 1 caractère affiché. 

Les bugs viennent des désaccords entre ces couches :

- Validation : les marques ne sont ni retirées ni plafonnées, elles passent telles quelles.
- Longueur : limite comptée en graphèmes mais appliquée en codepoints/octets => troncature au milieu d'une séquence => corruption voire crash.
- Stockage : désaccord caractères vs octets => troncature silencieuse en base.
- Affichage : débordement vertical, rendu cassé, etc.

## Problème de la normalisation

La normalisation est une fusion par table de correspondance, pas une suppression. Elle ne peut fusionner [base + marque] en un seul caractère que si Unicode définit un caractère précomposé pour cette combinaison exacte. Il existe un 'é' précomposé par exemple, mais il n'existe aucun caractère précomposé pour un 'a' avec 50 marques au-dessus. Unicode n'a pas ce codepoint.

La normalisation Unicode est la transformation d'une string vers une forme canonique pour
pouvoir comparer / stocker proprement. Deux formes reviennent souvent :

- NFC : recomposition [base + accent] vers la forme précomposée la plus courte (`e` + ◌́ → `é`).
- NFKC : pareil mais avec remplacement des caractères "de compatibilité" par leur équivalent (exposant ² => 2).

NFC et NFKC ne nettoient pas le Zalgo, il survit aux deux en étant au pire un peu réordonné. C'est pour cela que le Zalgo passe les filtres et que la normalisation est une fausse amie.

La question utile en test n'est donc pas "Est-ce normalisé ?" mais plutôt "Est-ce filtré par
catégorie Unicode ?".

## Impacts

Du plus grave au moins grave :

- Contournement de filtre / WAF : si le filtre matche la signature avant normalisation et que le backend strippe/normalise après, des marques insérées dans la charge cassent la signature puis sont retirées en aval => le payload se réassemble (`<scr̈ipt>` passe le filtre et redevient `<script>`). Sert à smuggler une autre injection (XSS, SQLi, etc.).

- Troncature destructive : couper de l'UTF-8 sur une frontière d'octet (et non de codepoint) produit des octets invalides => exception en aval, log corrompu, JSON malformé.

- Débordement de stockage : VARCHAR(255) compté en octets mais validation en caractères => insertion qui échoue ou tronque en silence, désynchronisation entre deux services.

- ReDoS : un validateur naïf sur les marques peut partir en backtracking exponentiel.

- DoS de rendu ("Zalgo bomb") : un champ réaffiché en boucle (liste de 1000 lignes, feed, etc.) peut figer un client ou un moteur de layout. Voir le bug "Effective Power" bien connu sur iOS.

- Pollution de pipeline texte : réémission du champ dans un export CSV, un PDF, un email, un webhook, ou des logs (brouillage du parsing SIEM).

- Affichage cassé : débordement vertical, rendu illisible.

## Remédiations

Correction :

- Filtrer par catégorie Unicode, pas par normalisation : retirer ou limiter les marques `Mn` (Mark, nonspacing) et `Me` (Mark, enclosing) => `''.join(c for c in s if unicodedata.category(c) not in ('Mn','Me'))`

- Plafonner les marques par cluster : 1-2 diacritiques max par caractère de base (suffisant pour les langues réelles, tue l'empilement).

- Compter en clusters de graphèmes pour les limites de longueur, valider en octets pour le stockage, tronquer sur des frontières de codepoint (et jamais d'octet).

- Normaliser en NFC quand même (hygiène générale), sans croire que cela suffit.

Détection :

- Ratio marques combinantes / caractères de base au-dessus d'un seuil (ex. > 1) = signal trivial à logger.
- Présence de codepoints `Mn`/`Me` en série dans un champ qui n'est pas censé en contenir.









