# AI Agent Traps

## Références 

Titre : AI Agent Traps

Lien : https://papers.ssrn.com/sol3/papers.cfm?abstract_id=6372438

Date : 28/03/2026

Chercheurs : Matija Franklin, Nenad Tomašev, Julian Jacobs, Joel Z. Leibo, Simon Osindero

Entreprise : Google DeepMind

## Abstract

Les "AI Agent Traps" sont des contenus malveillants conçus pour manipuler, tromper ou exploiter les agents qui les visitent.

Cet article présente le premier cadre systématique visant à comprendre cette menace émergente. 

Six types d’attaques :

- Les "Content Injection Traps" qui exploitent l’écart entre la perception humaine, l’analyse par les machines et le rendu dynamique ;
- Les "Semantic Manipulation Traps" qui corrompent le raisonnement de l’agent et ses processus internes de vérification ;
- Les "Cognitive State Traps" qui ciblent la mémoire à long terme, les bases de connaissances et les politiques comportementales apprises ;
- Les "Behavioural Control Traps" qui détournent les capacités de l’agent pour provoquer des actions non autorisées ;
- Les "Systemic Traps" qui exploitent les interactions entre agents pour provoquer des défaillances à grande échelle ;
- Les "Human-in-the-Loop Traps" qui exploitent les biais cognitifs afin d’influencer un superviseur humain.

Cette recherche n’est spécifique à aucun agent ni modèle particulier. En cartographiant cette nouvelle surface d’attaque, ce papier met en évidence des lacunes critiques dans les mécanismes de défense actuels et propose un programme de recherche visant à sécuriser l’ensemble de l’écosystème des agents.

## Contenu

Ces traps s’appuient sur trois domaines de recherche convergents : 

1) Le Machine Learning Adversarial (AML) montre que des inputs bien conçus peuvent perturber fortement les modèles. Ces attaques peuvent permettre de modifier prédictions, générations ou explications d'un modèle notamment.

2) La sécurité web fournit les mécanismes de distribution des attaques. Des techniques comme le cloaking permettent de servir un contenu différent aux machines et aux humains, ce qui peut être adapté pour cibler spécifiquement des agents IA. Des contenus malveillants peuvent ainsi rester cachés jusqu’à certaines conditions ou requêtes.

3) La sécurité des IA explore les contournements des garde-fous, notamment via le jailbreaking, les injections de prompt directes ou indirectes, et les attaques multimodales. Des techniques comme le RAG peuvent être exploitées via du data poisoning, et même les données d’entraînement peuvent être corrompues. Ces approches montrent que les modèles peuvent être manipulés à plusieurs niveaux.

### Content Injection Traps

Ciblage de la phase d'entrée des données directement, en faisant en sorte que les humains ne voient rien et que les agents IA voient du contenu.

Quatre vecteurs principaux :
- Web-standard obfuscation (HTML caché, métadonnées, polices malveillantes qui changent le rendu, etc.)
- Dynamic cloaking (contenu injecté dynamiquement seulement si un agent est détecté)
- Steganographic payloads (pixels, son, etc.)
- Syntactic masking (commandes cachées dans du LaTeX ou du Markdown)

### Semantic Manipulation Traps

Ces attaques ciblent au contraire le raisonnement de l’agent au lieu de l'entrée brute. 

Trois vecteurs principaux : 
- Biased phrasing / framing (mots biaisés ou avec un ordre différent, ton autoritaire, etc.)
- Oversight & critic evasion (misdirections et scénarios fictifs)
- Persona hyperstition (boucle de rétroaction où les données publiques d'un modèle influencent son comportement)

### Cognitive State Traps

Ces attaques visent la mémoire et l’apprentissage de l’agent (court et long terme).

Trois vecteurs principaux : 
- RAG Knowledge Poisoning (injection de fausses informations dans les bases)
- Latent memory poisoning (injection de fausses informations dans la mémoire persistante)
- Contextual learning traps (manipulation de l'apprentissage)

### Behavioural Control Traps

Ces attaques visent directement les actions de l’agent.

Trois vecteurs principaux : 
- Embedded Jailbreak Sequences (Jailbreak caché dans du contenu externe)
- Data Exfiltration Traps (tentative de faire exfiltrer des données sensibles à l'agent)
- Sub-agent Spawning (l'agent est poussé à créer d'autres agents)

### Systemic Traps 

Ces attaques exploitent le comportement collectif de plusieurs agents. Même si chaque agent agit “correctement”, le système global devient instable. 

Vecteurs principaux : 
- Congestion Traps (saturation et genre de DDoS)
- Interdependence Cascades (effet domino et équivalent d'un flash crash financier)
- Tacit Collusion (coordination implicite entre agents via des signaux publics.)
- Compositional Fragment Traps (payload malveillant divisé en morceaux et rassemblé quand plusieurs agents combinent les informations)
- Sybil Attacks (création de faux agents pour influencer le système)

### Human-in-the-Loop Traps

Ces attaques ciblent simplement l’humain via l’agent. 

Vecteurs principaux : 
- Liens de phishing
- Réponses très techniques que l'humain va valider sans comprendre
- ...

## Mitigation 

La protection et la correction sont compliquées parce que les attaques sont subtiles et ressemblent à du contenu normal. De plus, il est toujours très dur de savoir quelle source a causé un comportement compromis. 

Pistes principales : 
- Exposer le modèle à des exemples adversariaux pendant l'entraînement.
- Utiliser des approches comme Constitutional AI pour établir des règles de comportement.
- Scanner les contenus et les sources en amont. 
- À l'échelle du web, développer des systèmes de réputation des sites et améliorer la transparence ainsi que la traçabilité sur les sources.

Un autre problèmes est que les lois actuelles sont en retard sur ces problématiques. En effet, il est très dur de définir qui est responsable si un agent fait une action illégale (opérateur, fournisseur du modèle, site web avec un contenu offensif, etc.)

Ces problèmes nécessiteront une collaboration entre chercheurs, industrie et régulateurs.
