# Tips pour l'OSED et le RE en général

- Penser à rebaser le fichier dans IDA pour faire correspondre les adresses entre WinDbg et IDA (Edit => Segments => Rebase program).

- Penser aux breakpoints d'accès pour vérifier quand une zone est touchée par le programme (commande "ba" dans WinDbg).

- EAX contient souvent la valeur de retour d'une fonction quand il est vers la fin d'une fonction.

- Les programmes changent parfois l'endianness des données lors du parsing ou de la vérification, souvent avec des instructions comme "and eax, 0FFh" et " shl eax, 18h" en début de bloc par exemple.

- Pour inspecter dynamiquement les arguments d'une fonction en x86 il faut regarder la pile avec un "dd esp LX" au moment du call, mais en x64 il faut regarder les registres (RDI, RSI, RDX et RCX) et non pas la pile à cause des conventions d'appel.

- Quand il y a un "lea" avant un "call" c'est souvent pour passer l'adresse d'une structure ou d'un buffer de sortie à la fonction appelée.

- Beaucoup de fonctions Windows utilisent le principe mentionné ci-dessus pour écrire leur résultat directement dans un argument fourni.
