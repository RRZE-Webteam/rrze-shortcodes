WordPress-Plugin:

Shortcodes für Einbindung von
- LaTeX-Code 
- RSS-Feeds
- YouTube-Videos (ohne Cookies)

Beispiel LaTeX: [latex color="000000" background="00ff00" size="4"][/latex]

	mögliche Attribute:
	color (Hexadezimal-Farbwert)
	background (Hexadezimal-Farbwert)
	size  (LaTeX-Größe, Standardwert = 0):
		-4 \tiny
		-3 \scriptsize
		-2 \footnotesize
		-1 \small
		0 \normalsize (12pt)
		1 \large
		2 \Large
		3 \LARGE
		4 \huge

Beispiel RSS-Feed: [rss url='http://blogs.fau.de/webworking/feed' show_description=1 show_date=1 date_format='j. F Y']

	mögliche Attribute:
	title (Standardwert = '')
	url (Standardwert = 'http://blogs.fau.de/rrze/feed')
	items (Standardwert = '5')
	show_title (Standardwert = 0)
	show_description (Standardwert = 0)
	show_author (Standardwert = 0)
	show_date (Standardwert = 0)
	show_source (Standardwert = 0)
	max_description_words (Standardwert = '25')
	date_format (Standardwert siehe Einstellungen/Zeitformat)

Beispiel YouTube: [ytembed align=middle width=300 norel=0]PTVrTEda4wk[/ytembed]

        Innerhalb des Shortcodes muss der Code zum Video angegeben werden.

	mögliche Attribute:
	align (Standardwert = left):
		left (linksbündige Ausrichtung)
		middle (mittige Ausrichtung)
		right (rechtsbündige Ausrichtung)
	width (Standardwert = WordPress-Defaultwerte)
	cookie (Standardwert = no):
		yes (Video wird unter Einbindung von Cookies angezeigt)
		no (Video wird ohne Einbindung von Cookies angezeigt)
	norel (Standardwert = 1):
		0 (nach dem Video werden ähnliche Videos angezeigt)
		1 (nach dem Video werden keine ähnlichen Videos angezeigt)
	yttext (Standardwert = yes):
		yes (Link zu YouTube-Video wird unterhalb der Vorschau angezeigt)
		no (nur das YouTube-Video wird angezeigt)