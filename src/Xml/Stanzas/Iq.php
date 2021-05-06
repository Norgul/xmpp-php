<?php

namespace Norgul\Xmpp\Xml\Stanzas;

class Iq extends Stanza
{
    public function getRoster()
    {
        $query = "<query xmlns='jabber:iq:roster'/>";
        $xml = "<iq type='get' id='{$this->uniqueId()}'>{$query}</iq>";

        $this->socket->send($xml);
    }

    public function addToRoster(string $name, string $forJid, string $from, string $groupName = null)
    {
        $group = $groupName ? "<group>{$groupName}</group>" : null;
        $item = "<item jid='{$forJid}' name='{$name}'>{$group}</item>";
        $query = "<query xmlns='jabber:iq:roster'>{$item}</query>";
        $xml = "<iq type='set' id='{$this->uniqueId()}' from='{$from}'>{$query}</iq>";

        $this->socket->send($xml);
    }

    public function removeFromRoster(string $jid, string $myJid)
    {

        $item = "<item jid='{$jid}' subscription='remove'/>";
        $query = "<query xmlns='jabber:iq:roster'>{$item}</query>";
        $xml = "<iq type='set' id='{$this->uniqueId()}' from='{$myJid}'>{$query}</iq>";

        $this->socket->send($xml);
    }

    public function setResource(string $name)
    {
        if (!trim($name)) {
            return;
        }

        $resource = "<resource>{$name}</resource>";
        $bind = "<bind xmlns='urn:ietf:params:xml:ns:xmpp-bind'>{$resource}</bind>";
        $xml = "<iq type='set' id='{$this->uniqueId()}'>{$bind}</iq>";

        $this->socket->send($xml);
    }

    public function setGroup(string $name, string $forJid)
    {
        $group = "<group>{$name}</group>";
        $item = "<item jid='{$forJid}'>{$group}</item>";
        $query = "<query xmlns='jabber:iq:roster'>{$item}</query>";
        $xml = "<iq type='set' id='{$this->uniqueId()}'>{$query}</iq>";

        $this->socket->send($xml);
    }

    public function getServerVersion()
    {
        $query = "<query xmlns='jabber:iq:version'/>";
        $xml = "<iq type='get' id='{$this->uniqueId()}'>{$query}</iq>";

        $this->socket->send($xml);
    }

    public function getServerFeatures()
    {
        $query = "<query xmlns='http://jabber.org/protocol/disco#info'></query>";
        $xml = "<iq type='get' id='{$this->uniqueId()}'>{$query}</iq>";

        $this->socket->send($xml);
    }

    public function getServerTime()
    {
        $query = "<query xmlns='urn:xmpp:time'/>";
        $xml = "<iq type='get' id='{$this->uniqueId()}'>{$query}</iq>";

        $this->socket->send($xml);
    }

    public function getFeatures(string $forJid)
    {
        $query = "<query xmlns='http://jabber.org/protocol/disco#info'></query>";
        $xml = "<iq type='get' to='{$forJid}'>{$query}</iq>";

        $this->socket->send($xml);
    }

    public function ping()
    {
        $query = "<query xmlns='urn:xmpp:ping'/>";
        $xml = "<iq type='get' id='{$this->uniqueId()}'>{$query}</iq>";

        if($this->socket->send($xml) === false)
        {
            return false;
        }
    }
}
