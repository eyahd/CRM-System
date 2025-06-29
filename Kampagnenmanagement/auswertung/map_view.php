<!DOCTYPE html> 
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Umsatz nach Stadt</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <style>
    body {
      font-family: sans-serif;
      margin: 0;
    }
    #map {
      height: 90vh;
      width: 100%;
    }
    .legend {
      background: white;
      padding: 10px;
      line-height: 1.4em;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
      position: absolute;
      bottom: 20px;
      left: 20px;
      z-index: 1000;
      font-size: 14px;
    }
    .legend .box {
      display: inline-block;
      width: 20px;
      height: 10px;
      margin-right: 5px;
      vertical-align: middle;
    }
  </style>
</head>
<body>
  <h1>🗺 Umsatz nach Stadt (Bubble Map)</h1>
  <div id="map"></div>
  <div class="legend">

<b>Farbskala (Umsatz in €)</b><br>
<div><span class="box" style="background:#800026"></span> > 200.000</div>
<div><span class="box" style="background:#E31A1C"></span> > 120.000</div>
<div><span class="box" style="background:#FD8D3C"></span> > 100.000</div>
<div><span class="box" style="background:#FED976"></span> > 90.000</div>
<div><span class="box" style="background:#FFE066"></span> > 70.000</div>
<div><span class="box" style="background:#ffffcc"></span> ≤ 70.000</div>

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
    const daten = [
      {"stadt":"Berlin","gesamt_umsatz":857864.34},
      {"stadt":"Hamburg","gesamt_umsatz":344980.72},
      {"stadt":"München","gesamt_umsatz":276077.37},
      {"stadt":"Kiel","gesamt_umsatz":157156.22},
      {"stadt":"Düsseldorf","gesamt_umsatz":143028.62},
      {"stadt":"Nürnberg","gesamt_umsatz":134490.45},
      {"stadt":"Dortmund","gesamt_umsatz":133300.61},
      {"stadt":"Köln","gesamt_umsatz":132677.73},
      {"stadt":"Hannover","gesamt_umsatz":128446.95},
      {"stadt":"Leipzig","gesamt_umsatz":124036.02},
      {"stadt":"Braunschweig","gesamt_umsatz":105348.63},
      {"stadt":"Chemnitz","gesamt_umsatz":103786.65},
      {"stadt":"Reutlingen","gesamt_umsatz":93636.63},
      {"stadt":"Bochum","gesamt_umsatz":89010.85},
      {"stadt":"Frankfurt am Main","gesamt_umsatz":88861.70},
      {"stadt":"Duisburg","gesamt_umsatz":86075.47},
      {"stadt":"Dresden","gesamt_umsatz":84283.07},
      {"stadt":"Witzenhausen","gesamt_umsatz":81765.98},
      {"stadt":"Wuppertal","gesamt_umsatz":80164.92},
      {"stadt":"Essen","gesamt_umsatz":79048.69}
      // Du kannst alle weiteren Städte hier einfügen
    ];

    const stadtKoordinaten = {
      "Berlin": [52.52, 13.405],
      "Hamburg": [53.551, 9.993],
      "München": [48.135, 11.582],
      "Köln": [50.9375, 6.9603],
      "Frankfurt am Main": [50.1109, 8.6821],
      "Bochum": [51.4818, 7.2162],
      "Leipzig": [51.3397, 12.3731],
      "Düsseldorf": [51.2277, 6.7735],
      "Hannover": [52.3759, 9.732],
      "Nürnberg": [49.4521, 11.0767],
      "Dortmund": [51.5136, 7.4653],
      "Kiel": [54.3233, 10.1228],
      "Braunschweig": [52.2689, 10.5268],
      "Chemnitz": [50.8278, 12.9214],
      "Reutlingen": [48.4914, 9.2043],
      "Duisburg": [51.4344, 6.7623],
      "Dresden": [51.0504, 13.7373],
      "Witzenhausen": [51.3422, 9.8579],
      "Wuppertal": [51.2562, 7.1508],
      "Essen": [51.4556, 7.0116]
      // weitere Koordinaten hinzufügen bei Bedarf
    };

function getColor(umsatz) {
  return umsatz > 200000 ? '#800026':  // sehr dunkles Rot
         umsatz > 120000 ? '#E31A1C' :
         umsatz > 100000 ? '#FD8D3C' :
         umsatz > 90000 ?  '#FED976' :
         umsatz > 70000  ? '#FFE066' :  // dunkleres Gelb
                           '#ffffcc';   // helles Gelb (unterste Klasse)
}



    async function initMap() {
      const map = L.map('map').setView([51.2, 10.5], 6);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
      }).addTo(map);

      daten.forEach(stadt => {
        const coords = stadtKoordinaten[stadt.stadt];
        if (!coords) return;

        // Radius stärker anpassen für bessere Sichtbarkeit
        const radius = Math.sqrt(stadt.gesamt_umsatz) * 6;
        const color = getColor(stadt.gesamt_umsatz);

        const popup = `
          <b>${stadt.stadt}</b><br>
          Umsatz: ${stadt.gesamt_umsatz.toLocaleString('de-DE')} €
        `;

        L.circle(coords, {
          radius: radius * 10,
          color: color,
          fillColor: color,
          fillOpacity: 0.6
        }).bindPopup(popup).addTo(map);
      });
    }

    initMap();
  </script>
</body>
</html>
