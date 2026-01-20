# Cours 2 - Agentic AI Explained

Agent = Entité logicielle avec un système autonome à base d'IA, qui apprend et agit selon son environnement. 

Un agent a une boucle simple : 
- Perception avec des capteurs ou de l'information donnée directement
- Raisonnement basé sur ses règles et sa logique
- Mise à jour de l'environnement selon ses capacités
- Apprentissage selon le feedback

Generative AI + Agentic AI = On-demand semantic reasoning

L'architecture générale d'une IA agentique reprend un peu la boucle du dessus : 
- Couche de perception
- Couche de décision
- Couche d'action
- Couche d'apprentissage

Les quatre grands principes utilisés par les ingénieurs pour concevoir des agents robustes sont les suivants : 

1) Agentic Decomposition : diviser les objectifs en rôles et tâches spécifiques
2) Context Engineering : soigner ce que l'agent voit en triant et nettoyant
3) Workflow Design : planifier les tâches étape par étape
4) Model Selection : choisir le bon module pour chaque tâche

Principaux "Design Patterns" pour la création de systèmes autonomes ou semi-autonomes : 

- Reflection Pattern : l'agent évalue et améliore son propre travail
- Tool Use Pattern : l'agent utilise des outils externes du genre BDD ou calculatrice
- Hierarchical Pattern : l'agent délègue à d'autres modèles spécialisés qui sont à son service
- Reactive Pattern : l'agent réagit en temps réel à son environnement et aux évènements

Dans les systèmes multi-agents il y a souvent des rôles prédéfinis assez courants : 

- Planner : agent qui transforme les objectifs globaux en étapes concrètes
- Executor : agent qui réalise les tâches définies par le Planner
- Critic / Evaluator : agent qui corrige et évalue la qualité des résultats
- Tool-User / Connector : interface entre un agent et le monde extérieur
- Coordinator : chef d'orchestre qui synchronise les autres agents

La première étape d'un projet peut par exemple être de construire un simple Agent ReAct (Reasoning and Acting) qui suit la boucle présentée de manière basique.

Exemples de frameworks : 
- LangChain
- LangGraph
- Crew.a
