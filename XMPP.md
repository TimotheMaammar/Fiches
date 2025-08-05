# XMPP

## Connexion avec SSL

    openssl s_client -connect $IP:5222 -starttls xmpp

## Récupération du numéro / domaine

    <stream:stream to='localhost' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' version='1.0'>

## Tentative de connexion avec admin/admin

    <stream:stream to='$NUMERO' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' version='1.0'>

    <auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='PLAIN'>AGFkbWluAGFkbWlu</auth>

## Énumération des commandes

    <iq from='admin@$NUMERO/XXXYYYZZZ' id='disco_info_1' to='$NUMERO' type='get'><query xmlns='http://jabber.org/protocol/disco#info'/></iq>

## Listing des utilisateurs si la commande de recherche est possible

    <iq type='set' id='search_2' to='search.$NUMERO'>
      <query xmlns='jabber:iq:search'>
        <nick>*</nick>
      </query>
    </iq>

## Énumération des noeuds

    <iq type='get' id='pubsub_items_enum' to='pubsub.$NUMERO'>
      <query xmlns='http://jabber.org/protocol/disco#items'/>
    </iq>
