# GraphQL

## Introspection

```js
{__schema{queryType{name}mutationType{name}subscriptionType{name}types{...FullType}directives{name description locations args{...InputValue}}}}fragment FullType on __Type{kind name description fields(includeDeprecated:true){name description args{...InputValue}type{...TypeRef}isDeprecated deprecationReason}inputFields{...InputValue}interfaces{...TypeRef}enumValues(includeDeprecated:true){name description isDeprecated deprecationReason}possibleTypes{...TypeRef}}fragment InputValue on __InputValue{name description type{...TypeRef}defaultValue}fragment TypeRef on __Type{kind name ofType{kind name ofType{kind name ofType{kind name ofType{kind name ofType{kind name ofType{kind name ofType{kind name}}}}}}}}
```

Essayer également d'ajouter des caractères spéciaux après `__schema` au cas où le blocage vienne d'une expression régulière ou d'un filtre sur ce terme exact. 

Penser notamment à l'espace insécable \u00A0
## Pas d'introspection

Essayer de faire cracher des noms au serveur en mettant des mots approchants et en comptant sur la correction native.

Exemple de requête : 

```
{"query":"mutation { createUser(name: alice, password: abc ) }"}
```

Exemple de réponse intéressante : 

```
{"errors":[{"message":"Cannot query field \"createUser\" on type \"RootMutation\". Did you mean \"createAsset\", \"createFolder\", or \"createBrand\"?","locations":[{"line":1,"column":12}]}],"data":null}
```
## Tools 

https://github.com/nikitastupin/clairvoyance

	pip3 install clairvoyance
	clairvoyance https://url.com/graphql -o schema.json -H '{"Token": "12345"}' -k

https://github.com/dolevf/graphql-cop

	git clone https://github.com/dolevf/graphql-cop
	cd graphql-cop
	pip install -r requirements.txt
	python .\graphql-cop.py -t https://url.com/gpql --header '{"ABC": "XYZ" }'


https://github.com/swisskyrepo/GraphQLmap

	git clone https://github.com/swisskyrepo/GraphQLmap
	cd ./GraphQLmap
	python3 -m venv .venv
	./.venv/bin/activate
	sudo python3 setup.py install
	python3 ./bin/graphqlmap -u https://url.com/graphql --headers '{ "Token": "12345" }' --proxy http://127.0.0.1:8080


