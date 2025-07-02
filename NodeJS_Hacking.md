## Fonctions à surveiller

### Généralités

- eval() => RCE
- Function() => RCE
- setTimeout() => Équivalent à eval() si façon 'setTimeout("doSomething()", 1000)'
- setInterval() => Même principe que setTimeout()
- unserialize() => Insecure Deserialization
- child_process.exec() => Injection de commande
- child_process.spawn() => Injection de commande si arguments dynamiques 
- require() => Chargement arbitraire de modules si chemins contrôlés
- fs.readFile() / fs.createReadStream() => LFI ou Path Traversal
- http.request() / https.request() => SSRF
- url.parse() => SSRF
- Object.assign() => Prototype Pollution
- JSON.parse() => Prototype Pollution si objet fusionné sans vérification
- merge() => Prototype Pollution si la source a des propriétés dangereuses
- redirect() => Open Redirect si argument contrôlé

### Injections NoSQL

- find()
- findOne()
- findById()
- countDocuments()
- deleteOne()
- deleteMany()
- remove()
- updateOne()
- updateMany()
- replaceOne()
- findOneAndUpdate()
- findOneAndDelete()
- findOneAndRemove()
- findByIdAndUpdate()
- findByIdAndRemove()
- findByIdAndDelete()
- distinct()
- aggregate()
- where()
- or()
- and()

## Sources

- req.body
- req.query
- req.params
- req.headers
- req.cookies
- req.signedCookies
- req.files
- req.hostname
- req.ip
- req.ips
- req.get()
- req.originalUrl
- req.url
## Exemples de fichiers intéressants à chercher

- /data/app.js
- /data/index.js
- /data/server.js
- /data/config.env
- /data/routeurs/adminRoutes.js
- /data/routeurs/inviteRoutes.js

## Quelques liens

https://github.com/aadityapurani/NodeJS-Red-Team-Cheat-Sheet
https://github.com/lirantal/awesome-nodejs-security
https://book.hacktricks.xyz/pentesting-web/deserialization/nodejs-proto-prototype-pollution/prototype-pollution-to-rce
https://medium.com/@sebnemK/node-js-rce-and-a-simple-reverse-shell-ctf-1b2de51c1a44
