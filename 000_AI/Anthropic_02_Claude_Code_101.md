# Claude Code 101

Claude Code agit directement, contrairement à Claude classique qui a un modèle question / réponse.

Agent = IA qui observe un environnement, prend des décisions, agit, vérifie et recommence si besoin.

Le coeur de Claude Code est une "agentic loop" qui applique ce concept. Cela lui permet de lire tout un dépôt, expliquer des features, suivre des bugs, lancer des tests, installer des choses, utiliser des outils, lancer des commandes, etc.

Context Window = "Mémoire" de Claude qui est en somme un nombre de tokens par session. 

Trois niveaux de permissions (que l'on change avec Shift + Tab) : 
- Mode normal (Approval) = Demander confirmation avant chaque action
- Mode Auto-accept = Modifier les fichiers sans demander
- Mode Plan = Proposer un plan sans rien toucher

Manières de l'utiliser :
- Terminal (le plus puissant)
- IDE (VS Code / JetBrains)
- Desktop (en mode multitâches)
- Web

Le workflow optimal est le suivant :
- Explore → Plan → Code → Commit 

Il est important de bien comprendre le projet en mode Plan avant de commencer à coder, pour corriger les éventuelles erreurs ou incompréhensions de Claude.

Tips importants :
- Définir un critère de succès pour avoir une fin claire
- Penser à ajouter des outils
- Penser à ajouter des tests
- Utiliser le fichier /outputs/CLAUDE.md pour les erreurs
- Utiliser un autre agent pour vérifier le travail avec un regard neuf et sans biais

L’une des fonctionnalités les plus utiles de Claude Code est le fichier "CLAUDE.md" qui sert de mémoire persistante à un projet. Ce fichier Markdown placé à la racine du projet évite à Claude de repartir à zéro à chaque session.

Skill = Autre type de fichier Markdown qui permet de donner des instructions spécifiques à Claude pour qu'ils les applique automatiquement quand c'est pertinent.

Claude peut déléguer certaines tâches à des subagents, qui travaillent en parallèle.      

Utilité :      
- Chaque subagent a son propre contexte (mémoire séparée)
- Il fait le travail (exploration, recherche, etc.)
- Il renvoie ensuite un résumé au Claude principal
- Ce concept permet d'isoler le bruit et de ne pas trop polluer

MCP est un standard qui permet à Claude de se connecter à des outils externes. 
Deux types :
- HTTP (API, SaaS, etc.)
- Scripts locaux

La commande "claude mcp add" permet d'ajouter un serveur MCP et la commande "/mcp" permet de surveiller ou gérer les serveurs MCP connectés.

Un serveur MCP peut être :
- Local => Juste pour ce projet
- User => Tous tes projets
- Project => Partagé via .mcp.json

Attention à ne pas en abuser parce que chaque serveur MCP consomme du contexte.

Les hooks permettent d’exécuter automatiquement des commandes à des moments précis du fonctionnement de Claude Code. 
Contrairement au fichier CLAUDE.md par exemple, un hook sera forcément exécuté.

Cas d'usage courants : 
- Formatage automatique du code
- Logging des commandes
- Blocage d’actions dangereuses
- Notifications quand une tâche est finie

Les hooks sont définis dans le fichier settings.json où l'on choisit un évènement, un filtre et une commande à exécuter.

Quelques évènements : 
- PreToolUse : avant une action (comme modifier un fichier)
- PostToolUse : après une action
- UserPromptSubmit : quand on envoie un prompt
- Stop : quand Claude a fini
- Notification : quand Claude envoie une notification
