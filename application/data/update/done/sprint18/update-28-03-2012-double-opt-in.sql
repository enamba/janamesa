--
-- default sql script fuer tabellen, bitte alles auskommentieren
-- damit es waehrend eines builds nichts ausgefuehrt wird

update newsletter_recipients set affirmed=1 where status=1;