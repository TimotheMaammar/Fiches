# From Day Zero to Zero Day - Eugene Lim

## Code review 

Taint analysis (= source and sink analysis) : suivre le flux d'une donnée depuis une source non fiable (input utilisateur, fichier, réseau) jusqu'à un sink sensible (appel système, requête, écriture mémoire).       
Deux approches complémentaires : source-to-sink favorise l'exhaustivité, sink-to-source favorise la sélection et le filtrage.

Bon réflexe côté sink-to-source : chercher la liste des fonctions bannies propre à chaque boîte, souvent dérivée ou inspirée de la "banned.h" de Microsoft : https://github.com/x509cert/banned

Quelques patterns récurrents à chercher activement :
- **TLV (Type-Length-Value)** : schéma très courant dans les formats binaires et protocoles réseau, toujours vérifier les mismatchs entre la longueur déclarée et la taille réelle des données.
- **Formats "directory-based"** (archives/wrappers autour d'autres fichiers, type ZIP, tar, OOXML) : deux familles de vulnérabilités à chercher systématiquement, le path traversal sur l'extraction, et les vulnérabilités héritées du format des fichiers enfants (XXE si du XML est embarqué, par exemple).
- **Champs customisables ou extensibles** : souvent le point le moins bien audité d'un format ou protocole, terrain fertile pour des bugs.
- **Propagation des erreurs** : un développeur qui fait une erreur de sécurité la refait souvent ailleurs dans la même codebase, un pattern trouvé une fois vaut la peine d'être cherché de manière exhaustive.

## Analyse automatisée

Bases théoriques : 
- **AST** (Abstract Syntax Tree), la structure grammaticale du code, comment les expressions s'imbriquent (ce qui est écrit)
- **CFG** (Control Flow Graph), les chemins d'exécution possibles entre blocs d'instructions (comment on y arrive)

Les deux sont complémentaires, et c'est exactement sur cette combinaison (souvent avec un data-flow graph par-dessus) que reposent les outils d'analyse automatisée modernes pour repérer un pattern syntaxique tout en vérifiant s'il est réellement atteignable.

CodeQL est un bon tool pour ça : https://codeql.github.com/      
Semgrep aussi : https://semgrep.dev/       

## Reverse engineering

OleViewDotNet est un bon tool pour la stack COM/Windows : https://github.com/tyranid/oleviewdotnet

"Code coverage" a une définition différente en reverse qu'en programmation classique : c'est le pourcentage du code binaire réellement exécuté/atteint pendant une session d'exécution ou de fuzzing.        
Lighthouse est un bon tool pour visualiser cette couverture : https://github.com/gaasedelen/lighthouse

Qiling est un bon tool pour l'émulation : https://github.com/qilingframework/qiling

Exécution symbolique : au lieu de faire tourner le programme avec des valeurs concrètes, on le fait tourner avec des variables symboliques et un solveur de contraintes détermine quelles entrées concrètes feraient prendre tel ou tel chemin, utile pour atteindre un chemin de code précis sans avoir à deviner l'input à la main.      
Angr est le leader en la matière : https://github.com/angr/angr

## Fuzzing

Avoir une feedback loop et faire du fuzzing intelligent donne évidemment bien plus de résultats que la version bête et straightforward.

Quelques outils généralistes :

* Boofuzz, framework de fuzzing : https://github.com/jtpereyda/boofuzz
* OSS-Fuzz, infrastructure Google de fuzzing : https://github.com/google/oss-fuzz
* FormatFuzzer, génère des fuzzers directement depuis une spec de format binaire : https://github.com/uds-se/FormatFuzzer

Coverage-guided fuzzing = fuzzing piloté par la couverture de code atteinte, nécessite un harness (point d'entrée custom qui appelle directement la fonction/le parseur ciblé plutôt que le programme entier).

* AFL++, la référence incontournable, avec deux modes clés (QEMU mode pour fuzzer sans avoir le code source, Frida mode pour l'instrumentation dynamique sans recompilation) : https://github.com/AFLplusplus/AFLplusplus
* afl-cov, analyse de couverture des runs AFL : https://github.com/axt/afl-cov
* FuzzIntrospector, analyse de la qualité du harness et des gaps de couverture non atteignables, maintenu par l'OpenSSF et très utilisé en complément d'OSS-Fuzz : https://github.com/ossf/fuzz-introspector

Toujours multithreader le fuzzing dès que c'est possible, gain de vitesse direct et quasiment gratuit.

Fuzzing pour langages managés :

* Jazzer, Java/JVM : https://github.com/CodeIntelligenceTesting/jazzer
* go-fuzz, Go : https://github.com/dvyukov/go-fuzz
