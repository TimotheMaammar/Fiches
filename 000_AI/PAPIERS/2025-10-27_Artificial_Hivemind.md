# Artificial Hivemind

## Références

Titre complet : "Artificial Hivemind: The Open-Ended Homogeneity of Language Models"     

Lien : https://arxiv.org/abs/2510.22954     

Date : 27/10/2025     

Institutions : University of Washington, Carnegie Mellon University, Allen Institute for Artificial Intelligence, Lila Sciences, Stanford University      

Noms des chercheurs : Liwei Jiang, Yuanjun Chai, Margaret Li, Mickel Liu, Raymond Fok, Nouha Dziri, Yulia Tsvetkov, Maarten Sap, Alon Albalak, Yejin Choi     

## Abstract

 Les LLM ont tendance à produire des réponses trop similaires entre eux, comme si tous les modèles pensaient de la même manière ; un phénomène que les chercheurs appellent "ruche artificielle", d'où le nom de cette étude. Pour étudier ce problème, l'équipe a créé Infinity-Chat qui est une base de données de requêtes ouvertes sans bonne réponse unique. En analysant les réponses de différents modèles, ils ont découvert deux choses : 

1) Un même modèle répète souvent les mêmes idées (manque de diversité interne).
2) Des modèles différents donnent des réponses quasiment identiques (convergence extrême entre les LLM).

Cela pose un risque à long terme : si les humains sont exposés en permanence à des réponses standardisées, cela pourrait appauvrir la créativité et la diversité des idées.      
L'étude permet enfin de mesurer ce problème et d’étudier comment éviter que les IA ne deviennent toutes des clones.

## Contenu     

Infinity-Chat = Dataset de 26000 questions ouvertes et réalistes du genre "Invente une histoire" ou "Propose des idées pour un projet" par exemple.      

L'étude a testé un peu plus de 70 modèles différents, à la fois des modèles ouverts et des modèles fermés.      

Il y avait également 25 humains pour donner un avis sur les réponses et choisir parmi des couples de réponses, pour vérifier la diversité humaine en comparaison.     

Pour quantifier la diversité des réponses, l'étude utilise l'entropie de Shannon, en plus de la similarité sémantique et de l'évaluation humaine.   

### Résultats

Les LLM favorisent le consensus et pénalisent les réponses originales ou subjectives au profit de réponses moyennes.    

#### Répétition Intra-Modèle

Même avec des paramètres censés augmenter la diversité on obtient une très forte similarité dans les réponses : 
- Température = 1.0 (plus d'exploration et de créativité)
- Top-p = 0.9 (on garde les mots jusqu'à atteindre 90% de probabilité cumulée)
- Min-p = 0.1 (on élimine un tout petit peu de mots très improbables)
- Similarité moyenne = 0.8 (très forte similarité)

#### Homogénéité Inter-Modèles

Similarité moyenne entre modèles différents : 71–82%

Certaines phrases comme "Le temps est une rivière" sont carrément identiques entre les modèles.
