
# Claude 101

Bases d'un bon prompt : 
- Définir le contexte et les objectifs
- Définir la tâche à effectuer
- Ajouter des règles et des exemples si possible

Projet : environnement dédié avec sa mémoire, son historique et ses instructions personnalisées. Quand l'historique d'un projet devient trop gros, Claude fait automatiquement du RAG pour étendre la capacité tout en gardant un maximum de pertinence dans les réponses.

Artéfact : sortie créée en standalone par Claude et placée sur le côté du chat, quand le contenu dépasse 15 lignes et qu'il est assez compliqué par exemple (code, images, diagrammes, etc.).

Skill : dossier d'expertise que Claude peut charger automatiquement pour faire une tâche spécifique. Il peut contenir des instructions, des scripts et diverses ressources qui aideront à l'accomplissement de la tâche. Les skills utilisent une sandbox sécurisée où Claude peut exécuter du code, manipuler des fichiers, etc.

Connector : outil permettant d'interconnecter Claude directement avec des outils ou des données pour qu'il aille les chercher en autonomie. Les connectors utilisent le Model Context Protocol (MCP) et sont divisés en "web connectors" et en "desktop extensions".

Enterprise Search : moteur de recherche interne intelligent qui permet d’interroger toutes les données d’une organisation (docs, Slack, emails, etc.) en une seule question et de synthétiser beaucoup de sources en une réponse cohérente. Très utile pour la recherche interne et le suivi de projet.

Research : mode qui transforme l'IA en agent autonome pouvant explorer plusieurs pistes, enchaîner les recherches et faire un rapport structuré entre autres. Découpage du problème puis recherche itérative puis synthèse puis citations. Durée située entre 5 et 15 minutes ou parfois plus, mais équivalent de plusieurs heures de recherche humaine donc très rentable dans tous les cas.









