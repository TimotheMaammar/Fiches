# Reverse Engineering

## File Formats: Magic Numbers (Python)

	cat /challenges/cimg
	(echo -n "[lMg"; echo -n "blablabla") > mon_image.cimg
	/challenge/cimg /home/hacker/mon_image.cimg

## File Formats: Magic Numbers (C)

	cat /challenges/cimg.c
	(echo -n "<MAG"; echo -n "blablabla") > mon_image.cimg
	/challenge/cimg /home/hacker/mon_image.cimg

## File Formats: Magic Numbers (x86)

Désassemblage de /challenge/cimg avec Binary Ninja

On voit au début : 
```
if (var_24.b != 0x5b || var_24:1.b != 0x21 || var_24:2.b != 0x4d || var_24:3.b != 0x47) {
    puts("ERROR: Invalid magic number!");
    exit(0xffffffff);
}
```

Même principe qu'avant. 

	(echo -ne '\x5b\x21\x4d\x47'; echo -n 'blablabla') > mon_image.cimg
	/challenge/cimg /home/hacker/mon_image.cimg

## Reading Endianness (Python)

	cat /challenges/cimg

Ligne avec l'endianness : 

> assert int.from_bytes(header[:4], "little") == 0x726E4F7B, "ERROR: Invalid magic number!"

Exploitation : 

	(echo -ne '\x7b\x4f\x6e\x72'; echo -n "blablabla") > mon_image.cimg
	/challenge/cimg /home/hacker/mon_image.cimg

## Reading Endianness (C)

	cat /challenges/cimg.c
	(echo -ne '\x43\x4e\x4d\x67'; echo -n "blablabla") > mon_image.cimg
	/challenge/cimg /home/hacker/mon_image.cimg

## Reading Endianness (x86)

Désassemblage de /challenge/cimg avec Binary Ninja

On voit au début : 
```
if (var_14 != 0x47414d3c)
	puts("ERROR: Invalid magic number!")
```

Exploitation : 

	(echo -ne '\x3c\x4d\x41\x47'; echo -n "blablabla") > mon_image.cimg
	/challenge/cimg /home/hacker/mon_image.cimg

## Version Information (Python)

```
with open("fichier.cimg", "wb") as fichier:
    fichier.write(b"cm6e")
    fichier.write((135).to_bytes(2, "little"))
```

## Version Information (C)

```
with open("fichier.cimg", "wb") as fichier:
    fichier.write(b"CMag")
    fichier.write((100).to_bytes(2, "little"))
```

## Version Information (x86)

```
with open("fichier.cimg", "wb") as fichier:
    fichier.write(b"\x3c\x30\x25\x72")
    fichier.write((125).to_bytes(2, "little"))
```

## Metadata and Data (Python)

```
with open("fichier.cimg", "wb") as fichier:
    fichier.write(b"<:MG")
    fichier.write((1).to_bytes(2, "little"))
    fichier.write((50).to_bytes(1, "little"))
    fichier.write((11).to_bytes(8, "little"))

    fichier.write(bytes([0x20] * (50 * 11)))
```

## Metadata and Data (C)

La structure C du début donne la taille attendue du header : (4+1+2+2)

```c
struct cimg_header
{
    char magic_number[4]; 
    uint8_t version;      
    uint16_t width;   
    uint16_t height;       
} __attribute__((packed));
```

Même principe ensuite : 

```python
with open("fichier.cimg", "wb") as fichier:
    fichier.write(b"<@Nr")
    fichier.write((1).to_bytes(1, "little"))
    fichier.write((44).to_bytes(2, "little"))
    fichier.write((24).to_bytes(2, "little"))

    fichier.write(bytes([0x20] * (44 * 24)))
```

## Metadata and Data (x86)

```
with open("fichier.cimg", "wb") as fichier:
    fichier.write(b"\x5b\x4d\x61\x67")
    fichier.write((1).to_bytes(4, "little"))
    fichier.write((68).to_bytes(2, "little"))
    fichier.write((15).to_bytes(2, "little"))

    fichier.write(bytes([0x20] * (68 * 15)))
```

En désassemblant le binaire avec Binary Ninja on observe que la taille est un "int32_t" donc 4 octets.
