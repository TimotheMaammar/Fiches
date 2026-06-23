# Placement d'un shellcode 

Petites notes pour l'OSED.

Deux angles de base pour le placement d'un shellcode en mémoire :

- Où la charge va résider en mémoire ? Est-ce qu'on connaît l'adresse ?
- Comment amener le flot d'exécution sur la charge ? Comment influencer EIP ?

## Rappels rapides

- push X   →  ESP -= 4 ; [ESP] = X           (écrit, descend)
- pop  R   →  R = [ESP] ; ESP += 4           (lit, remonte)
- call X   →  push EIP_suivant ; EIP = X  
- ret      →  pop EIP

call = push + jmp = Empilement de l'adresse de retour
ret = pop eip
leave = mov esp, ebp ; pop ebp = Téléportation de ESP sur EBP et restauration du EBP de l'appelant



## 1) Endroit

### a) Sur la stack (après EIP)

Shellcode posé juste après l'adresse de retour. Très simple mais place limitée par la taille du buffer. C'est plutôt le choix par défaut tant qu'on n'est pas contraint par un manque de place notamment.

Façons d'atteindre cette zone :

- Instructions comme "jmp esp" (OU "call esp" OU "push esp ; ret")
- Saut direct vers l'adresse si elle est connue, stable, et sans mauvais caractères (en la mettant dans EIP directement)

### b) Sur la stack (avant EIP)

Grande zone avant EIP, ou fragment plus loin dans ce même buffer.
Quand la place après l'adresse de retour est trop courte, le buffer qu'on a rempli pour atteindre EIP offre aussi beaucoup d'espace.

Façons d'atteindre cette zone :

- Saut arrière (jmp $-X) ou near jmp depuis la zone après EIP
- Stack adjust (add/sub esp, X ; jmp esp) pour enjamber une zone polluée
- Saut vers un registre qui pointe déjà dans le buffer

### c) Code cave

Code cave = Suite d'octets inutilisés dans un module.

À utiliser quand la stack ne convient pas : trop peu de place, zone écrasée ou instable, besoin d'une adresse fixe et fiable que la stack ne garantit pas. Sert souvent à loger un egghunter ou un petit stub dans un module sans ASLR. Suppose cependant un moyen d'y écrire la charge (write primitive, WriteProcessMemory, etc.).

Octets typiques d'un code cave :

- 00 : zone non initialisée, padding de fin de section
- CC : instruction int3 (interruption), padding inséré entre des fonctions par le compilateur
- 90 : instruction NOP, padding d'alignement

Repérage :

- Recherche dans WinDbg : `s -b 0x10000000 L0x50000 00 00 00 00 00 [...]`
- Parsing des headers avec "!dh" (souvent pour aller chercher le padding de fin des sections exécutables)

Un code cave en .data est inscriptible (RW) ; un cave en .text ne l'est pas (RX), il faut un autre moyen d'y écrire.

Façons d'atteindre cette zone :

- Saut direct vers son adresse fixe (RVA constante => offset depuis la base du module)

- Via un trampoline si l'adresse brute contient des mauvais caractères

  

### d) Zone allouée RWX (souvent avec VirtualAlloc)

Cas où aucun emplacement existant ne convient : payload trop gros pour la stack ou un code cave, zone d'arrivée contrainte ou polluée, ou simplement besoin d'une mémoire vierge et fiable bien à soi.

On obtient une mémoire neuve avec VirtualAlloc(), généralement grâce à du ROP, et on y recopie le shellcode.

Façons d'atteindre cette zone :

- Saut vers l'adresse retournée par l'allocation
- Souvent depuis la chaîne ROP, après un stack pivot

### e) À une adresse inconnue (Egghunter)

Cas où l'on parvient à écrire la charge en mémoire, mais sans contrôler ni prédire son adresse (copie par l'application, allocation non déterministe, etc.). La charge est posée dans un endroit valide mais imprévisible.

Façon d'atteindre cette zone :

- Egghunter : un stub balaie la mémoire et saute sur le tag placé devant la charge

  

### f) Réception par le réseau (socket reuse)

Cas d'un service réseau où la place dans le buffer est trop petite pour tout le shellcode, et où l'on veut éviter d'ouvrir une nouvelle connexion.

Un stub minuscule réutilise la socket déjà ouverte par le client pour recevoir un second stage bien plus gros. Seul le petit stub doit tenir dans la place contrainte ; le vrai payload arrive ensuite par le canal déjà établi. 

Il y a pas mal de contraintes mais les principales sont que le le bug doit être atteint via notre connexion de base (pas par un autre canal), et qu'il faut ensuite réussir à retrouver le handle de la socket.



### g) Buffer global / statique (.data / .bss)

La charge est posée dans une variable globale ou statique du programme : un tableau global vulnérable, un buffer statique, etc. Son adresse est fixe dans l'image du module (RVA constante). Alternative à la stack quand on veut une adresse stable sans dépendre d'un code cave.

Différence avec le code cave : ici ce sont des octets réellement utilisés (une vraie variable du programme), pas du padding inutilisé.

Trois manières d'arriver à ce résultat : 

- Overflow d'un buffer global (situé dans .data ou .bss par exemple)
- Le programme copie un input dans une variable globale par design (cas le plus réaliste)

- Utilisation d'une write primitive



Pour atteindre cette zone il faut simplement un saut vers l'adresse, ou éventuellement un trampoline en cas de mauvais caractères dans l'adresse. En revanche, on n'a pas accès à toutes les techniques de rebond liées à la stack, parce que rien ne pointe vers une variable globale par défaut comme ESP le ferait pour la stack.



### h) Heap (spray)

Cas où l'on ne trouve pas directement notre shellcode mais où le programme fait des allocations sur le heap que l'on peut contrôler (JavaScript, parsing d'objets, etc.). C'est une approche probabiliste basée sur le fait d'arroser beaucoup de zones.

Remplir le heap de NOP avant le shellcode (NOP sled) pour qu'une adresse prévisible retombe dans le sled (ex : 0x0c0c0c0c).     

Le spray ne détourne rien tout seul. Il faut une autre primitive pour rediriger EIP vers 0x0c0c0c0c.      

Façons d'atteindre cette zone :

- Rediriger EIP vers une adresse devinée du sled

- Typiquement via un pointer hijack (souvent une vtable corrompue via un UAF par exemple)

  


## 2) Prise de contrôle d'EIP

### a) JMP ESP

**Écriture directe** de l'adresse de retour, chargée dans EIP au moment du `ret`

Scénario classique et facile où l'exécution arrive jusqu'à l'épilogue de la fonction vulnérable.     

Trampoline par défaut. ESP a une propriété garantie : juste après le `ret`, il pointe toujours sur les octets qui suivent l'adresse de retour. Si la charge est posée là, inutile d'inspecter quoi que ce soit, on sait qu'ESP convient. On place donc à l'adresse de retour l'adresse d'un `jmp esp` (module fixe, sans ASLR, sans mauvais caractères), et il rebondit sur la charge.

Trois opcodes équivalents pour le rebond sur ESP, à départager selon les mauvais caractères :

- `jmp esp` (FF E4) : le plus propre, ne touche à rien
- `call esp` (FF D4) : empile 4 octets sous la charge (ESP = ESP - 4)
- `push esp ; ret` (54 C3) : ESP au final inchangé

### b) JMP / CALL vers registre

**Écriture directe** de l'adresse de retour (on y met l'adresse d'un trampoline `jmp/call <reg>` ).

Même principe que `jmp esp`, mais quand ESP ne pointe pas la charge alors qu'un autre registre, lui, tombe dedans. On inspecte les registres au moment du crash, on repère celui qui pointe dans nos données (EAX, EBX, EDI…), et on saute via le trampoline correspondant.

- Direct : `jmp <reg>`, `call <reg>`, `push <reg> ; ret`
- Indirect : `jmp [reg+offset]` si le registre pointe non pas le buffer, mais un pointeur vers le buffer (double déréférencement)

### c) Sauts pour gagner de la place

**Écriture directe** de l'adresse de retour (un trampoline, puis un petit saut de repositionnement).

Quand la zone après l'adresse de retour est trop courte pour tout le shellcode, on n'y loge qu'un petit saut qui repositionne l'exécution vers une zone plus grande (souvent avant EIP, cf. 1b).

- Saut arrière : `jmp $-X` (EB xx), saut court limité à ±127 octets, vers le début du buffer
- Near jmp : `E9 xx xx xx xx` pour les distances au-delà de 127 octets
- Stack adjust : `add/sub esp, X ; jmp esp` pour décaler ESP et enjamber une zone polluée

### d) Stack pivot

**Écriture directe** de l'adresse de retour (on y met l'adresse d'un gadget de pivot).

Au lieu de sauter dans un buffer, on déplace ESP lui-même vers une grande zone contrôlée (heap, autre buffer, allocation) : la suite de l'exécution (chaîne de `ret` / ROP) s'y déroule comme si c'était la nouvelle stack. `mov esp, X` (ou un `xchg`, un `pop esp`…) téléporte ESP sur la zone ; aucune borne, le CPU fait aveuglément confiance à ESP. C'est le `; ret` qui suit le gadget qui embraye la chaîne ROP.

Indispensable quand l'overflow ne donne que quelques octets de stack mais qu'on maîtrise une grande zone ailleurs dont l'adresse est dans un registre. C'est souvent le préalable à l'étape d'exécution sous DEP.

Gadgets :

- `xchg eax, esp ; ret`
- `mov esp, eax ; ret`
- `pop esp ; ret`
- `leave ; ret` (= `mov esp, ebp ; pop ebp ; ret`, pratique si on contrôle EBP)
- `add esp, X ; ret` (mini-pivot : on reste sur la stack, on enjambe juste du junk)

### e) SEH overwrite

**Écriture indirecte** : on écrase le handler SEH, pas l'adresse de retour. EIP ne bascule que plus tard, au traitement de l'exception.

La SEH chain est une liste chaînée de gestionnaires d'exception sur la stack. On écrase un enregistrement (nSEH + Handler) ; en provoquant une exception, Windows appelle notre Handler. On le pointe sur un `pop ; pop ; ret` qui retombe sur nSEH (qu'on contrôle), où un saut court mène au shellcode.

- Handler → `pop ; pop ; ret` (dans un module sans SafeSEH/ASLR)
- nSEH → saut court vers le shellcode
- puis on déclenche l'exception

S'utilise quand l'overflow déclenche une exception avant d'atteindre le `ret`, ou quand un canary protège l'adresse de retour (le SEH overwrite le contourne). Bloqué par SafeSEH et SEHOP (vérification de l'intégrité de la chaîne).

### f) Egghunter

**Écriture indirecte** : le stub de l'egghunter est lui-même atteint par une autre technique, puis c'est lui qui saute sur la charge.

Pertinent quand la charge est en mémoire à une adresse imprévisible. Lent par nature puisqu'il scanne la mémoire ; le stub se loge souvent dans un code cave.

### g) Pointer hijack

**Écriture indirecte** : on écrase un pointeur de fonction (pas l'adresse de retour) que le programme appellera plus tard. EIP ne bascule qu'à ce moment, parfois bien après l'écriture.

Suppose une write primitive visant une adresse précise (write-what-where), pas un simple débordement linéaire : il faut viser pile l'emplacement du pointeur. Trois couches à distinguer : le **bug** (use-after-free, type confusion, écriture arbitraire), la **cible** corrompue, et la **technique** (pointer hijack). Cibles classiques :

- IAT/GOT : on remplace l'entrée d'un import ; le prochain appel à l'API saute chez nous
- Vtable : on corrompt le pointeur de vtable d'un objet C++ ; le prochain appel virtuel est détourné
- Callback : pointeur de callback (timer, handler, fonction enregistrée) que le programme invoquera

Souvent couplé au heap spray (navigateur) : le spray garantit que l'adresse pointée contient la charge.

### h) Partial overwrite

**Écriture directe mais partielle** de l'adresse de retour (seulement les octets de poids faible).

On n'écrase que les octets de poids faible de l'adresse de retour (ou d'un pointeur sauvegardé) pour rediriger l'exécution dans une fenêtre proche qu'on contrôle, sans avoir à fournir une adresse complète. Utile quand on ne maîtrise pas les octets hauts (ASLR partiel) mais que la cible est proche de la valeur d'origine.

Ça marche parce que l'ASLR randomise les octets hauts mais pas les 12 bits de poids faible (offset dans la page, déterministe) : 1 octet déplace dans la même page, 2 octets dans une fenêtre de 64 Ko. Portée forcément réduite.

### i) Saut direct vers une adresse en dur

**Écriture directe** de l'adresse de retour (l'adresse brute du shellcode).

On écrase l'adresse de retour avec l'adresse brute du shellcode (comme une adresse de stack relevée au debugging). Pas de trampoline : le `ret` pope cette adresse dans EIP, qui retombe pile sur la charge. À utiliser quand l'adresse du shellcode est connue ET stable (pas d'ASLR sur la zone, conditions reproductibles). C'est l'ancêtre du `jmp esp`. Fragile en pratique : l'adresse de stack varie selon l'environnement (taille des variables d'environnement, etc.), et elle contient souvent un null byte de poids fort qui casse une copie de chaîne, d'où la préférence pour `jmp esp`.


## RÉSUMÉ

	1)  Place après EIP + trampoline              => Stack après EIP + JMP ESP / JMP reg
	2)  Peu de place, grande zone à côté          => Stack avant EIP + saut arrière / near jmp
	3)  Besoin d'une adresse fixe et fiable       => Code cave
	4)  Besoin d'une zone propre et spacieuse     => Zone allouée RWX
	5)  Charge en mémoire, adresse inconnue       => Egghunter
	6)  Mini-stack, grande zone ailleurs          => Stack pivot
	7)  Exception avant l'adresse de retour       => SEH overwrite
	8)  Service réseau, place minuscule           => Socket reuse
	9)  Buffer global débordable, adresse stable  => Buffer global / statique
	10) Write-what-where sur un pointeur          => Pointer hijack
	11) Octets hauts non maîtrisés, cible proche  => Partial overwrite
	12) Allocations répétées contrôlées           => Heap spray
	13) Adresse du shellcode connue et stable     => Saut direct
	14) Environnement influençable (local)        => Bloc d'environnement
