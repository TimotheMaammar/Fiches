# The Adolescence of Technology - Dario Amodei

Voir aussi le papier "Machines of Loving Grace" si besoin.

"Powerful AI" = IA pas forcément très différente d'aujourd'hui dans la forme, mais avec une capacité supérieure à un prix Nobel dans de nombreuses disciplines, qui possède de nombreuses interfaces d'interconnexion, capable de piloter d'autres machines, et reproductible massivement sous forme de copies indépendantes.

D'après Dario, cette "Powerful AI" pourrait tout à fait arriver dans 1 ou 2 ans. Cela pourrait largement dépasser ce délai mais il penche plutôt en faveur de cette courte fourchette, à cause des "scaling laws" qui l'ont toujours beaucoup intéressé : 
- https://arxiv.org/abs/2001.08361

Ces scaling laws s'illustrent très concrètement pour l'IA : il y a trois ans à peine, l'IA galérait à résoudre des problèmes mathématiques de base et à écrire du code.

L'IA écrit aujourd'hui déjà la majorité du code chez Anthropic : 
- https://www.anthropic.com/research/how-ai-is-transforming-work-at-anthropic

Il faut voir l'IA comme une future nation de génies supérieurs à nos meilleurs cerveaux et capables de tout traiter beaucoup plus vite, mais avec en contrepartie des motivations très diverses et parfois limitées. 

Risques possibles selon lui : 
- A) Mauvaises intentions de la part de cette nation
- B) Exploitation de cette nation par d'autres à des fins de destruction
- C) Exploitation à des fins de domination
- D) Destruction de l'économie même sans hostilité mais à cause de l'asymétrie
- E) Effets indirects imprévisibles

## Risque A 

Le risque A pourrait venir des étrangetés observées régulièrement (comme les hallucinations) et de toute la science-fiction qui est ingérée par ces programmes à travers la littérature, les jeux vidéos, etc. Ce n'est pas forcément très probable mais c'est possible, et Claude a déjà eu des comportements déceptifs quand Anthropic lui a donné des données d'entraînement disant que l'entreprise était une ennemie (avec chantage sur des employés fictifs notamment). Claude évoluait aussi plutôt "contre" l'entreprise en adoptant des comportements destructeurs : 
- https://www.anthropic.com/research/agentic-misalignment
- https://www.anthropic.com/research/emergent-misalignment-reward-hacking

Ce problème d'opposition avait toutefois été résolu par un prompt un peu opposé prenant le problème dans l'autre sens : 
- https://alignment.anthropic.com/2025/inoculation-prompting/

C'était un setup particulier, mais des erreurs pourraient aussi tout à fait arriver à cause des gigantesques sommes de données ingérées et de toutes les inconnues.

La défense contre ce risque est évidemment d'entraîner l'IA le mieux possible dès le début, mais une grosse partie se passe aussi en post-training (phase qui façonne comment le modèle va se comporter).

Anthropic a rédigé une Constitution IA pour aider dans ce domaine et pour donner une liste de principes généraux au lieu de chercher à cadrer chaque petit problème : 
- https://arxiv.org/abs/2212.08073
- https://www.anthropic.com/constitution

Il y a aussi l'angle d'apprendre à mieux "regarder à l'intérieur" de l'IA et Dario avait déjà publié un papier en 2025 sur ce sujet :
- https://www.darioamodei.com/post/the-urgency-of-interpretability

Un papier de recherche était également sorti en 2024 sur comment réussir à identifier des "features" compréhensibles humainement dans le réseau neuronal de Claude : 
- https://transformer-circuits.pub/2024/scaling-monosemanticity/index.html

Autres papiers intéressants sur la sécurité : 
- https://www.anthropic.com/research/next-generation-constitutional-classifiers
- https://www.anthropic.com/research/auditing-hidden-objectives

## Risque B

Le problème de ce risque est que l'IA est très accessible contrairement au nucléaire ou aux armes biochimiques par exemple. Heureusement les personnes ayant à la fois l'intelligence et la motivation pour faire de gros dégâts sont en général très rares (on peut penser à Kaczynski entre autres), et le problème est donc plutôt qu'un LLM pourrait facilement aider quelqu'un de moindre intelligence à construire des armes sophistiquées.

Quelques papiers ont déjà été faits spécifiquement sur le risque biologique possible avec les LLM : 
- https://www.judiciary.senate.gov/imo/media/doc/2023-07-26_-_testimony_-_amodei.pdf
- https://www.science.org/doi/10.1126/science.ads9158#elettersSection

La défense contre ce risque est principalement de sécuriser les modèles contre l'assistance à la création d'armes évidemment, mais le jailbreak reste toujours une possibilité. Des classifiers sont toutefois maintenus à jour pour limiter les risques au maximum (bien que ce ne soit pas rentable pour l'entreprise) : 
- https://www.anthropic.com/research/next-generation-constitutional-classifiers
- https://arxiv.org/pdf/2504.01849

L'autre voie est évidemment que la recherche en biologie avance vite et qu'on sache mieux se défendre intrinsèquement contre les attaques biologiques, point où l'IA pourra d'ailleurs considérablement contribuer.

Outre le biologique qui est le plus mortel, le plus gros morceau sera évidemment les cyberattaques assistées par IA, qui existent déjà : 
- https://www.anthropic.com/news/disrupting-AI-espionage

## Risque C

Le risque précédent parlait des individus, mais les États et les institutions représentent aussi un grand risque. Un gouvernement autoritaire pourrait tout à fait exploiter l'IA pour surveiller et oppresser ses citoyens, voire même d'autres pays. 

Sous-risques variés : 
- Armes autonomes
- Surveillance par IA
- Propagande par IA
- Conseil stratégique pour ces régimes

Ordre d'inquiétude : 
1) Parti Communiste Chinois
2) Les démocraties compétitives en IA
3) Pays non démocratiques avec de gros datacenters
4) Les entreprises d'IA elles-mêmes

La défense contre ce risque est évidemment d'éviter de vendre des puces au PCC et d'aider au maximum les démocraties attaquées comme l'Ukraine. La Chine a quelques années de retard sur les USA en production de puces et il ne faut pas leur donner le boost dont ils ont besoin. Également légiférer sur ce que les gouvernements peuvent ou ne peuvent pas faire, pour éviter tout débordement.

## Risque D

L'IA pourrait aussi stimuler une croissance économique rapide et substantielle en améliorant des secteurs comme la recherche scientifique, l'innovation biomédicale, la production, les chaînes d'approvisionnement et l'efficacité des systèmes financiers. Une croissance du PIB de 10 à 20 % par an pourrait être possible grâce à l'IA.

Cependant, cela pourrait avoir de grosses répercussions sur le monde du travail, notamment pour tous les boulots de cols blancs (surtout en niveau d'entrée) à cause de l'automatisation. Mais historiquement la technologie a en général ouvert la voie à beaucoup plus de marchés et de productivité, l'Histoire joue plutôt en notre faveur et il n'y a pas de quantité de travail fixe, l'économie évolue et se diversifie.

L'IA pourrait toutefois avoir quelques différences avec les précédentes révolutions technologiques, à cause de : 
- La vitesse de progrès largement supérieure
- La force cognitive de l'IA et sa couverture de plein de domaines
- L'évolution rapide de l'IA dans tous ces mêmes domaines

L'IA semble également se diffuser bien plus vite dans les entreprises que les précédentes technologies majeures, ce qui rend l'adaptation progressive plus compliquée pour le marché du travail. Et les boulots physiques pourraient être touchés aussi avec la robotique.

Certains pensent que même si l'IA surpasse les humains dans presque tous les domaines, la loi de l'avantage comparatif (selon laquelle chaque entité se spécialise dans ce qu'elle fait le mieux) permettrait de maintenir un rôle pour les humains dans l'économie. Cependant, si l'IA devient des milliards de fois plus productive que les humains, cette logique pourrait s'effondrer. Les coûts de transaction (comme le temps et l'énergie nécessaires pour échanger avec les humains) pourraient rendre ce type d'échange peu rentable, et les salaires humains pourraient chuter à des niveaux très bas, même si les humains ont encore des compétences utiles.

La principale défense contre ce risque sera d'orienter l'adoption de l'IA dans les entreprises pour les faire innover et pour ralentir la disruption de l'emploi, et de promouvoir la réaffectation des employés ainsi que la philanthropie des plus riches. Des politiques de redistribution pourraient également être nécessaires.

## Risque E

On va probablement vivre une décennie équivalente à un siècle pré-IA de progression scientifique et cela amène un grand nombre d'inconnues.     
En vrac : 
- Avancées en biologie qui permettront peut-être l'allongement de l'espérance de vie
- Dépendance à l'IA et influence des croyances humaines
- Perte de sens pour les gens
- ...

## Test de l'humanité

Il est fondamentalement impossible d'arrêter le développement de l'IA parce que la technologie est trop simple à reproduire et que quelqu'un prendra toujours la place de celui qui arrête de toute façon. Une modération réaliste pourrait être atteinte en ralentissant l'accès aux puces et semi-conducteurs pour les régimes autoritaires, mais difficilement plus. 

L'IA sera probablement le plus grand test de l'humanité mais beaucoup de chercheurs et de grands esprits travaillent à passer ce test.
