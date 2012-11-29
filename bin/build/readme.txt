Build Process:

Der Build Prozess ist in 3 Teile unterteilt

1) Staging

Es wird eine neue Versionsnummer generiert und die Sourcen aus dem Master geholt und angepasst (jsMin, etc)

Anschließend wird daraus eine Paket gebaut, dass völlig Domain unabhängig ist

Beim ausrollen des Staging Systems auf die Server (Liste ist definiert in der properties.ini) werden die entsprechenden Anpassungen für die Domains vorgenommen.
Dazu wird aus dem config Verzeichnis die Basis und eine Domainspezifische config abgelegt. Konfigurationen sollten in die jeweilige Datei eingepflegt werden (z.b. UA Codes
in die Domain spezifische, SMS Versand Zugangsdaten in die Basis Datei)

Die htaccess Datei ist global und mit Platzhaltern gefüllt (DOMAIN_NAME - lieferando.de, DOMAIN_REGEX - lieferando\.de)

2) Deploy

Eine ausgewählte Version wird deployed

3) Static

aktueller Master wird auf die Static Server ausgelagert