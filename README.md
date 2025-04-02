# **OpenSim-RemoteAdmin**  

OpenSim RemoteAdmin PHP-Skript  

## **Beschreibung:**

Ein PHP-Skript zur einfachen Nutzung der Remote-Admin-Funktionalität in OpenSim.  

## **Installation:**  

1. **RemoteAdmin aktivieren**  
   Lies die Dokumentation auf **[OpenSimulator.org](http://opensimulator.org/wiki/RemoteAdmin)**, um zu erfahren, wie du die Remote-Admin-Funktion aktivierst.  

2. **`config.php` bearbeiten**  
   - Trage die erforderlichen Informationen im Abschnitt `[RemoteAdmin]` ein.  
   - Lege den Speicherort für Datei-Uploads fest. Dies muss ein Verzeichnis sein, das **von OpenSim gelesen und von deinem Webserver beschrieben werden kann**.  

3. **Sicherheitsdateien anpassen**  
   - Bearbeite `.htaccess`, um die `AuthUserFile`-Zeile auf die `.htpasswd`-Datei zu verweisen.  
   - Erstelle oder bearbeite `.htpasswd` zur Authentifizierung mit einem Tool wie **[htaccesstools.com](http://www.htaccesstools.com/htpasswd-generator/)**.  

## **Verwendung:**  

- Das Skript **liest die Remote-Admin-Methoden** aus der Datei `RemoteAdmin.json`.  
- Nach Auswahl einer Methode wird **ein Formular generiert**, das die Anfrage an den OpenSim-Server sendet und die Ergebnisse anzeigt.  

## **Einstellungen in `RemoteAdmin.json`:**  

- **Standardwerte können gesetzt werden** mit `"default": ""`.  
- **Typen der Eingabefelder:**  
  - `string`, `hidden`, `readonly`, `boolean`, `file`, `int` oder `uuid`.  
  - (`int` und `uuid` werden standardmäßig als `string` behandelt.)  

## **Haftungsausschluss:**  

⚠ **Vorsicht beim Verwenden von RemoteAdmin!** Einige Methoden erlauben **dauerhafte Änderungen** an Regionen oder Benutzerdaten.  
🔒 Nutze die Einstellung `enabled_methods` in `[RemoteAdmin]` innerhalb der Datei `opensim.ini`, um den RemoteAdmin auf **nur die benötigten Funktionen** zu beschränken.  
