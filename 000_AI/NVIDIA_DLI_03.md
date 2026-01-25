# Cours 3 - Augment your LLM Using Retrieval Augmented Generation

RAG (Retrieval Augmented Generation) = Technique d'amélioration d'un LLM avec une base de données externe pour limiter les hallucinations et avoir de meilleures réponses.

À ne pas confondre avec le fine-tuning classique, plus coûteux et difficile à maintenir, qui consiste à ajouter des données dans le modèle mais qui seront "apprises" et deviendront statiques.

Les deux peuvent toutefois être combinés et ne sont pas incompatibles.

Exemple de chaîne : 

1) Ingestion des documents
2) Passage par un modèle d'embedding pour unifier
3) Stockage dans une "Vector DB" (Weaviate, Milvus, etc.)
4) Utilisation de ces nouvelles données à chaque requête d'utilisateur si besoin

Il existe déjà pas mal de frameworks pour aider à faire du RAG : 
- LangChain
- LlamaIndex
- Triton
- ...
