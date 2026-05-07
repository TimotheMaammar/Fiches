# Claude Code in Action

Assistant de code = Système complet qui combine un modèle de langage avec des outils qui permettent d'agir concrètement.

Un assistant de code suit un process un peu similaire à celui d'un développeur humain : 
1) Récupérer le contexte
2) Formuler un plan
3) Agir

Instructions pour l'installation : https://code.claude.com/docs/en/quickstart 

Pour les tâches complexes qui demandent d’explorer beaucoup de fichiers ou de comprendre l’architecture du projet, on peut activer un "Planning Mode" avec "/plan" ou Shift+Tab deux fois de suite.

Dans ce mode, Claude va :
- lire davantage de fichiers du projet
- analyser le code plus en profondeur
- préparer un plan d’implémentation détaillé et l'expliquer
- attendre une validation avant de modifier quoi que ce soit

On peut modifier le plan en live avec Ctrl+G par exemple.

Le niveau d'effort de réflexion de Claude peut quant à lui être contrôlé avec "/effort" et on peut afficher son raisonnement détaillé avec Ctrl+O. Et le mot "ultrathink" dans une requête permet de demander beaucoup plus de réflexion pour cette seule requête.

Appuyer sur Échap pendant une réponse permet d'interrompre Claude, pour lui éviter de continuer à partir dans une mauvaise direction par exemple.

Si Claude répète souvent une même erreur il faut modifier son fichier CLAUDE.md ou passer par la commande "/memory" pour ajouter des règles.

La commande "/compact" résume toute la conversation en conservant les informations importantes apprises par Claude. Et la commande "/clear" permet de démarrer une nouvelle conversation depuis un contexte vierge.

On peut aussi créer des commandes nous-mêmes en faisant un sous-dossier "commands" dans le dossier .claude puis en faisant un fichier Markdown NOM_COMMANDE.md.

L'exemple le plus populaire de serveur MCP à utiliser avec Claude est Playwright : 

	claude mcp add playwright npx @playwright/mcp@latest

Par défaut, Claude demandera une autorisation chaque fois qu’il utilisera un outil MCP.

Pour éviter ces confirmations répétitives, on peut préautoriser le serveur grâce au fichier .claude/settings.local.json :

```
{
  "permissions": {
    "allow": ["mcp__playwright"],
    "deny": []
  }
}
```

Sans MCP, Claude ne voit que le code.     
Avec Playwright, Claude peut :      
- ouvrir une application dans un navigateur
- générer un composant
- observer le rendu visuel réel
- retester automatiquement
- ...

Exemple de demande : 

```
Navigate to localhost:3000, generate a basic component, review the styling, and update the generation prompt at @src/lib/prompts/generation.tsx to produce better components going forward.
```

Il existe plein d'autres serveurs MCP pour :
- bases de données
- tests d’API
- monitoring
- intégrations Cloud
- ...

Claude Code propose également une intégration officielle avec GitHub via GitHub Actions.

Cela permet à Claude : 
- d’agir directement dans GitHub
- d’intervenir sur les issues et pull requests
- d’automatiser des reviews

Commande pour l'installation : 

	/install-github-app

Quand Claude est mentionné dans une PR ou une issue, il peut :
- analyser la demande
- créer un plan d’action
- exécuter des tâches
- accéder au code du dépôt
- répondre directement dans l’issue ou la PR

Dans GitHub Actions, il faut autoriser explicitement chaque outil.       
Exemple : 

	allowed_tools: "Bash(npm:*),Bash(sqlite3:*),mcp__playwright__browser_snapshot"

## Hooks et SDK

Les hooks permettent d’exécuter automatiquement des commandes avant qu’un outil soit utilisé par Claude ou après son exécution. C’est extrêmement utile pour automatiser des workflows de développement ou pour bloquer des modifications dangereuses entre autres. 

Types de hooks : 
- PreToolUse
- PostToolUse
- Notification
- Stop
- SubagentStop
- PreCompact
- SessionEnd
- ...

Penser à utiliser des chemins absolus pour éviter toute forme d'ambiguïté.

Les hooks qui servent à faire une review de code après toute modification sont particulièrement utiles.      
Exemples : 
- Vérification des éventuelles duplications
- Vérifier les problèmes de types pour les langages typés (TypeScript, Rust, etc.)

Le Agent SDK permet d’utiliser Claude Code programmatiquement depuis Python et TypeScript.       

Installation : 
```
mkdir sdk-demo  
cd sdk-demo  
npm init -y  
npm install @anthropic-ai/claude-agent-sdk
```

Exemple minimal : 

```js
import { query } from "@anthropic-ai/claude-agent-sdk";

const prompt = "List the files in the current directory";

for await (const message of query({ prompt })) {
  console.log(JSON.stringify(message, null, 2));
}
```
