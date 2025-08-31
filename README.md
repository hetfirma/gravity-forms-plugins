=== GF NL Address Autocomplete ===
Contributors: webventiv
Tags: gravity forms, address, autocomplete, pdok, netherlands
Requires at least: 6.0
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Voegt NL-adres-autocomplete toe aan het **standaard** Gravity Forms Adres-veld via PDOK Locatieserver (geen API key nodig).

== Beschrijving ==

- Gebruik het normale **Address**-veld van Gravity Forms (geen custom field type).
- Zet bij dat veld de CSS-klasse: `nl-address-autocomplete`.
- Deze plugin laadt een klein script dat bij het **straat**-veld (input `_1`) suggesties toont en bij selectie **postcode** (`_5`) en **plaats** (`_3`) invult.
- Land (`_6`) wordt optioneel op "Nederland" gezet (aanpasbaar).

Werkt met AJAX en meerdere formulieren op dezelfde pagina.

== Installatie ==

1. Upload de zip via *Plugins → Nieuwe plugin → Plugin uploaden*.
2. Activeer **GF NL Address Autocomplete**.
3. Open je Gravity Form, voeg een **Address**-veld toe en zet bij **Custom CSS Class**: `nl-address-autocomplete`.
4. Publiceer de pagina met het formulier.

== Veelgestelde vragen ==

= Ik wil een andere provider dan PDOK gebruiken =  
Pas `assets/wbv-nl-addr.js` aan (de `fetchSuggest`/`fetchDetails` functies).

= Laadt het script overal? =  
Nee. Het wordt geënqueue’d wanneer GF scripts voor het formulier laadt. De init-binding gebeurt alleen voor Address-velden met de CSS-klasse.

== Changelog ==
= 1.0.0 =
* Eerste release.
