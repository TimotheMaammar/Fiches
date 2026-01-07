# Tips pour l'OSED et le RE en général

- Penser à rebaser le fichier dans IDA pour faire correspondre les adresses entre WinDbg et IDA (Edit => Segments => Rebase program).

- Penser aux breakpoints d'accès pour vérifier quand une zone est touchée par le programme (commande "ba" dans WinDbg).

- EAX contient souvent la valeur de retour d'une fonction quand il est vers la fin d'une fonction.
