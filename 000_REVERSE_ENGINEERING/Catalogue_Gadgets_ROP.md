Petit catalogue de gadgets ROP utiles pour l'OSED.

# Deux cas de figure

Le niveau de complexité dépend entièrement d'une question : les valeurs à poser (adresse de la fonction, arguments) contiennent-elles des mauvais caractères ?

## Cas 1 : valeurs null-free (littéraux directs)

Si l'adresse de la fonction et tous les arguments sont sans mauvais caractères, ils sont placés **directement en littéral** dans le skeleton, sur la pile. Puis un pivot fait pointer ESP dessus et l'appel se déclenche.

- Pas de construction de valeur dans EAX
- Pas de pointeur d'écriture ESI
- Pas de calcul au runtime
=> Seuls des gadgets de pivot sont nécessaires.

## Cas 2 : valeurs avec mauvais caractères (skeleton-patching)

Si une valeur contient un octet interdit, elle ne peut pas être placée en littéral. Elle est alors **construite dans un registre** (sans bad char), puis **écrite en mémoire** dans le slot correspondant. C'est le skeleton-patching, qui mobilise tout le reste de la fiche.

	- Skeleton de placeholders posé sur la pile (dummies à écraser)
	- Valeur réelle construite dans EAX
	- Écriture via `mov [esi], eax`, ESI parcourant les slots
	- Adresse des slots calculée au runtime (ancrage sur un registre, souvent ESP)

Le reste des sections concerne le Cas 2, sauf le pivot et le déclenchement de l'appel, communs aux deux.


# Charger une valeur

	- `pop eax ; ret`        => mettre un littéral dans un registre (la valeur suit sur la pile)
	- `pop ecx ; ret`        => alimenter le 2e registre pour l'arithmétique
	- `pop ebx / edx / edi`  => selon le registre de travail imposé par les autres gadgets

Brique de base : tout part d'un `pop`. La valeur chargée doit être null-free, sinon elle est corrigée par arithmétique.


# Fabriquer une valeur (anti bad chars)

Quand la valeur voulue contient un `00` (ou un autre bad char), elle ne peut pas être chargée directement : elle est construite.

	- `add eax, ecx ; ret`   => atteindre une cible par addition (charger 2 moitiés propres)
	- `sub eax, ecx ; ret`   => idem par soustraction
	- `neg eax ; ret`        => obtenir une valeur via son opposé (souvent plus propre en octets)
	- `inc eax ; ret`        => ajustement de +1
	- `dec eax ; ret`        => ajustement de -1
	- `xor eax, eax ; ret`   => mettre un registre à 0 proprement (0 littéral interdit)
	- `not eax ; ret`        => complément, autre moyen d'éviter des octets gênants

Technique classique : pour charger `0x00401000`, faire `pop eax` avec un 0x01401001 puis `add eax, 0xFEFFFFFF` (deux opérandes sans 00) pour retomber sur la cible.


# Mémoire (cœur du skeleton-patching)

	- `mov [esi], eax ; ret`     => ÉCRIRE eax dans le slot pointé (pose un argument réel)
	- `mov [edi], eax ; ret`     => variante (le registre pointeur dépend du gadget dispo)
	- `mov eax, [eax] ; ret`     => DÉRÉFÉRENCER : lire la vraie adresse d'une API via son pointeur IAT
	- `mov eax, [ecx] ; ret`     => déréf variante

Le `mov [reg], eax` est le geste qui remplace le `push` d'argument : rien n'est empilé, la valeur est écrite. Les crochets = accès mémoire. Le `mov eax, [eax]` transforme un pointeur IAT en adresse réelle de fonction (les API sont rebasées, seule l'adresse de l'entrée IAT est connue).


# Transfert entre registres

	- `mov esi, eax ; ret`       => charger une adresse DANS ESI (sans crochets, ≠ écriture mémoire)
	- `mov eax, esi ; ret`       => copier un registre (recaler une base)
	- `xchg eax, ebx ; ret`      => échanger : amener une valeur dans le registre attendu par un gadget
	- `push eax ; pop esi ; ret` => transfert via la pile quand aucun `mov esi, eax` n'existe

Distinction importante : sans crochets = manipuler le registre (ex. charger une adresse dans ESI) ; avec crochets = toucher la mémoire (ex. `mov [esi], eax`). Souvent la valeur est construite dans EAX puis déplacée vers le registre source attendu par le gadget d'écriture.


# Avancer le pointeur d'écriture

	- `inc esi ; ret`            => avancer d'1 octet (×4 = dword suivant du skeleton)
	- `add esi, 4 ; ret`         => avancer d'un dword d'un coup si dispo (plus propre)
	- `add esi, X ; ret`         => sauter plusieurs slots

Après chaque `mov [esi], eax`, le pointeur passe au slot suivant. Si seul `inc esi` existe, il est répété 4 fois par dword (motif fréquent : `inc esi` × 4).


# Capturer ESP / ancrage runtime (Cas 2)

L'adresse des slots du skeleton n'est pas connue à l'avance (la pile bouge). Elle est déduite au runtime d'un registre qui pointe sur le buffer.

	- `push esp ; pop eax ; ret` => récupérer l'adresse de pile courante dans un registre
	- `mov eax, esp ; ret`       => idem si dispo

Principe constant : s'ancrer sur un registre fiable, puis ajuster vers le 1er slot. Les détails varient à chaque cible :

1) L'ancre est souvent ESP, mais parfois un autre registre pointe déjà le buffer au crash.
2) Le gadget EIP peut pré-charger le pointeur. Exemple : `push esp ; push eax ; pop edi ; pop esi ; ret` remplit ESI/EDI dès le départ.
3) L'offset d'ajustement (ex. `-0x1C`) est propre au layout : il se détermine au débogueur (position du skeleton par rapport à ESP).


# Pivot (déplacer ESP), commun aux deux cas

	- `xchg eax, esp ; ret`           => ESP <- eax (pivot le plus courant)
	- `mov esp, eax ; ret`            => idem
	- `pop esp ; ret`                 => ESP <- valeur dépilée
	- `mov esp, ebp ; pop ebp ; ret`  => équivaut à `leave ; ret`
	- `leave ; ret`                   => `mov esp, ebp ; pop ebp` puis ret
	- `add esp, X ; ret`              => avancer ESP (sauter une zone, nettoyer)
	- `sub esp, X ; ret`              => reculer ESP

Le pivot sert à deux choses : (1) reloger ESP sur la zone ROP/skeleton quand l'overflow ne donne pas la pile au bon endroit ; (2) déclencher l'appel en faisant pointer ESP sur le bloc d'arguments rempli.


# Déclencher l'appel, commun aux deux cas

## Méthode skeleton + pivot

ESP est pivoté sur le bloc `[&Fonction][retour][arg1][arg2]...` => le `ret` "appelle" la fonction avec ses arguments en place.

## Technique pushad

	- `pushad ; ret`   => empile EAX, ECX, EDX, EBX, ESP, EBP, ESI, EDI d'un coup

Les 8 registres sont préchargés pour que la pile résultante forme directement la frame d'appel de VirtualProtect. Arrangement standard :

| Registre | Valeur à mettre | Rôle après pushad |
|---|---|---|
| EDI | adresse d'un `ret` | ROP NOP (le 1er ret glisse) |
| ESI | ptr vers `jmp [VirtualProtect]` | exécuté => appelle l'API |
| EBP | un `jmp esp` | adresse de retour (saut vers le shellcode après l'appel) |
| ESP | (valeur courante) | devient lpAddress (pointe vers le shellcode) |
| EBX | dwSize (ex. 0x201) | 2e arg |
| EDX | flNewProtect = 0x40 | 3e arg (PAGE_EXECUTE_READWRITE) |
| ECX | ptr inscriptible | 4e arg (lpflOldProtect) |
| EAX | 0x90909090 | NOP / junk |

C'est ce que `!mona rop` génère automatiquement. Avantage : un seul `pushad` pose toute la frame.

## Rebond direct

	- `jmp esp` / `call esp` / `push esp ; ret`  => sauter sur le shellcode après l'appel


# Filler

	- `ret` (seul)         => NOP de ROP : aligner la pile, absorber un `pop` en trop, rattraper un offset
	- `ret 0x04` (`retn`)  => ret + nettoie N octets de pile (sauter un argument déjà consommé)

Le `ret` seul est le couteau suisse : on en glisse pour réaligner la chaîne ou compenser un gadget qui dépile trop ou pas assez.


# Appariement des registres (Cas 2)

Un gadget d'écriture impose DEUX registres à la fois :

	- `mov [esi], eax`  => valeur construite dans EAX, pointeur = ESI
	- `mov [edi], ecx`  => valeur construite dans ECX, pointeur = EDI

Le choix du registre de travail n'est donc pas libre : il est dicté par le `mov [reg], reg` disponible. Le module doit aussi fournir de quoi piloter ce registre source (`pop`, `add`, `neg`, `mov reg,[reg]`). Le bon registre de travail = celui qui a à la fois le gadget d'écriture ET de quoi le remplir proprement.


# Les 3 gadgets prioritaires (Cas 2)

Sans ces trois-là, pas de skeleton-patching possible :

	1) `pop <reg> ; ret`          => charger des valeurs
	2) `mov [<reg>], <reg> ; ret` => écrire en mémoire (poser les args)
	3) `mov <reg>, [<reg>] ; ret` => déréférencer l'IAT (vraie adresse d'API)

Le pivot (`xchg eax, esp` ou `mov esp, ebp`) vient juste après dans l'ordre de priorité.


# Squelette d'une chaîne (récap)

## Cas 1 (valeurs null-free)

	1) Pré-requis : offset EIP, bad chars, module sans ASLR
	2) Poser le bloc d'appel en littéraux : [&Fonction][retour][arg1..argN]
	3) Pivoter ESP sur le bloc => l'appel se déclenche
	4) L'adresse de retour pointe sur le shellcode

## Cas 2 (valeurs à bad char)

	1) Pré-requis identiques
	2) Récupérer le pointeur IAT de la fonction (ex. VirtualAlloc)
	3) Poser le skeleton en placeholders : [&Fonction][retour][arg1..argN]
	4) Ancrer un registre d'écriture (ex. ESI) sur le 1er slot, calculé depuis ESP au runtime
	5) Pour chaque slot : construire la valeur dans EAX (anti-null) => `mov [esi], eax` => avancer le pointeur
	6) Déréférencer l'IAT pour le slot &Fonction (`mov eax, [eax]`)
	7) Pivoter ESP sur le skeleton => l'appel se déclenche
	8) L'adresse de retour pointe sur le shellcode (zone rendue exécutable)
