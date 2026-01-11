
# NX / DEP

## Principe

NX (No eXecute) = Fonctionnalité matérielle généralement supportée par le processeur et qui empêche l'exécution de code dans certaines zones importantes comme la pile ou le tas.

DEP (Data Execution Prevention) = Équivalent du NX mais en logiciel et généralement mis en place par Windows qui va s'appuyer dessus ou le réimplémenter.

## Contournement

Le principal contournement de ces sécurités est le ROP (Return-Oriented Programming), qui consiste à exécuter du code déjà présent dans le programme à l'aide de gadgets. Au-delà des ROP chains, il y a aussi l'attaque Return-to-libc qui vise à remplacer l'adresse de retour d'une fonction par une adresse de fonction de libc (en général system() évidemment).

# ASLR

## Principe

ASLR (Address Space Layout Randomization) = Technique qui randomise les adresses à chaque fois qu'un programme est lancé pour complexifier l'exploitation. Cela rend plus difficile le buffer overflow traditionnel ainsi que toutes les attaques à base de ROP notamment.

## Contournement

Principales techniques : 

1) Fuite d'informations (PEB, TEB, GOT, etc.) et d'adresses grâce à d'autres techniques : 
	- Format String
	- Use-After-Free
	- Dangling Pointers
	- Uninitialized Memory Read
	- Buffer Over-read
	- ...

2) Brute forcing des adresses     
	=> Technique qui marche surtout en x86 vu l'échelle bien plus grande en x64 qui nécessiterait au moins un oracle ou un partial overwrite pour réduire la fourchette.

3) Chercher des modules sans ASLR et passer par eux à la place.

# Stack Canaries / SSP

## Principe

Stack Canaries = Protection contre les buffer overflows qui insère une valeur spéciale dans la pile avant le retour d'une fonction pour détecter les modifications. 

SSP (Stack Smashing Protector) = Implémentation de la technique Stack Canaries dans certains compilateurs comme GCC.

## Contournement

Principales techniques :

1) Fuite d'informations comme pour l'ASLR et avec les mêmes techniques     

2) Éviter le canary :      
	A) Stack Pivot => Déplacer RSP vers une zone contrôlée juste avant le check     
	B) Détourner le flux juste avant le check avec un saut ou grâce au SEH     
	C) Brute forcing du canary comme pour l'ASLR mais en plus simple     

Il est à noter qu'il n'y a pas de canaries dans le cas des heap overflows et des BSS-based overflows à cause de la nature spécifique du heap et de la section BSS.

# PIE

## Principe

PIE (Position Independent Executable) = Chargement du binaire à une adresse aléatoire pour chaque exécution. C'est en somme l'équivalent de l'ASLR mais juste pour l'adresse de base du binaire lui-même. Ces deux techniques sont d'ailleurs à utiliser ensemble pour vraiment faire en sorte que l'exploitation soit un enfer. 

## Contournement

Même chose que pour l'ASLR.

# Fortify Source

## Principe

Fortify Source (FS) = Extension présente sur pas mal de compilateurs qui rajoute des vérifications supplémentaires à la compilation, notamment sur certaines fonctions à risque comme strcpy(), sprintf(), etc. 

On ne la voit pas directement avec checksec et compagnie mais on peut le déduire autrement :      
- Si on sait que le binaire a été compilé avec -D_FORTIFY_SOURCE=X     
- Si on trouve des fonctions avec le suffixe chk :     
	=> `__strcpy_chk`     
	=> `__memcpy_chk`     
	=> `__snprintf_chk`     
	=> ...     
## Contournement

Principaux problèmes :

1) Cette extension ne protège pas le heap, et plus généralement tout ce qui est dynamique (ce qui est à base de malloc() notamment)     
2) Cette extension ne protège pas contre le ROP donc toutes les techniques à base de ROP fonctionnent contre elle      
3) Toutes les fonctions ne sont pas protégées donc on peut parfois en trouver des équivalentes sans protection     

Chaque niveau de FORTIFY_SOURCE a aussi eu quelques vulnérabilités historiques et il existe des CVE sur différents cas de figure.     

# RELRO

## Principe

Global Offset Table (GOT) = Table contenant les adresses réelles des fonctions externes après leur résolution par le linker.     

Procedure Linkage Table (PLT) = Stubs de code qui appellent les fonctions via la GOT.     

RELRO (Relocation Read-Only) = Protection propre à Linux qui rend la section GOT en lecture seule après le chargement, ce qui évite la modification d'adresses de fonctions (comme rediriger printf() vers system() par exemple).

Deux types : 
1) Full RELRO = Protection totale mais plus lente     
2) Partial RELRO = Protection partielle mais meilleure en performances     

## Contournement

Cette technique ne protège pas des attaques à base de ROP donc c'est évidemment le contournement principal, mais il est aussi possible de chercher à écrire dans d'autres sections modifiables par exemple. 

# SafeSEH

## Principe

SafeSEH = Protection propre à Windows consistant en une sorte de liste blanche de handlers qui permet de protéger le binaire contre l'exploitation d'un SEH overflow en surveillant si chaque handler est bien dans la liste.

La plupart des debuggers populaires ou certains outils spécifiques peuvent détecter cette protection facilement et lister les modules avec ou sans SEH.

## Contournement

Le principe de base reste généralement de chercher des modules sans cette protection dans WinDbg par exemple, sinon il faudra une seconde vulnérabilité (ou une vulnérabilité assez forte pour tout faire à la fois) qui permet de réécrire la table SafeSEH.
