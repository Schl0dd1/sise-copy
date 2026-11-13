---
title: "Startseite"
description: "Kollektive Auseinandersetzung mit sexualisierter Gewalt"
date: "2026-02-24T21:43:22+01:00"
draft: false
# Seitentypen (bestimmen das Layout). Hier statt in den einzelnen Dateien,
# weil diese Datei nicht im CMS bearbeitet wird.
cascade:
  # Übersichtsseiten der Hauptmenü-Bereiche → layouts/bereich/
  - target:
      path: "{/ueber-uns,/veranstaltungen,/was-wir-machen,/beitraege,/links}"
      kind: section
    type: bereich
  # Podcast mit Folgen → layouts/podcast/
  - target:
      path: "/was-wir-machen/podcast{,/**}"
    type: podcast
  # Galerie mit Alben → layouts/gallery/
  - target:
      path: "/beitraege/galerie{,/**}"
    type: gallery
  # Veranstaltungen → layouts/veranstaltung/
  - target:
      path: "/veranstaltungen/**"
      kind: page
    type: veranstaltung
---

sichtbar & selbstbestimmt ist eine selbstorganisierte Gruppe von Betroffenen
sexualisierter Gewalt. Wir haben uns im Herbst 2019 in Leipzig gegründet.

Wir verstehen uns als politische Gruppe, die sich kollektiv mit den eigenen
Betroffenheitserfahrungen auseinandersetzt und dabei gesellschaftliche Strukturen
in den Blick nimmt, die sexualisierte Gewalt ermöglichen und aufrechterhalten.
