<?php
defined('ABSPATH') || exit;

function ww_legal_upsert_page(string $slug, string $title, string $content): int {
    $page = get_page_by_path($slug, OBJECT, 'page');
    $data = [
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_content' => $content,
    ];

    if ($page) {
        $data['ID'] = $page->ID;
        wp_update_post($data);
        return (int) $page->ID;
    }

    return (int) wp_insert_post($data);
}

$updated = date_i18n('d.m.Y');

$notice = '<!-- wp:paragraph --><p><strong>Entwurfshinweis:</strong> Dieser Rechtstext ist als praxisnaher Arbeitsentwurf für die Wichtelwerken-Plattform erstellt. Vor Livegang müssen die Platzhalter ergänzt und der Text anwaltlich geprüft werden, insbesondere wegen Marktplatzmodell, Zahlungsabwicklung, Anbieterrolle, Verbraucherrecht und DSA-Pflichten.</p><!-- /wp:paragraph -->';

$agbNutzer = <<<HTML
<!-- wp:heading --><h2>Allgemeine Geschäftsbedingungen für Käuferinnen, Käufer und sonstige Nutzer</h2><!-- /wp:heading -->
$notice
<!-- wp:paragraph --><p>Stand: {$updated}</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>1. Anbieter der Plattform</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Betreiberin der Plattform „Wichtelwerken“ ist die Wichtelwerken UG (haftungsbeschränkt) i.G., <strong>[vollständige Anschrift ergänzen]</strong>, vertreten durch <strong>[Vertretungsberechtigte ergänzen]</strong>, E-Mail: <strong>[E-Mail ergänzen]</strong> („Wichtelwerken“, „wir“).</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Wichtelwerken betreibt einen Online-Marktplatz für handgemachte, regionale, nachhaltige und sonstige sorgfältig kuratierte Produkte von Anbietern. Über die Plattform können Käufer Produkte entdecken, bestellen und bezahlen.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>2. Geltungsbereich</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Diese AGB gelten für die Nutzung der Plattform durch Käuferinnen, Käufer und sonstige Nutzer. Für Anbieter gelten ergänzend die gesonderten „AGB für Anbieter“.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Abweichende Bedingungen von Nutzern gelten nur, wenn Wichtelwerken ihnen ausdrücklich in Textform zugestimmt hat.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>3. Rolle von Wichtelwerken und Vertragsschluss</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wichtelwerken stellt die technische Plattform bereit und wickelt die Bestellung sowie Zahlung über den Marktplatz ab. Der Kaufvertrag über ein Produkt kommt grundsätzlich zwischen dem Käufer und dem jeweiligen Anbieter zustande, sofern das Produkt nicht ausdrücklich als Eigenangebot von Wichtelwerken gekennzeichnet ist.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Die Darstellung eines Produkts im Shop ist noch kein rechtlich bindendes Angebot, sondern eine Aufforderung zur Abgabe einer Bestellung. Der Käufer gibt durch Anklicken des Bestellbuttons im Checkout ein verbindliches Angebot ab. Die Annahme erfolgt durch Bestellbestätigung, Versandbestätigung oder Bereitstellung der Ware.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Wichtelwerken kann Bestellungen ablehnen oder stornieren, wenn gesetzliche Gründe, offensichtliche Fehler, fehlende Verfügbarkeit, Verdacht auf Missbrauch oder Verstöße gegen Plattformregeln vorliegen.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>4. Nutzerkonto und Gastbestellung</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Käufe können, soweit technisch angeboten, mit Kundenkonto oder als Gastbestellung erfolgen. Nutzer sind verpflichtet, bei Bestellung wahrheitsgemäße und aktuelle Angaben zu machen und Zugangsdaten vertraulich zu behandeln.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Wichtelwerken darf Konten vorübergehend sperren oder dauerhaft schließen, wenn konkrete Anhaltspunkte für Missbrauch, Rechtsverstöße, Zahlungsstörungen oder sonstige erhebliche Vertragsverletzungen bestehen.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>5. Produkte, Anbieterangaben und Verfügbarkeit</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Produktinformationen werden in der Regel vom jeweiligen Anbieter bereitgestellt. Wichtelwerken bemüht sich um Plausibilitätskontrollen, übernimmt aber keine Gewähr dafür, dass Anbieterangaben stets vollständig, aktuell oder fehlerfrei sind.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Bei handgemachten Produkten und Naturprodukten können geringfügige Abweichungen in Farbe, Form, Größe, Material, Verpackung oder Verarbeitung auftreten, soweit dies produktüblich und zumutbar ist.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>6. Preise, Steuern, Versandkosten und Zahlung</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Alle Preise verstehen sich, soweit nicht anders angegeben, als Bruttopreise einschließlich der jeweils anwendbaren Umsatzsteuer. Versandkosten und sonstige Kosten werden im Checkout angezeigt.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Die Zahlung wird über den Marktplatz bzw. einen von Wichtelwerken eingebundenen Zahlungsdienstleister abgewickelt. Der konkrete Zahlungsdienstleister, Zahlungsarten, Auszahlungsabläufe und zusätzliche Bedingungen sind vor Livegang zu ergänzen: <strong>[Zahlungsdienstleister/Payment Service Provider ergänzen]</strong>.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Der Käufer kann mit befreiender Wirkung an Wichtelwerken bzw. den Zahlungsdienstleister zahlen. Wichtelwerken leitet Anbietererlöse nach Maßgabe der Anbieter-AGB und Zahlungsdienstleisterbedingungen an den jeweiligen Anbieter weiter.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>7. Lieferung, Abholung und Gefahrübergang</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Lieferung und Versand erfolgen durch den jeweiligen Anbieter, sofern nicht ausdrücklich anders angegeben. Lieferzeiten sind produkt- und anbieterabhängig. Bei digitalen oder organisatorischen Leistungen gelten die jeweiligen Produktangaben.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Ist der Käufer Verbraucher, geht die Gefahr des zufälligen Untergangs und der zufälligen Verschlechterung grundsätzlich erst mit Übergabe der Ware an den Käufer oder eine empfangsberechtigte Person über.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>8. Widerrufsrecht für Verbraucher</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Verbrauchern steht bei Fernabsatzverträgen grundsätzlich ein gesetzliches Widerrufsrecht zu, sofern keine gesetzliche Ausnahme greift. Einzelheiten ergeben sich aus der Widerrufsbelehrung und den Produktinformationen des jeweiligen Anbieters.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Besondere Ausnahmen können insbesondere bei individuell angefertigten Waren, schnell verderblichen Waren, versiegelten Hygieneprodukten, entsiegelten Ton-/Video-/Softwareträgern oder anderen gesetzlich geregelten Fällen bestehen. Anbieter müssen solche Ausnahmen produktbezogen korrekt angeben.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Weitere Informationen finden Sie unter <a href="/widerruf-rueckgabe/">Widerruf und Rückgabe</a>.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>9. Gewährleistung und Reklamationen</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Es gelten die gesetzlichen Mängelrechte. Vertraglicher Ansprechpartner für produktbezogene Gewährleistungsansprüche ist grundsätzlich der jeweilige Anbieter. Wichtelwerken kann die Kommunikation technisch unterstützen und Reklamationen an Anbieter weiterleiten.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Bitte prüfen Sie gelieferte Produkte zeitnah und melden Sie Transportschäden, Falschlieferungen oder Mängel möglichst frühzeitig über die im Shop angegebenen Kontaktwege. Gesetzliche Verbraucherrechte bleiben hiervon unberührt.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>10. Bewertungen, Inhalte und Kommunikation</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Nutzer dürfen keine rechtswidrigen, beleidigenden, diskriminierenden, irreführenden, werblichen, personenbezogene Daten Dritter enthaltenden oder sonst missbräuchlichen Inhalte einstellen. Wichtelwerken darf solche Inhalte prüfen, entfernen und bei wiederholten Verstößen Konten einschränken.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>11. Verbotene Nutzungen</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Unzulässig sind insbesondere automatisiertes Auslesen, Manipulation von Bestellungen oder Bewertungen, Umgehung der Zahlungsabwicklung, rechtswidrige Produktangebote, Täuschung über Identität oder Eigenschaften sowie jede Nutzung, die Sicherheit, Verfügbarkeit oder Integrität der Plattform beeinträchtigt.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>12. Haftung von Wichtelwerken</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wichtelwerken haftet unbeschränkt bei Vorsatz, grober Fahrlässigkeit, Verletzung von Leben, Körper oder Gesundheit, nach dem Produkthaftungsgesetz sowie bei übernommenen Garantien.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Bei leicht fahrlässiger Verletzung wesentlicher Vertragspflichten haftet Wichtelwerken nur auf den vertragstypischen, vorhersehbaren Schaden. Wesentliche Vertragspflichten sind solche Pflichten, deren Erfüllung die ordnungsgemäße Durchführung des Plattformvertrags überhaupt erst ermöglicht und auf deren Einhaltung Nutzer regelmäßig vertrauen dürfen.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Für Produktmängel, Produktbeschreibungen und Erfüllung des Kaufvertrags haftet grundsätzlich der jeweilige Anbieter, soweit Wichtelwerken nicht selbst Verkäufer ist oder eigene Pflichtverletzungen vorliegen.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>13. Datenschutz</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Informationen zur Verarbeitung personenbezogener Daten finden Sie in der <a href="/datenschutzerklaerung/">Datenschutzerklärung</a>. Cookie-Einstellungen können über die <a href="/cookie-richtlinie/">Cookie-Richtlinie</a> bzw. den Consent-Banner verwaltet werden.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>14. Änderungen der Plattform und dieser AGB</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wichtelwerken darf die Plattform weiterentwickeln, Funktionen ändern oder einstellen, soweit dies Nutzern zumutbar ist. Änderungen dieser AGB werden Nutzern rechtzeitig mitgeteilt, soweit ein laufendes Vertragsverhältnis betroffen ist. Gesetzliche Rechte bleiben unberührt.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>15. Schlussbestimmungen</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Es gilt deutsches Recht unter Ausschluss des UN-Kaufrechts. Gegenüber Verbrauchern gilt diese Rechtswahl nur, soweit dadurch der Schutz zwingender Vorschriften des Staates des gewöhnlichen Aufenthalts nicht entzogen wird.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Sollten einzelne Bestimmungen unwirksam sein, bleibt die Wirksamkeit der übrigen Bestimmungen unberührt.</p><!-- /wp:paragraph -->
HTML;

$agbAnbieter = <<<HTML
<!-- wp:heading --><h2>Allgemeine Geschäftsbedingungen für Anbieter</h2><!-- /wp:heading -->
$notice
<!-- wp:paragraph --><p>Stand: {$updated}</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>1. Vertragspartner und Gegenstand</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Diese Anbieter-AGB gelten zwischen der Wichtelwerken UG (haftungsbeschränkt) i.G., <strong>[vollständige Anschrift ergänzen]</strong>, vertreten durch <strong>[Vertretungsberechtigte ergänzen]</strong>, E-Mail: <strong>[E-Mail ergänzen]</strong> („Wichtelwerken“) und Personen oder Organisationen, die über Wichtelwerken Produkte anbieten („Anbieter“).</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Wichtelwerken stellt eine Marktplatzinfrastruktur bereit. Anbieter können eigene Shops anlegen, Produkte einstellen, Bestellungen erhalten und Zahlungen über den Marktplatz abwickeln lassen. Der Kaufvertrag mit dem Endkunden kommt grundsätzlich zwischen Anbieter und Käufer zustande.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>2. Zulassung als Anbieter</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Die Nutzung als Anbieter setzt eine Registrierung, Annahme dieser Anbieter-AGB und Freischaltung durch Wichtelwerken voraus. Ein Anspruch auf Zulassung besteht nicht.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Anbieter müssen bei Registrierung vollständige, richtige und aktuelle Angaben machen. Dazu gehören insbesondere Name/Firma, ladungsfähige Anschrift, E-Mail, Telefonnummer, steuerliche Angaben, Bank-/Auszahlungsdaten, vertretungsberechtigte Personen und, soweit einschlägig, Registerdaten.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Handelt ein Anbieter gewerblich, muss er dies zutreffend kenntlich machen und alle gesetzlichen Informationspflichten gegenüber Verbrauchern erfüllen. Private Anbieter dürfen nicht den Eindruck eines gewerblichen Angebots erwecken, wenn die tatsächliche Tätigkeit gewerblich ist.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>3. KYBC, DSA und Nachverfolgbarkeit von Anbietern</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wichtelwerken kann Anbieterangaben vor Freischaltung und während der Nutzung prüfen und Nachweise verlangen, insbesondere zur Identität, Anschrift, Erreichbarkeit, Unternehmereigenschaft, Bankverbindung, Steuerdaten und Produktverantwortlichkeit.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Bei Online-Marktplätzen können nach dem Digital Services Act insbesondere Pflichten zur Nachverfolgbarkeit von Unternehmern bestehen. Anbieter verpflichten sich, erforderliche Angaben und Nachweise unverzüglich bereitzustellen und aktuell zu halten. Wichtelwerken kann Angebote sperren oder Auszahlungen zurückhalten, wenn erforderliche Angaben fehlen, offensichtlich unrichtig sind oder nicht verifiziert werden können.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>4. Anbieter-Shop und Produktangebote</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Anbieter sind für ihre Shopdarstellung, Produktbilder, Produktbeschreibungen, Preise, Lieferinformationen, Steuerangaben, Lebensmittel-/Produkthinweise, Warnhinweise, Widerrufsangaben und sonstige Pflichtinformationen selbst verantwortlich.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Produktangaben müssen wahr, vollständig, verständlich und nicht irreführend sein. Bei Lebensmitteln, Kosmetik, Spielzeug, Textilien, Büchern, personalisierten Produkten und sonst regulierten Produktgruppen sind die jeweils geltenden Spezialpflichten einzuhalten.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Anbieter dürfen nur Produkte anbieten, zu deren Verkauf sie berechtigt sind und die sicher, verkehrsfähig und rechtmäßig sind. Verboten sind insbesondere rechtsverletzende, gefährliche, gefälschte, jugendgefährdende, diskriminierende oder sonst unzulässige Waren.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>5. Preise, Steuern und Rechnungen</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Anbieter legen Bruttopreise, Umsatzsteuerstatus, Versandkosten und sonstige Preisbestandteile korrekt fest. Sie sind selbst verantwortlich für steuerliche Einordnung, Rechnungsstellung, Buchhaltung, Umsatzsteuer, Einkommen-/Körperschaftsteuer und sonstige Abgaben.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Ist der Anbieter Kleinunternehmer, pauschalbesteuert, gemeinnützig, privat oder regulär umsatzsteuerpflichtig, muss dies zutreffend angegeben und in Rechnungen/Produktangaben berücksichtigt werden.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>6. Bestell- und Zahlungsabwicklung</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Bestellungen und Zahlungen werden über Wichtelwerken bzw. einen eingebundenen Zahlungsdienstleister abgewickelt. Anbieter akzeptieren, dass Käufer mit befreiender Wirkung an Wichtelwerken bzw. den Zahlungsdienstleister zahlen können.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Der konkrete Zahlungsdienstleister, Auszahlungsrhythmus, Gebühren, Rückbelastungen, Reserven, Identitätsprüfung und Zahlungsdienstleisterbedingungen sind vor Livegang zu ergänzen: <strong>[Payment Service Provider und Auszahlungsbedingungen ergänzen]</strong>.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Wichtelwerken kann fällige Provisionen, Gebühren, Rückerstattungen, Stornos, Chargebacks, Zahlungsdienstleisterkosten oder sonstige berechtigte Forderungen mit Anbietererlösen verrechnen.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>7. Provisionen und Entgelte</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Für Verkäufe über die Plattform kann Wichtelwerken eine Provision oder sonstige Entgelte erheben. Die konkrete Höhe, Bemessungsgrundlage, Fälligkeit und Abrechnung sind in der jeweils aktuellen Gebührenübersicht oder Anbietervereinbarung geregelt: <strong>[Gebührenmodell ergänzen]</strong>.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Alle Entgelte verstehen sich zuzüglich gesetzlicher Umsatzsteuer, soweit diese anfällt.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>8. Lieferung, Erfüllung und Kundenservice</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Anbieter sind für rechtzeitige Lieferung, ordnungsgemäße Verpackung, Versand, Sendungsinformationen, Kommunikation mit Käufern, Reklamationen, Gewährleistung und Rückabwicklung verantwortlich, soweit Wichtelwerken nicht ausdrücklich eigene Leistungen übernimmt.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Anbieter müssen Bestellungen zeitnah bearbeiten. Nicht verfügbare Produkte sind unverzüglich zu deaktivieren oder zu aktualisieren. Verzögerungen, Lieferprobleme oder Produktmängel sind dem Käufer und Wichtelwerken unverzüglich mitzuteilen.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>9. Verbraucherrecht, Widerruf und Pflichtinformationen</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Gewerbliche Anbieter müssen Verbrauchern vor Vertragsschluss alle gesetzlich erforderlichen Informationen bereitstellen, insbesondere Identität und Anschrift, wesentliche Produkteigenschaften, Gesamtpreis, Lieferkosten, Lieferbedingungen, Zahlungsbedingungen, Widerrufsbelehrung, Muster-Widerrufsformular, Gewährleistungsrechte und gegebenenfalls Garantien.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Anbieter tragen die Verantwortung dafür, ob ein Widerrufsrecht besteht oder eine gesetzliche Ausnahme greift. Fehlerhafte Widerrufsbelehrungen, fehlende Pflichtinformationen oder unzulässige Ausschlüsse gehen zulasten des Anbieters.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>10. Produktkonformität, Sicherheit und Rückrufe</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Anbieter gewährleisten, dass ihre Produkte den anwendbaren gesetzlichen Anforderungen entsprechen. Dazu können je nach Produktgruppe Kennzeichnungs-, Sicherheits-, Lebensmittel-, Verpackungs-, Produktsicherheits-, Urheber-, Marken-, Textil-, Spielzeug-, Elektro-, Batterie- oder Umweltvorschriften gehören.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Erhält ein Anbieter Kenntnis von Sicherheitsproblemen, Rechtsverletzungen, behördlichen Beanstandungen oder Rückrufen, informiert er Wichtelwerken unverzüglich und ergreift erforderliche Maßnahmen.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>11. Rechte an Inhalten</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Anbieter räumen Wichtelwerken für die Dauer der Veröffentlichung ein einfaches, räumlich unbeschränktes Recht ein, Produkttexte, Bilder, Logos, Shopnamen und sonstige Inhalte zur Darstellung, Bewerbung und technischen Bereitstellung der Plattform zu nutzen.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Anbieter sichern zu, dass sie über alle hierfür erforderlichen Rechte verfügen und keine Rechte Dritter verletzen.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>12. Moderation, Sperrung und Maßnahmen</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wichtelwerken kann Angebote prüfen, ablehnen, herabstufen, sperren, löschen oder Anbieterfunktionen einschränken, wenn konkrete Anhaltspunkte für Rechtsverstöße, Produktgefahren, Verbraucherbeschwerden, Zahlungsrisiken, fehlende Nachweise, irreführende Angaben oder Verstöße gegen diese Anbieter-AGB bestehen.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Soweit gesetzlich erforderlich, informiert Wichtelwerken den Anbieter über Gründe der Maßnahme und stellt Beschwerdemöglichkeiten bereit.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>13. Freistellung</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Anbieter stellen Wichtelwerken von Ansprüchen Dritter frei, die aus rechtswidrigen Angeboten, fehlerhaften Produktangaben, fehlenden Pflichtinformationen, Steuerverstößen, Schutzrechtsverletzungen, Produktmängeln, Produktgefahren, Datenschutzverstößen des Anbieters oder sonstigen vom Anbieter zu vertretenden Pflichtverletzungen entstehen. Die Freistellung umfasst angemessene Kosten der Rechtsverteidigung.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>14. Datenschutz und Auftragsverarbeitung</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wichtelwerken und Anbieter verarbeiten personenbezogene Daten im Rahmen ihrer jeweiligen Verantwortlichkeiten. Anbieter dürfen Käuferdaten nur zur Abwicklung der jeweiligen Bestellung, gesetzlichen Pflichten und zulässigen Kundenkommunikation nutzen. Werbung, Weitergabe oder Zweckänderung ist nur mit Rechtsgrundlage zulässig.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Sofern eine Auftragsverarbeitung oder gemeinsame Verantwortlichkeit erforderlich wird, schließen die Parteien vor Aufnahme der Verarbeitung eine gesonderte Vereinbarung.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>15. Laufzeit und Kündigung</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Der Anbieter-Vertrag läuft auf unbestimmte Zeit und kann von beiden Parteien mit einer Frist von <strong>[Frist ergänzen, z.B. 14 Tage]</strong> in Textform gekündigt werden. Das Recht zur außerordentlichen Kündigung aus wichtigem Grund bleibt unberührt.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Nach Vertragsende sind offene Bestellungen ordnungsgemäß abzuwickeln. Wichtelwerken kann Daten im gesetzlich zulässigen Umfang für Abwicklung, Nachweise und gesetzliche Aufbewahrungspflichten speichern.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>16. Haftung</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wichtelwerken haftet unbeschränkt bei Vorsatz, grober Fahrlässigkeit, Verletzung von Leben, Körper oder Gesundheit, nach dem Produkthaftungsgesetz sowie bei übernommenen Garantien. Im Übrigen haftet Wichtelwerken bei leichter Fahrlässigkeit nur bei Verletzung wesentlicher Vertragspflichten und begrenzt auf den typischerweise vorhersehbaren Schaden.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>17. Änderungen</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wichtelwerken kann diese Anbieter-AGB ändern, wenn sachliche Gründe bestehen, insbesondere Änderungen der Rechtslage, Zahlungsdienstleisterbedingungen, Plattformfunktionen oder Sicherheitsanforderungen. Anbieter werden rechtzeitig informiert. Widerspricht ein Anbieter, kann Wichtelwerken den Anbieter-Vertrag ordentlich kündigen.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>18. Schlussbestimmungen</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Es gilt deutsches Recht unter Ausschluss des UN-Kaufrechts. Gerichtsstand ist, soweit zulässig, der Sitz von Wichtelwerken. Zwingende Verbraucherrechte bleiben unberührt.</p><!-- /wp:paragraph -->
HTML;

$privacy = <<<HTML
<!-- wp:heading --><h2>Datenschutzerklärung</h2><!-- /wp:heading -->
$notice
<!-- wp:paragraph --><p>Stand: {$updated}</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>1. Verantwortlicher</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Verantwortlich für die Datenverarbeitung auf dieser Website ist die Wichtelwerken UG (haftungsbeschränkt) i.G., <strong>[vollständige Anschrift ergänzen]</strong>, vertreten durch <strong>[Vertretungsberechtigte ergänzen]</strong>, E-Mail: <strong>[Datenschutzkontakt ergänzen]</strong>.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Für bestimmte Verarbeitungsvorgänge im Rahmen einzelner Verkäufe kann zusätzlich der jeweilige Anbieter eigener Verantwortlicher sein, insbesondere für Produktabwicklung, Versand, Reklamationen, gesetzliche Aufbewahrungspflichten und eigene Kundenkommunikation.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>2. Hosting und technische Bereitstellung</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Beim Aufruf der Website werden technisch erforderliche Daten verarbeitet, insbesondere IP-Adresse, Datum und Uhrzeit, aufgerufene Seiten, Browser-/Geräteinformationen, Referrer, Server-Logdaten und Sicherheitsereignisse. Zweck ist die Bereitstellung, Stabilität, Sicherheit und Fehleranalyse der Website. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Der konkrete Hostinganbieter ist vor Livegang zu ergänzen: <strong>[Hostinganbieter und Serverstandort ergänzen]</strong>.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>3. Kundenkonto, Anbieterregistrierung und Nutzerverwaltung</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Bei Registrierung und Nutzung eines Kontos verarbeiten wir Stammdaten, Kontaktinformationen, Zugangsdaten, Rollen, Shopdaten, Bestellhistorie, Kommunikationsdaten und technische Protokolldaten. Zweck ist die Bereitstellung des Kontos, Authentifizierung, Marktplatznutzung, Missbrauchsprävention und Vertragsdurchführung. Rechtsgrundlagen sind Art. 6 Abs. 1 lit. b, lit. c und lit. f DSGVO.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Bei Anbietern verarbeiten wir zusätzlich Angaben zur Identität, Anschrift, Unternehmereigenschaft, steuerlichen Einordnung, Bank-/Auszahlungsdaten, Nachweise und Verifikationsdaten, soweit dies für Marktplatzbetrieb, Zahlungsabwicklung, DSA-/KYBC-Pflichten, Sicherheit und Abrechnung erforderlich ist.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>4. Bestellungen, Zahlung und Versand</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Bei Bestellungen verarbeiten wir insbesondere Name, Rechnungs- und Lieferadresse, E-Mail-Adresse, Telefonnummer, Warenkorb, Produktdaten, Preise, Steuerdaten, Zahlungsstatus, Bestellnotizen, Versandstatus und Kommunikationsdaten. Zweck ist die Anbahnung und Durchführung von Kaufverträgen, Zahlungs- und Bestellabwicklung, Kundenservice, Gewährleistung, Rückabwicklung und gesetzliche Dokumentation. Rechtsgrundlagen sind Art. 6 Abs. 1 lit. b und lit. c DSGVO.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Zahlungen sollen über den Marktplatz bzw. einen Zahlungsdienstleister abgewickelt werden. Dabei können Zahlungsdaten, Transaktionsdaten, Risikoprüfungsdaten und Auszahlungsdaten an den Zahlungsdienstleister übermittelt werden. Der konkrete Zahlungsdienstleister und dessen Datenschutzhinweise sind vor Livegang zu ergänzen: <strong>[Payment Service Provider ergänzen]</strong>.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Für Versand und Lieferung geben wir erforderliche Bestell- und Kontaktdaten an den jeweiligen Anbieter und, soweit erforderlich, Versanddienstleister weiter.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>5. Anbieter-Shops und öffentliche Inhalte</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Anbieterprofile, Shopnamen, Produktbilder, Produkttexte, Preise, öffentliche Anbieterinformationen und Bewertungen können öffentlich sichtbar sein. Zweck ist die Darstellung des Marktplatzes und der Produktangebote. Rechtsgrundlagen sind Art. 6 Abs. 1 lit. b und lit. f DSGVO.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>6. Kommunikation</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wenn Sie uns kontaktieren oder über die Plattform mit Anbietern kommunizieren, verarbeiten wir Kommunikationsinhalte, Kontaktdaten, Zeitpunkte und zugehörige Bestell-/Kontodaten. Zweck ist die Bearbeitung von Anfragen, Support, Reklamationen, Missbrauchsprävention und Nachweisführung. Rechtsgrundlagen sind Art. 6 Abs. 1 lit. b und lit. f DSGVO.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>7. Cookies und Consent-Management</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wir nutzen technisch notwendige Cookies und vergleichbare Technologien, insbesondere für Warenkorb, Anmeldung, Checkout, Sicherheit, Spracheinstellungen und Consent-Management. Rechtsgrundlagen sind Art. 6 Abs. 1 lit. b, lit. c und lit. f DSGVO sowie § 25 Abs. 2 TDDDG.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Nicht notwendige Cookies, Analyse-, Präferenz- oder Marketingdienste werden nur nach Einwilligung eingesetzt. Rechtsgrundlage ist Art. 6 Abs. 1 lit. a DSGVO sowie § 25 Abs. 1 TDDDG. Einwilligungen können jederzeit widerrufen werden.</p><!-- /wp:paragraph -->
<!-- wp:shortcode -->[cmplz-manage-consent text="Cookie-Einstellungen ändern"]<!-- /wp:shortcode -->
<!-- wp:paragraph --><p>Weitere Informationen finden Sie in der <a href="/cookie-richtlinie/">Cookie-Richtlinie</a>.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>8. Eingesetzte Systeme und Plugins</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>WordPress zur Verwaltung der Website.</li><li>WooCommerce zur Shop-, Warenkorb-, Checkout- und Bestellabwicklung.</li><li>Dokan zur Anbieter-/Marktplatzfunktion.</li><li>Complianz zur Cookie- und Consent-Verwaltung.</li></ul><!-- /wp:list -->
<!-- wp:paragraph --><p>Externe Google Fonts sind im Theme deaktiviert. Falls künftig Analyse-, Marketing-, Karten-, Video-, Newsletter-, Zahlungs- oder Versanddienste eingebunden werden, muss diese Datenschutzerklärung entsprechend aktualisiert werden.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>9. Empfänger personenbezogener Daten</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Empfänger können, soweit erforderlich, sein: Anbieter, Zahlungsdienstleister, Versanddienstleister, Hostinganbieter, technische Dienstleister, Steuerberater, Rechtsberater, Behörden, Gerichte und sonstige Stellen, soweit eine rechtliche Pflicht oder berechtigtes Interesse besteht.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>10. Speicherdauer</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wir speichern personenbezogene Daten nur so lange, wie dies für die genannten Zwecke erforderlich ist oder gesetzliche Aufbewahrungspflichten bestehen. Handels- und steuerrechtlich relevante Unterlagen können regelmäßig bis zu sechs bzw. zehn Jahre aufzubewahren sein. Konto- und Kommunikationsdaten werden gelöscht oder anonymisiert, wenn sie nicht mehr erforderlich sind und keine Aufbewahrungspflichten entgegenstehen.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>11. Drittlandübermittlungen</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Eine Übermittlung in Staaten außerhalb der EU/des EWR erfolgt nur, soweit hierfür eine Rechtsgrundlage besteht, insbesondere ein Angemessenheitsbeschluss, geeignete Garantien wie EU-Standardvertragsklauseln oder eine ausdrückliche Einwilligung. Konkrete Drittlanddienste sind vor Livegang zu ergänzen.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>12. Betroffenenrechte</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Betroffene Personen haben nach Maßgabe der DSGVO Rechte auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung, Datenübertragbarkeit, Widerspruch gegen Verarbeitungen auf Grundlage berechtigter Interessen sowie Widerruf erteilter Einwilligungen mit Wirkung für die Zukunft.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Zudem besteht ein Beschwerderecht bei einer Datenschutzaufsichtsbehörde, insbesondere in dem Mitgliedstaat des gewöhnlichen Aufenthalts, Arbeitsplatzes oder Orts des mutmaßlichen Verstoßes.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>13. Pflicht zur Bereitstellung</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Bestimmte Daten sind für Konto, Bestellung, Zahlung, Versand oder Anbieterfreischaltung erforderlich. Ohne diese Daten können wir die jeweilige Funktion möglicherweise nicht bereitstellen.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>14. Automatisierte Entscheidungen</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Eine ausschließlich automatisierte Entscheidungsfindung im Sinne von Art. 22 DSGVO findet nach aktuellem Stand nicht statt. Zahlungsdienstleister können eigene Risiko- und Betrugsprüfungen durchführen; Details sind nach Auswahl des Zahlungsdienstleisters zu ergänzen.</p><!-- /wp:paragraph -->
HTML;

$impressum = <<<HTML
<!-- wp:heading --><h2>Impressum</h2><!-- /wp:heading -->
$notice
<!-- wp:paragraph --><p>Angaben nach § 5 Digitale-Dienste-Gesetz (DDG)</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Wichtelwerken UG (haftungsbeschränkt) i.G.<br><strong>[Straße und Hausnummer ergänzen]</strong><br><strong>[PLZ und Ort ergänzen]</strong><br>Deutschland</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Vertreten durch: <strong>[Vertretungsberechtigte ergänzen]</strong><br>E-Mail: <strong>[E-Mail ergänzen]</strong><br>Telefon: <strong>[Telefon ergänzen]</strong></p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Registergericht: in Gründung / <strong>[nach Eintragung ergänzen]</strong><br>Registernummer: in Gründung / <strong>[nach Eintragung ergänzen]</strong><br>Umsatzsteuer-ID: <strong>[falls vorhanden ergänzen]</strong></p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Verantwortlich für Inhalte</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Verantwortlich nach § 18 Abs. 2 MStV: <strong>[Name und Anschrift ergänzen]</strong>.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Online-Streitbeilegung und Verbraucherstreitbeilegung</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung bereit: <a href="https://ec.europa.eu/consumers/odr/" rel="nofollow noopener" target="_blank">https://ec.europa.eu/consumers/odr/</a>.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Angaben zur Bereitschaft oder Verpflichtung zur Teilnahme an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle bitte ergänzen.</p><!-- /wp:paragraph -->
HTML;

$widerruf = <<<HTML
<!-- wp:heading --><h2>Widerruf und Rückgabe</h2><!-- /wp:heading -->
$notice
<!-- wp:paragraph --><p>Stand: {$updated}</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>1. Grundsatz</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wenn Sie als Verbraucher über Wichtelwerken bei einem gewerblichen Anbieter bestellen, steht Ihnen grundsätzlich ein gesetzliches Widerrufsrecht zu. Der Kaufvertrag kommt in der Regel mit dem jeweiligen Anbieter zustande. Dieser ist für die konkrete Widerrufsbelehrung verantwortlich.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>2. Widerrufsfrist</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Die Widerrufsfrist beträgt grundsätzlich 14 Tage ab dem Tag, an dem Sie oder ein von Ihnen benannter Dritter, der nicht Beförderer ist, die Ware erhalten haben. Bei Teillieferungen gelten die gesetzlichen Sonderregeln.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>3. Ausübung des Widerrufs</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Um Ihr Widerrufsrecht auszuüben, müssen Sie den jeweiligen Anbieter mittels eindeutiger Erklärung über Ihren Entschluss informieren, den Vertrag zu widerrufen. Nutzen Sie hierfür die in der Bestellung, im Anbieterprofil oder in der Widerrufsbelehrung angegebenen Kontaktdaten. Wichtelwerken kann die Weiterleitung unterstützen.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>4. Folgen des Widerrufs</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Im Falle eines wirksamen Widerrufs sind empfangene Leistungen nach den gesetzlichen Vorschriften zurückzugewähren. Rückzahlungen erfolgen grundsätzlich über das ursprünglich eingesetzte Zahlungsmittel, sofern nichts anderes vereinbart wurde.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>5. Rücksendung</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Die Rücksendeadresse kann je nach Anbieter abweichen. Bitte senden Sie Waren nicht ohne Prüfung der jeweiligen Anbieterangaben zurück. Die Kosten der Rücksendung trägt der Käufer nur, soweit dies rechtlich zulässig und ordnungsgemäß mitgeteilt wurde.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>6. Ausnahmen vom Widerrufsrecht</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Das Widerrufsrecht kann gesetzlich ausgeschlossen sein, insbesondere bei Waren, die nach Kundenspezifikation angefertigt werden, eindeutig auf persönliche Bedürfnisse zugeschnitten sind, schnell verderben können oder aus Gründen des Gesundheitsschutzes/Hygiene nicht zur Rückgabe geeignet sind, wenn ihre Versiegelung entfernt wurde. Anbieter müssen solche Ausnahmen produktbezogen angeben.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>7. Reklamationen und Gewährleistung</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Gesetzliche Mängelrechte bestehen unabhängig vom Widerrufsrecht. Bitte melden Sie Mängel möglichst zeitnah an den Anbieter oder über die von Wichtelwerken bereitgestellten Kontaktwege.</p><!-- /wp:paragraph -->
HTML;

$agbNutzerId = ww_legal_upsert_page('agb-nutzer', 'AGB für Käufer und Nutzer', $agbNutzer);
$agbAnbieterId = ww_legal_upsert_page('agb-anbieter', 'AGB für Anbieter', $agbAnbieter);
$privacyId = ww_legal_upsert_page('datenschutzerklaerung', 'Datenschutzerklärung', $privacy);
$impressumId = ww_legal_upsert_page('impressum', 'Impressum', $impressum);
$widerrufId = ww_legal_upsert_page('widerruf-rueckgabe', 'Widerruf und Rückgabe', $widerruf);

update_option('wp_page_for_privacy_policy', $privacyId);
update_option('woocommerce_terms_page_id', $agbNutzerId);

WP_CLI::success("Rechtstexte aktualisiert: AGB Nutzer #{$agbNutzerId}, AGB Anbieter #{$agbAnbieterId}, Datenschutz #{$privacyId}, Impressum #{$impressumId}, Widerruf #{$widerrufId}");
