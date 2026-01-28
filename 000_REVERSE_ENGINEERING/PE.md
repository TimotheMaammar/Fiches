
Liens utiles : 
- https://learn.microsoft.com/fr-fr/windows/win32/debug/pe-format
- https://www.aldeid.com/wiki/PE-Portable-executable
- https://offwhitesecurity.dev/malware-development/portable-executable-pe/section-headers/

PE32 = Portable Executable 32-bit = Magic number 0x10B = Relocations HIGHLOW     
PE32+ = Portable Executable 64-bit = Magic number 0x20B = Relocations DIR64     

# WinDbg

Exemple de fouille dans le header PE d'une DLL pour lire ses métadonnées :

```
lm
lm m KERNEL32                    # On obtient 761d0000 en start
dt nt!_IMAGE_DOS_HEADER 761d0000
dd 761d0000 + 0x3c L1            # Le fameux 0x3C du vrai header
? 761d0000 + 000000f8            # On ajoute la valeur obtenue dessus
db 761d00f8 L4                   # DOIT AFFICHER LES OCTETS 50 45 00 00

dt nt!_IMAGE_FILE_HEADER 761d00f8+4        # Image File Header
dt nt!_IMAGE_OPTIONAL_HEADER 761d00f8+18   # Optional Header
```

Bien vérifier les points suivants au fur et à mesure :
- Les premiers octets du Image File Header doivent être "50 45 00 00"
- Les octets suivants (+4) doivent être 0x014C (x86) ou 0x8664 (x64)
- Les premiers octets du Optional Header (+18) doivent être 0x10b (x86) ou 0x20B (x64)

Inspection des sections : 

```
# On reprend la valeur de SizeOfOptionalHeader trouvée dans le IMAGE_FILE_HEADER : 
#     +0x010 SizeOfOptionalHeader : 0xe0
# Et on applique la formule IMAGE_FILE_HEADER + 0x18 + la taille trouvée

db 761d00f8 + 0x18 + 0xe0 L10 
# On doit trouver un nom de section comme ".text" par exemple
```
# Structure d'un PE
## DOS Header (IMAGE_DOS_HEADER)

Header gardé pour des raisons de rétro-compatibilité mais qui ne sert quasiment plus.
La seule valeur intéressante est le 0x3C qui permet de sauter au "vrai" header PE : 

```
+0x00  WORD   e_magic      // Signature "MZ" (0x5A4D)
+0x02  WORD   e_cblp       // Bytes on last page of file
+0x04  WORD   e_cp         // Pages in file
+0x06  WORD   e_crlc       // Relocations
+0x08  WORD   e_cparhdr    // Size of header in paragraphs
+0x0A  WORD   e_minalloc   // Minimum extra paragraphs needed
+0x0C  WORD   e_maxalloc   // Maximum extra paragraphs needed
+0x0E  WORD   e_ss         // Initial (relative) SS value
+0x10  WORD   e_sp         // Initial SP value
+0x12  WORD   e_csum       // Checksum
+0x14  WORD   e_ip         // Initial IP value
+0x16  WORD   e_cs         // Initial (relative) CS value
+0x18  WORD   e_lfarlc     // File address of relocation table
+0x1A  WORD   e_ovno       // Overlay number
+0x1C  WORD[4] e_res       // Reserved words
+0x24  WORD   e_oemid      // OEM identifier
+0x26  WORD   e_oeminfo    // OEM information
+0x28  WORD[10] e_res2     // Reserved words
+0x3C  DWORD  e_lfanew     // Offset to the PE Header
```

## DOS Stub

- Offset 0x40 et taille variable.
- Code DOS 16-bit qui affiche "This program cannot be run in DOS mode"
- Permet une rétro-compatibilité minimale
- Se termine juste avant le PE Header
- Inutile aussi pour nous aujourd'hui

## PE Header (IMAGE_NT_HEADERS)

Le "vrai" header accessible depuis l'offset 0x3C.
### 1) Image File Header (IMAGE_FILE_HEADER)

```
+0x00  DWORD  Signature            // "PE\0\0" (0x00004550)
+0x04  WORD   Machine              // Architecture (0x014C / 0x8664)
+0x06  WORD   NumberOfSections     // Nombre de sections
+0x08  DWORD  TimeDateStamp        // Date de compilation (Unix timestamp)
+0x0C  DWORD  PointerToSymbolTable // Offset vers la table de symboles (déprécié)
+0x10  DWORD  NumberOfSymbols      // Nombre de symboles (déprécié)
+0x14  WORD   SizeOfOptionalHeader // Taille de l'Optional Header
+0x16  WORD   Characteristics      // Flags du fichier
```
### 2) Optional Header (IMAGE_OPTIONAL_HEADER)

```
+0x18  WORD   Magic                    // 0x010B (PE32) ou 0x020B (PE32+)
+0x1A  BYTE   MajorLinkerVersion
+0x1B  BYTE   MinorLinkerVersion
+0x1C  DWORD  SizeOfCode               // Taille de .text
+0x20  DWORD  SizeOfInitializedData    // Taille de .data
+0x24  DWORD  SizeOfUninitializedData  // Taille de .bss
+0x28  DWORD  AddressOfEntryPoint      // RVA du point d'entrée
+0x2C  DWORD  BaseOfCode               // RVA de début de .text
+0x30  DWORD  BaseOfData               // RVA de début de .data (PE32 only)
+0x34  DWORD  ImageBase                // Adresse de chargement préférée
+0x38  DWORD  SectionAlignment         // Alignement en mémoire (4KB)
+0x3C  DWORD  FileAlignment            // Alignement sur disque (512B ou 4KB)
+0x40  WORD   MajorOperatingSystemVersion
+0x42  WORD   MinorOperatingSystemVersion
+0x44  WORD   MajorImageVersion
+0x46  WORD   MinorImageVersion
+0x48  WORD   MajorSubsystemVersion
+0x4A  WORD   MinorSubsystemVersion
+0x4C  DWORD  Win32VersionValue        // Réservé (0)
+0x50  DWORD  SizeOfImage              // Taille totale en mémoire
+0x54  DWORD  SizeOfHeaders            // Taille de tous les headers
+0x58  DWORD  CheckSum                 // Checksum du fichier
+0x5C  WORD   Subsystem                // GUI, Console, Native, etc.
+0x5E  WORD   DllCharacteristics       // Flags DLL
+0x60  DWORD  SizeOfStackReserve       // Taille de stack réservée
+0x64  DWORD  SizeOfStackCommit        // Taille de stack commitée
+0x68  DWORD  SizeOfHeapReserve        // Taille de heap réservée
+0x6C  DWORD  SizeOfHeapCommit         // Taille de heap commitée
+0x70  DWORD  LoaderFlags              // Déprécié
+0x74  DWORD  NumberOfRvaAndSizes      // Nombre d'entrées Data Directory (16)
```
### 3) Data Directories

```
+0x78  DWORD  Export Table                   // RVA de l'Export Directory
+0x7C  DWORD                                 // Taille de l'Export Directory  
+0x80  DWORD  Import Table                   // RVA de l'Import Directory
+0x84  DWORD                                 // Taille de l'Import Directory  
+0x88  DWORD  Resource Table                 // RVA du Resource Directory
+0x8C  DWORD                                 // Taille du Resource Directory  
+0x90  DWORD  Exception Table                // RVA de l'Exception Directory
+0x94  DWORD                                 // Taille de l'Exception Directory
+0x98  DWORD  Certificate Table              // Raw offset du Security Directory (pas RVA)
+0x9C  DWORD                                 // Taille du Security Directory  
+0xA0  DWORD  Base Relocation Table          // RVA du Base Relocation Directory
+0xA4  DWORD                                 // Taille du Base Relocation Directory
+0xA8  DWORD  Debug                          // RVA du Debug Directory
+0xAC  DWORD                                 // Taille du Debug Directory  
+0xB0  DWORD  Architecture                   // RVA de la Copyright
+0xB4  DWORD                                 // Taille de la Copyright Note  
+0xB8  DWORD  Global Ptr                     // RVA du Global Pointer (IA-64 uniquement)
+0xBC  DWORD                                 // Non utilisé (doit être 0)  
+0xC0  DWORD  TLS Table                      // RVA du Thread Local Storage Directory
+0xC4  DWORD                                 // Taille du TLS  
+0xC8  DWORD  Load Config Table              // RVA du Load Configuration Directory
+0xCC  DWORD                                 // Taille du Load Configuration Directory  
+0xD0  DWORD  Bound Import                   // RVA du Bound Import Directory
+0xD4  DWORD                                 // Taille du Bound Import Directory  
+0xD8  DWORD  IAT                            // RVA de la première IAT
+0xDC  DWORD                                 // Taille totale de toutes les IAT
+0xE0  DWORD  Delay Import Descriptor        // RVA du Delay Import Directory
+0xE4  DWORD                                 // Taille du Delay Import Directory  
+0xE8  DWORD  CLR Runtime Header             // RVA du COM Header (.NET)
+0xEC  DWORD                                 // Taille du COM Header (dans les exécutables .NET)  
+0xF0  DWORD  Reserved                       // Réservé (doit être 0)
+0xF4  DWORD  Reserved                       // Réservé (doit être 0)  
```

Différences principales en PE32+ :
- Pas de BaseOfData
- ImageBase, SizeOfStackReserve, SizeOfStackCommit, SizeOfHeapReserve, SizeOfHeapCommit sont des QWORD (8 bytes)
### 4) Section Table (IMAGE_DATA_DIRECTORY)

- Après l’Optional Header, la Section Table commence à l’offset : PointerToPEHeader + 0x18 + SizeOfOptionalHeader
- Chaque entrée fait 40 octets.
- Pour chaque section, on peut calculer son contenu sur disque et en mémoire.

```
+0x00  BYTE[8] Name                  // Nom de la section (ASCII)
+0x08  DWORD   VirtualSize           // Taille en mémoire
+0x0C  DWORD   VirtualAddress        // RVA de début en mémoire
+0x10  DWORD   SizeOfRawData         // Taille sur disque (alignée sur FileAlignment)
+0x14  DWORD   PointerToRawData      // Offset sur disque
+0x18  DWORD   PointerToRelocations  // Déprécié (0)
+0x1C  DWORD   PointerToLinenumbers  // Déprécié (0)
+0x20  WORD    NumberOfRelocations   // Déprécié (0)
+0x22  WORD    NumberOfLinenumbers   // Déprécié (0)
+0x24  DWORD   Characteristics       // Flags de la section
```







