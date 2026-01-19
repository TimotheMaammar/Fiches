# Cours 1 - Generative AI explained

Débuts de l'IA = Principalement de la classification du genre reconnaître des photos de chats mais progressivement étendue à d'autres formats.

Generative AI = Algorithmes permettant de créer du nouveau contenu réaliste reflétant les caractéristiques de données d'entraînement.

La principale manière d'entraîner un modèle de langage est simplement de lui faire prédire le prochain mot dans une séquence. Le texte est découpé en tokens qui peuvent être des mots ou même des syllabes, pour mieux unifier tous les langages.

Few-shot learning = Quelques exemples puis question     
Zero-shot learning = Aucun exemple et question directement

Le contenu humain sur Internet n'est pas du tout optimal pour apprendre la résolution de problème à un modèle puisqu'il n'est pas directement écrit sous forme { clé : valeur } et qu'il y a beaucoup de choses implicites et ambiguës, d'où l'importance de la supervision humaine et de l'apprentissage par renforcement dans la phase d'apprentissage.

Au-delà de savoir utiliser des outils, les modèles de langage peuvent aussi être connectés aux autres modèles (TTS, TT3D, etc.). Cette composition se fait avec ce qu'on appelle des "embeddings" et qui consistent en une sorte d'étape d'entraînement supplémentaire où l'on va créer un espace abstrait composé d'une somme de vecteurs. Les vecteurs sont des projections de ces images et de ces textes. Voir "CLIP" de OpenAI.
