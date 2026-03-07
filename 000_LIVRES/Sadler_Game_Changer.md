# Historique

Les dix premières parties publiées du match AlphaZero vs Stockfish sont arrivées fin 2017, ce qui a provoqué un tremblement de terre à la fois grâce au score sans appel et à la manière de gagner d'AlphaZero. Cette soudaine hausse du niveau d'analyse possible a permis de revisiter bon nombre de positions de milieu de jeu évaluées à 0.00 par les anciens moteurs notamment.

Le projet AlphaGo a également marqué un tournant dans l’IA en battant les meilleurs joueurs humains de Go, jeu réputé impossible à maîtriser par les machines. L’équipe de DeepMind a rassemblé une équipe de 15 experts pour accélérer le développement et leur approche combinait deux réseaux de neurones (un Policy Net pour choisir les coups et un Value Net pour évaluer les positions) avec une recherche par arbre Monte-Carlo.

AlphaZero a joué 44 millions de parties contre lui-même en 9 heures et a ajusté ses réseaux de neurones après chacune d'entre elles pour mieux évaluer les positions et augmenter la probabilité des coups qui mènent à la victoire. Il a appris par des exemples et des observations concrètes au lieu de suivre des règles préétablies et des réglages humains. Comme c'est un modèle probabiliste, il a parfois tendance à choisir les branches les plus sûres au lieu des plus directes.

Il a aussi et surtout montré qu'on peut faire apprendre des compétences complexes à un modèle sans aucune connaissance de base, ce qui est très encourageant pour la médecine, la robotique et bien d'autres secteurs.

# Principes montrés par les parties d'AlphaZero

## Nouveautés et choses inhabituelles

- Ne pas hésiter à ralentir une attaque pour reconvertir le plan en exploitation des pièces adverses mal placées, au lieu de forcer et de créer des grosses faiblesses chez soi. Cela vaut aussi pour les parties très offensives comme les KIA, et globalement pour toutes les fortes attaques à l'aile roi.

- AlphaZero a tendance à plus placer ses cavaliers au bord que ce qui est habituellement recommandé et fait dans le jeu humain, mais surtout pour les faire arriver sur des cases proches du roi adverse (par exemple pour naviguer en f4 depuis h5).

- Il aime énormément la poussée du pion en h6 / h3 pour affaiblir la structure côté roi et restreindre le roi adverse dans les finales, bien plus souvent et bien plus tôt que dans les parties de GM en moyenne. Si l'on se prend un h5 en contre, cela donne la case g5 pour un cavalier ou un fou ainsi qu'une bonne opportunité d'ouverture avec g4. Il ouvre parfois aussi la structure à l'aile roi en prenant en g6 par exemple. Et cela peut aussi servir à faire sortir les tours par la troisième rangée.

- Grande importance d'ouvrir des lignes sur le roi adverse, quitte à sacrifier du matériel. La combinaison d'une ligne ouverte et d'une diagonale ouverte sur un roi est quasiment une garantie de victoire pour lui, particulièrement dans les situations de fous de couleurs opposées dont il raffole en milieu de jeu.

- AlphaZero ne part que très rarement pour l'attaque de minorité classique dans les structures Carlsbad, cherchant souvent de l'attaque ou d'autres manoeuvres à la place.

- Défense très active et dynamique. AlphaZero déteste se faire dominer et fait tout pour retrouver du dynamisme, quitte à sacrifier du matériel.

## Anciens principes connus resoulignés par AlphaZero

- Stabiliser le centre avant d'attaquer sur les ailes pour limiter le contre-jeu.

- Les fous de couleurs opposés favorisent l'attaque en milieu de jeu.

- Rendre les pièces adverses passives et échanger les pièces adverses actives.

- Tout faire pour placer les cavaliers sur de bons avant-postes.

- Viser le roi adverse tout en maintenant le sien hors de danger.

## Ouvertures 

Quand il est libre de choisir ses ouvertures, AlphaZero a un répertoire très classique et restreint. 

En blanc :
- Beaucoup de d4 et de Cf3.
- Ordres de coups qui visent à éviter la Nimzo-indienne

En noir :
- Systématiquement e5 face à e4.
- Systématiquement la berlinoise face à l'espagnole. 
- Systématiquement e5 face à c4.
- Systématiquement Cf6 face à d4 avec pour objectif de jouer Nimzo-indienne ou Ragozine.

