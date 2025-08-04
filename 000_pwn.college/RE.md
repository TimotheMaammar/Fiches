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

