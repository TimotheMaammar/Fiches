# WinDbg

## Affichage et édition

Voir aussi :
- Pour la lecture : dq, du, dD, dpS, df, da, du, dyb, dl, dds, ...
- Pour le déréférencement : by, wo, dwo, qwo, ...

```
db 761d0000          # Octets
db 761d0000 L10      # 16 octets
dw 761d0000          # Words
dW 761d0000          # Words avec ASCII
dd 761d0000          # Double words
dc 761d0000          # Double words avec ASCII
dp 761d0000          # Pointers
dps 761d0000         # Pointers + Symboles
da 761d0000          # ASCII


.formats 00905a4d    # Affichage de tous les formats 
.formats @eax        # Pareil pour la valeur stockée dans ESP
.formats poi(esp)    # Pareil mais utilisation d'ESP comme pointeur

r eax, ebx           # Affichage des registres
r eax=1, ebx=ebx+10  # Modification
kv                   # Affichage de la call stack en mode verbeux
~                    # Affichage des threads en cours
~2s                  # Sélectionner le thread 2 


? poi(esp) + 0x10    # Calcul 
ed 761d0000 9090     # Édition à une adresse donnée 
```

## Debugging

```
.restart             # Redémarrer le processus
q (qd)               # Quitter (et détacher)

bp <ADRESSE> "CMD"   # Breakpoint avec éventuellement une commande
bd / be / bc         # Désactiver / Réactiver / Supprimer un breakpoint
bl / bc *            # Lister / Supprimer tous les breakpoints
bm kernel32!*strcpy  # Breakpoint sur un symbole et à chaque utilisation

ba <TYPE> <ADDR>     # Breakpoint matériel sur l'accès mémoire
ba e1 0x1000         # Breakpoint sur l'exécution d'une instruction
ba r4 0x1000         # Breakpoint sur la lecture de 4 octets
ba w8 0x1000         # Breakpoint sur l'écriture de 8 octets 

p / t / g            # Step over / Step into / Continuer l'exécution

```
## Recherche

```
s -a 0 L?80000000 "This program"          # Recherche d'une chaine
s -b 761d0000 L1000000 4d 5a              # Recherche d'octets
s -d 761d0000 L1000000 12345678           # Recherche d'un word
```
## Modules

```
lm                   # Liste 
lm m kernel32        # Module spécifique
lm m kernel32 v      # Verbose (très utile)
lm a 761d0000        # Quel module à cette adresse ? 
lm a eip             # Quel module à l'adresse contenue dans EIP ?

x kernel32!*         # Tous les symboles d'un module
x kernel32!Create*   # Exemple de filtre
x *!NtCreateFile     # À quel module appartient ce symbole ?

ln 761d0000          # Symbole le plus proche de cette adresse

.reload /f           # Forcer le rechargement
ld KERNEL32          # Charger les symboles pour un module
```

## Variables très utiles 

```
dx Debugger.State.DebuggerVariables         # Résumé de toutes les variables
dx @$curprocess                             # Processus en cours
dx @$cursession                             # Session en cours
dx @$curthread                              # Thread en cours
dx @$curthread.Registers.User               # Registres principaux
[...]
```

## Autres commandes utiles

```
!peb                                        # Affichage du PEB
!teb                                        # Affichage du TEB
!gle                                        # GetLastError()
!gle -all                                   # Pour tous les threads
!handle                                     # Liste des handles
!handle 0 f                                 # Avec détails
!dh -a KERNEL32                             # Afficher l’en-tête PE
```

## Liens utiles

- https://dblohm7.ca/pmo/windbgcheatsheet.html
- https://learn.microsoft.com/fr-fr/windows-hardware/drivers/debugger/getting-started-with-windbg
- https://gist.github.com/MangaD/3bbeae1b326351b2728d856fb5cd651c
