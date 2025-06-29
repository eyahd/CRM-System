<?php 

$conn = new mysqli("localhost", "root", "", "crm_autovermietung");
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}


$segments = [];
$segment_result = $conn->query("SELECT DISTINCT segment FROM kategorie");
if ($segment_result) {
    while ($row = $segment_result->fetch_assoc()) {
        $segments[] = $row['segment'];
    }
}


$errors = [];
$success = false;


$betreff = $inhalt = $segment = "";
$kampagnenArt = $kampagnenTyp = "";
$startdatum = $enddatum = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $segment = $_POST['segment'] ?? '';
    $kampagnenArt = $_POST['kampagnenArt'] ?? '';
    $kampagnenTyp = $_POST['kampagnen_typ'] ?? '';
    $startdatum = $_POST['startdatum'] ?? '';
    $enddatum = $_POST['enddatum'] ?? '';

 
    
    if (empty($segment)) {
        $errors[] = "Bitte wählen Sie eine Zielgruppe.";
    }
    if (empty($kampagnenArt)) {
        $errors[] = "Bitte wählen Sie eine Kampagnenart.";
    }
    if (empty($kampagnenTyp)) {
        $errors[] = "Bitte wählen Sie einen Kampagnentyp.";
    }
    if (empty($startdatum)) {
        $errors[] = "Bitte geben Sie ein Startdatum ein.";
    }
    if (empty($enddatum)) {
        $errors[] = "Bitte geben Sie ein Enddatum ein.";
    }
    // Optional: validate that enddatum >= startdatum

    if (empty($errors)) {
        // Hier kannst du die Daten z.B. speichern
        // Oder an die nächste Seite weiterleiten mit allen Daten
        $params = http_build_query([
            'segment' => $segment,
            'kampagnenArt' => $kampagnenArt,
            'kampagnenTyp' => $kampagnenTyp,
            'startdatum' => $startdatum,
            'enddatum' => $enddatum
        ]);
        header("Location: sending_visualization.php?$params");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>Kampagnenplanung</title>
    <style>
body {
  font-family: 'Poppins', sans-serif;
  background: 
    linear-gradient(rgba(245, 247, 250, 0.85), rgba(226, 232, 236, 0.85)),
    url('https://images.unsplash.com/photo-1552519507-da3b142c6e3d'); /* image of cars/highway */
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  color: #333;
  margin: 0;
  padding: 0;
}

.container {
  max-width: 850px;
  margin: 60px auto;
  background-color: rgba(255, 255, 255, 0.95); /* slight transparency */
  padding: 40px;
  border-radius: 20px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}


h1 {
  text-align: center;
  color: #1e3a8a;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 2.5rem;
}

label {
  display: block;
  margin-top: 20px;
  font-weight: 500;
  color: #2c3e50;
}

input[type="text"],
input[type="date"],
textarea,
select {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 10px;
  margin-top: 8px;
  font-size: 1rem;
  background-color: #fefefe;
}

input:focus,
select:focus,
textarea:focus {
  border: 2px solid #1e3a8a;
  outline: none;
}

.button {
  background-color:#1e3a8a ;

  color:rgb(251, 253, 255);
  padding: 14px 22px;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  margin-top: 30px;
  cursor: pointer;
  font-weight: 600;
  transition: background-color 0.3s ease, transform 0.2s;
}

.button:hover {
  background-color: #1e3a8a;
  transform: scale(1.05);
}

.error {
  color: #e74c3c;
  font-weight: 500;
  margin-bottom: 1rem;
}

    </style>
</head>
<body>

<div class="container">
    <h1>Kampagne erstellen</h1>

    <?php if (!empty($errors)): ?>
        <div class="error" style="color:red; margin-bottom:20px;">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
       
     <label for="language">Sprache der Nachricht</label>
      <select id="language" name="language" onchange="handleKampagnentypChange()" aria-label="Sprache">
        <option value="de" selected>Deutsch</option>
        <option value="en">Englisch</option>
      </select>


  <label for="segment">Zielgruppe</label>
  <select id="segment" name="segment">
    <option value="">-- Bitte wählen --</option>
    <?php foreach ($segments as $seg): ?>
      <option value="<?= htmlspecialchars($seg) ?>" <?= $segment === $seg ? 'selected' : '' ?>>
        <?= htmlspecialchars($seg) ?>
      </option>
    <?php endforeach; ?>
  </select>


        <label for="kampagnenArt">Kampagnenart</label>
        <select id="kampagnenArt" name="kampagnenArt" required>
            <option value="">Bitte wählen</option>
        <option value="ereignis">📅 Ereigniskampagne</option>
        <option value="zeit">⏰ Zeitkampagne</option>
        <option value="benutzerdefiniert">⚙️ Benutzerdefiniert</option>
        </select>

        <label for="kampagnen_typ">Kampagnen-Typ</label>
        <select id="kampagnen_typ" name="kampagnen_typ" required <?= $kampagnenArt === "" ? 'disabled' : '' ?>>
            <option value="">-- Bitte erst Kampagnenart wählen --</option>
        </select>

        <label for="startdatum">Startdatum</label>
        <input type="date" id="startdatum" name="startdatum" value="<?= htmlspecialchars($startdatum) ?>" required>

        <label for="enddatum">Enddatum</label>
        <input type="date" id="enddatum" name="enddatum" value="<?= htmlspecialchars($enddatum) ?>" required>


        

        <label for="rabattCode">Rabattcode</label>
        <input type="text" id="rabattCode" name="rabattCode" readonly>

        <label for="betreff">Betreff</label>
        <input type="text" id="betreff" name="betreff" readonly>

       
        <label for="nachricht"> Nachricht</label>
        <textarea id="nachricht" name="nachricht" rows="6" readonly></textarea>

        <button class="button" type="submit">Kampagne erstellen</button>
    </form>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // DOM 
    const kampagnenArt = document.getElementById("kampagnenArt");
    const kampagnenTyp = document.getElementById("kampagnen_typ");
    const segmentSelect = document.getElementById("segment");
    const languageSelect = document.getElementById("language");
    const startdatumInput = document.getElementById("startdatum");
    const enddatumInput = document.getElementById("enddatum");
    const rabattCodeInput = document.getElementById("rabattCode");
    const nachrichtTextarea = document.getElementById("nachricht");
    const betreffInput = document.getElementById("betreff");

    // Options for campaign types
    const kampagnenTypOptions = {
        "ereignis": [
            { value: "geburtstag", text: "🎂 Geburtstag" },
            { value: "produkteinfuehrung", text: "📈 Produkteinführung" },
            { value: "jubilaeum", text: "🌟 Jubiläum" },
            { value: "neueroeffnung", text: "🏢 Neueröffnung" },
            { value: "sponsoring", text: "🏆 Sponsoring" },
            { value: "wettbewerb", text: "🏅 Wettbewerb" },
            { value: "kooperation", text: "👥 Kooperation" }
        ],
        "zeit": [
            { value: "weihnachten", text: "🎄 Weihnachten" },
            { value: "ostern", text: "🐣 Ostern" },
            { value: "valentinstag", text: "💕 Valentinstag" },
            { value: "black_friday", text: "🛍️ Black Friday" },
            { value: "neujahr", text: "🎆 Neujahr" },
            { value: "back_to_school", text: "🏫 Back to School / Uni" },
            { value: "saisonal", text: "🌌 Saisonal" }
        ],
        "benutzerdefiniert": [
            { value: "custom", text: "⚙️ Benutzerdefiniert" }
        ]
    };

    function updateKampagnenTypOptions() {
        const art = kampagnenArt.value;
        kampagnenTyp.innerHTML = '<option value="">-- Bitte erst Kampagnenart wählen --</option>';

        if (kampagnenTypOptions[art]) {
            kampagnenTyp.disabled = false;
            kampagnenTypOptions[art].forEach(opt => {
                const option = document.createElement("option");
                option.value = opt.value;
                option.textContent = opt.text;
                kampagnenTyp.appendChild(option);
            });
        } else {
            kampagnenTyp.disabled = true;
        }
    }

    function generateRabattCode() {
        const segment = segmentSelect.value.toUpperCase().slice(0, 3) || "GEN";
        const random = Math.floor(1000 + Math.random() * 9000);
        rabattCodeInput.value = `${segment}${random}`;
    }

    function generateNachricht() {
        const segment = segmentSelect.value;
        const typ = kampagnenTyp.value;
        const lang = languageSelect.value;
        const start = startdatumInput.value;
        const end = enddatumInput.value;
        const code = rabattCodeInput.value;

        if (!segment || !typ || !start || !end) {
            nachrichtTextarea.value = "";
            betreffInput.value = "";
            return;
        }

        // Text ohne Emoji für den Betreff
        const kampagnenText = kampagnenTyp.options[kampagnenTyp.selectedIndex].text.replace(/^[^a-zA-ZäöüÄÖÜß]*\s*/, '');

        // Generate subject line
        const betreffText = `${kampagnenText} für ${segment} (${start} – ${end})`;
        betreffInput.value = betreffText;

        // Message body
        const dateText = (lang === "de") ? `vom ${start} bis zum ${end}` : `from ${start} to ${end}`;
        let message = "";

        if (lang === "de") {
            message = `Liebe/r ${segment}-Kunde,\n\nWir freuen uns, Ihnen unsere ${typ}-Kampagne ${dateText} vorzustellen. Verwenden Sie den Rabattcode ${code}, um exklusive Vorteile zu erhalten!\n\nIhr Autovermietungsteam`;
        } else {
            message = `Dear ${segment} customer,\n\nWe are pleased to present our ${typ} campaign ${dateText}. Use the discount code ${code} to enjoy exclusive benefits!\n\nYour Car Rental Team`;
        }

        nachrichtTextarea.value = message;
    }

    function generateBetreffTemplate() {
        const typ = kampagnenTyp.value;
        const lang = languageSelect.value;

        const subjects = {
            de: {
                geburtstag: "Feiern Sie mit uns! 🎉 Exklusives Angebot zum Geburtstag",
                produkteinfuehrung: "Neuheit entdecken: Unsere neueste Produkteinführung!",
                weihnachten: "🎄 Frohe Weihnachten! Jetzt mit Winterrabatt",
                black_friday: "Black Friday Deal – nur für kurze Zeit!",
                valentinstag: "💕 Exklusives Valentinstags-Angebot für Sie",
                custom: "Individuelles Angebot für unsere Kunden"
            },
            en: {
                geburtstag: "Celebrate with us! 🎉 Special Birthday Offer",
                produkteinfuehrung: "Discover our new product launch!",
                weihnachten: "🎄 Merry Christmas! Winter discount inside",
                black_friday: "Black Friday Deal – limited time only!",
                valentinstag: "💕 Special Valentine's Day offer just for you",
                custom: "Custom offer for our valued customers"
            }
        };

        betreffInput.value = subjects[lang][typ] || "Neue Kampagne gestartet";
    }

    // Event listeners
    kampagnenArt.addEventListener("change", () => {
        updateKampagnenTypOptions();
        generateRabattCode();
        generateNachricht();
    });

    kampagnenTyp.addEventListener("change", () => {
        generateRabattCode();
        generateNachricht();
        generateBetreffTemplate();
    });

    [segmentSelect, startdatumInput, enddatumInput, languageSelect].forEach(el =>
        el.addEventListener("change", () => {
            generateNachricht();
            generateBetreffTemplate();
        })
    );

   
    const logoutLink = document.createElement("a");
    logoutLink.href = "logout.php";
    logoutLink.textContent = "Logout";
    Object.assign(logoutLink.style, {
        position: "fixed",
        top: "20px",
        right: "20px",
        backgroundColor: "#e74c3c",
        color: "white",
        padding: "10px 15px",
        borderRadius: "8px",
        textDecoration: "none",
        fontWeight: "bold",
        zIndex: "1000"
    });
    logoutLink.onmouseover = () => logoutLink.style.backgroundColor = "#c0392b";
    logoutLink.onmouseout = () => logoutLink.style.backgroundColor = "#e74c3c";
    document.body.appendChild(logoutLink);
}
if (!empty($startdatum) && !empty($enddatum)) {
    if ($enddatum < $startdatum) {
        $errors[] = "Das Enddatum darf nicht vor dem Startdatum liegen.";
    }
});

</script>


</body>
</html>
