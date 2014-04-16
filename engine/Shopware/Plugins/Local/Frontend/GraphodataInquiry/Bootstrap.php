<?php
/**
* Der Klassenname der Bootstrap setzt sich immer aus den gleichen Bestandteilen zusammen und
* entspricht weitestgehend dem Pfad, in dem das Plugin liegt.
*
* Das Plugin \Shopware\Plugins\Commercial\Backend\SwagTest hat beispielsweise eine Bootstrap
* mit dem Namen Shopware_Plugins_Backend_SwagTest.
*
* Der Pfad-Bestandteil Local/Default/Community/Commercial wird nicht berücksichtigt,
* die Plugins können zwischen diesen Ordnern frei verschoben werden.
*
* Die Zuordnung zu einem der drei Bereiche Frontend/Backend/Core ist zwar obligatorisch
* hat aber keine funktionale Bedeutung - sie dient einzig einer groben Einordnung
* zur Übersicht.
*
*/
class Shopware_Plugins_Frontend_GraphodataInquiry_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * In der getCapabilities-Methode kann der Entwickler beeinflussen,
     * welche Funktionen der PluginManager dem Nutzer zur Verfügung stellt:
     *
     * install: Ist es möglich, das Plugin zu (de)installieren?
     * enable: Ist es möglich, das Plugin zu (de)aktivieren?
     * update: Kann das Plugin aktualisiert werden?
     *
     */
    public function getCapabilities()
    {
        return array(
            'install' => true,
            'enable' => true,
            'update' => false
        );
    }
 
    /**
     * Gibt den Marketing-Namen des Plugins zurück.
     */
    public function getLabel()
    {
        return 'Graphodata Inquiry';
    }
 
    /**
     * Gibt die Version des Plugins als String zurück
     */
    public function getVersion()
    {
        return "1.0.0";
    }
 
    /**
    * Gibt die gesammelten Plugin-Informationen zurück
    *
    */
    public function getInfo() {
        return array(
            // Die Plugin-Version.
            'version' => $this->getVersion(),
            // Copyright-Hinweis
            'copyright' => 'Copyright (c) 2014, graphodata AG',
            // Lesbarer Name des Plugins
            'label' => $this->getLabel(),
            // Info-Text, der in den Plugin-Details angezeigt wird
            'description' => file_get_contents($this->Path() . 'info.txt'),
            // Anlaufstelle für den Support
            'support' => 'http://www.graphodata.de',
            // Hersteller-Seite
            'link' => 'http://www.graphodata.de',
            // Änderungen
            'changes' => array(
                '1.0.0'=>array('releasedate'=>'2014-03-20', 'lines' => array(
                    'Erster Release'
                ))
            ),
            // Aktuelle Revision des Plugins
            'revision' => '1'
        );
    }
 
    /**
     * Die Update-Methode wird ausgeführt, wenn ein bestehendes Plugin
     * durch den Nutzer aktualisiert wird.
     * War das Update erfolgreich, muss 'true' zurück gegeben werden
     */
    public function update($version)
    {
        // Hier können bspw. Änderungen an der Tabellenstruktur des Plugins
        // vorgenommen werden
        return true;
    }
 
    /**
     * Die Install-Methode wird bei der Installation des Plugins ausgeführt.
     * Hier wird bspw. die Datenstruktur des Plugins erzeugt.
     *
     * Auch das Generieren von Attribut-Models, das Erstellen der
     * Konfigurations-Elemente oder das Registrieren einer neuen
     * Zahlungsart geschieht zur Installationszeit
     */
    public function install()
    {
        // Installationslogik, Aufrufen einer Helfer-Methode, um
        // das Plugin auf bestimmte Events zu registrieren
        $this->subscribeEvents();
 
       
 
        // Alternativ kann auch ein Array zurückgegeben werden:
        return array(
            'success' => true,
            'message' => 'Install was successfull'
        );
    }
 
    /**
     * Die Uninstall-Methode wird ausgeführt, wenn ein Plugin durch den
     * Benutzer deinstalliert wird.
     * Hier können bspw. Datenstrukturen wieder entfernt werden.
     *
     */
    public function uninstall()
    {
        // Falls bei der Installation neue Attribute erzeugt wurden,
        // können diese bei der Deinstallation wieder entfernt werden.
        /*$this->Application()->Models()->removeAttribute(
            // Betroffene Attribut-Tabelle
            's_articles_attributes',
            // Eindeutiges Entwicklerkürzel
            'ppassmann',
            // Attribut
            'ppgdag'
        );*/
 
        // Auch nach dem Entfernen von Attributen ist die Neu-Generierung
        // der entsprechenden Models erforderlich
        /*$this->getEntityManager()->generateAttributeModels(array(
            's_articles_attributes'
        ));*/
 
        return true;
    }
 
    /**
     * Helfer-Methode um das Plugin auf bestimmte Events zu registrieren
     */
    private function subscribeEvents()
    { 
        // Registriert das Plugin auf den postDispatch des Checkout-Controllers
        // Das Event wird nach der Verarbeitung durch den Controller gefeuert
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Forms',
            'onFormsPostDispatch'
        );

    }
 

    public function onFormsPostDispatch(Enlight_Event_EventArgs $arguments)
    {
		
		// Controller, dessen postDispatch ausgeführt wird
        $subject = $arguments->getSubject();
 
        // Verarbeitetes Request-Objekt der Controller-Action
        $request = $subject->Request();
 
        // View der aktuellen controllerAction
        $view = $subject->View();

		// Query vom Request
		$query = $request->getQuery();
		
		// Postdata vom Controller
		$post = $subject->_postData;

		
		//Session abfragen ob es eine Anfrage ist
		if(Shopware()->Session()->inquiry)
		{
			$msg = $this->buildMsg($post);
			$mail = $this->buildMail($post['74'], $msg);
			
			$mail->Send();
			Shopware()->Session()->inquiry = false;
		}
		
		//Wenn der Controller auf ein Anfrageformular zeigt die Sessionvariable setzen
		if($query['controller'] === 'support' && ($query['action'] === 'index') && array_key_exists('sInquiry', $query))
		{
			Shopware()->Session()->inquiry = true;
		} else {
			Shopware()->Session()->inquiry = false;
		}
		
    }

	public function buildMsg($data)
	{
		$msg = '<div style="font-family:arial; font-size:12px;">
		<p>Hallo '.$data['72'].' '.$data['75'].' '.$data['71'].'</p>
		<p>Wir werden uns so schnell wie möglich bei Ihnen melden.</p>
		<p>Ihre Anfrage lautete:</p>
		<p>'.$data['69'].'</p>';

		return $msg;
	}
	
	public function buildMail($email, $msg)
	{
		$mail = Shopware()->Mail();
		$mail->IsHTML(1);
		$mail->From		= Shopware()->Config()->Mail;
		$mail->Fromname	= Shopware()->Config()->Mail;
		$mail->Subject = 'Ihre Anfrage - Furthof Antikmöbel';
		$mail->Body = $msg;
		$mail->ClearAddresses();
		$mail->AddAddress($email);

		return $mail;
	}

    /**
     * Wenn das Plugin eigene Models bereit stellt und verwendet,
     * muss das Model-Verzeichnis in der afterInit-Methode der
     * Bootstrap registriert werden. Dafür gibt es die Helfer-Methode
     * registerCustomModels. Für diese muss ein Verzeichnis "Models"
     * im Plugin-Verzeichnis existieren
     *
     */
    public function afterInit()
    {
        //$this->registerCustomModels();
    }
 
 
}