# IDA Free

## Mémo rapide sur les cross-references

- xref to = Qui appelle cet endroit ?
- xref from = Qu'est-ce que ce code utilise ?

## Raccourcis les plus utiles

- Ctrl+P => Overview de toutes les commandes avec possibilité de chercher
- Espace sur une instruction => Passer en vue graph / texte en suivant l'instruction
- Tabulation sur une instruction => Passer en vue pseudocode / texte en suivant l'instruction
- Entrée sur une instruction => Suivre un xref / jump
- X => Lister les xrefs vers l'élément courant
- Ctrl+X => Lister les xrefs depuis l'élément courant
- Ctrl+L => Sauter à une fonction par son nom
- Alt+M => Marquer une ligne
- Ctrl+M => Voir l'ensemble des marques et sauter dessus
- G => Sauter à une adresse
- P => Modifier une instruction en assembleur
- Ctrl+F => Chercher tout type de texte dans le contexte courant
- N => Renommer n'importe quel label

## Types et structures

Le raccourci Shift + F1 (ou View => Open subviews => Local types) permet de voir les types importés dans IDA et d'en rajouter à la main si besoin.

On peut aussi importer des types depuis un header C directement (File > Load file > Parse C header file), et tout ce qui est parsé atterrit dans Local Types. Pratique pour charger les structures Windows (`PROCESS_INFORMATION`, `sockaddr_in`, etc.) sans les définir à la main.

Si on veut appliquer une structure avec 'T' sur un buffer, elle doit exister dans "Local Types" au préalable.

Penser aussi à renommer les fonctions dès qu'on sait ce qu'elles font.

### FLIRT 

FLIRT =  Fast Library Identification and Recognition Technology = Sorte d'empreinte utilisée par IDA pour reconnaître automatiquement du code provenant de bibliothèques connues.

IDA possède une base de signatures construites à partir de bibliothèques connues, et cela peut considérablement aider pour améliorer le contexte d'un programme.

Le raccourci Shift + F11 permet de charger une signature FLIRT.

Marche bien pour les trucs classiques mais peut être limité dès qu'il y a de l'obfuscation ou du packing. Surtout utile sur des binaires strippés sans symboles de debug.

