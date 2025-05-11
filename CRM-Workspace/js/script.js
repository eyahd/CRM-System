// Zeigt oder versteckt Firmenfelder basierend auf dem Kundentyp
function toggleFirmenfelder() {
  const typ = document.getElementById("typ")?.value;
  const firmenfelder = document.getElementById("firmenfelder");
  const firmenname = document.getElementById("firmenname");
  const ust_id = document.getElementById("ust_id");

  if (!typ || !firmenfelder || !firmenname || !ust_id) return;

  const anzeigen = typ === "geschaeft";
  firmenfelder.style.display = anzeigen ? "block" : "none";
  firmenname.required = anzeigen;
  ust_id.required = anzeigen;
}

function toggleKundenfelder(rolle) {
  const kundenbereiche = document.querySelectorAll(".kunde-feld");
  const anzeigen = rolle === "kunde";

  kundenbereiche.forEach((bereich) => {
    bereich.style.display = anzeigen ? "" : "none";
    // Alle Formularelemente innerhalb deaktivieren/aktivieren
    bereich.querySelectorAll("input, select, textarea").forEach((el) => {
      el.disabled = !anzeigen;
    });
  });
}

// Zeigt oder versteckt den Kundentyp-Container (nur für Kunden relevant)
function toggleKundentyp(rolle) {
  const container = document.getElementById("kundentyp-container");
  if (container) {
    container.style.display = rolle === "kunde" ? "block" : "none";
  }
}

// Validiert die IBAN beim Verlassen des Eingabefeldes
function initIbanValidation() {
  const ibanInput = document.getElementById("iban");
  const fehlerText = document.getElementById("iban-fehler");

  if (ibanInput && fehlerText) {
    ibanInput.addEventListener("blur", () => {
      const isValid = IBAN.isValid(ibanInput.value.trim());
      ibanInput.classList.toggle("invalid", !isValid);
      fehlerText.textContent = isValid
        ? ""
        : "Bitte geben Sie eine gültige IBAN ein.";
    });
  }
}

// Prüft die Passwortfelder auf Übereinstimmung und Mindestlänge
function checkPasswortGleichheit(event) {
  const pw1 = document.getElementById("passwort")?.value || "";
  const pw2 = document.getElementById("passwort2")?.value || "";

  if (pw1.length < 8) {
    alert("Das Passwort muss mindestens 8 Zeichen lang sein.");
    event.preventDefault();
    return false;
  }

  if (pw1 !== pw2) {
    alert("Die Passwörter stimmen nicht überein.");
    event.preventDefault();
    return false;
  }

  return true;
}

window.addEventListener("DOMContentLoaded", () => {
  toggleFirmenfelder();

  const rolleElement = document.getElementById("rolle");
  if (rolleElement) {
    const rolle = rolleElement.value;
    toggleKundentyp(rolle);
    toggleKundenfelder(rolle); // ✅ Korrekte Übergabe des Rollenwerts

    rolleElement.addEventListener("change", (e) => {
      const neueRolle = e.target.value;
      toggleKundentyp(neueRolle);
      toggleKundenfelder(neueRolle); // ✅ beim Wechsel auch neu setzen
    });
  }

  initIbanValidation();

  const form = document.querySelector("form");
  const pw1 = document.getElementById("passwort");
  const pw2 = document.getElementById("passwort2");

  if (form && pw1 && pw2) {
    form.addEventListener("submit", checkPasswortGleichheit);
  }

  const typElement = document.getElementById("typ");
  if (typElement) {
    typElement.addEventListener("change", toggleFirmenfelder);
  }
});
