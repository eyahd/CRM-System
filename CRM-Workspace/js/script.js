// Anzeigen oder Verstecken der Firmenfelder je nach Typauswahl
function toggleFirmenfelder() {
  const typ = document.getElementById("typ")?.value;
  const firmenfelder = document.getElementById("firmenfelder");
  const firmenname = document.getElementById("firmenname");
  const ust_id = document.getElementById("ust_id");

  if (!typ || !firmenfelder || !firmenname || !ust_id) return;

  const anzeigen = typ === "geschaeft";

  firmenfelder.style.display = anzeigen ? "block" : "none";
  firmenname.toggleAttribute("required", anzeigen);
  ust_id.toggleAttribute("required", anzeigen);
}
// Anzeigen oder Verstecken vom Admin oder Kunde
function toggleKundentyp(value) {
    const container = document.getElementById('kundentyp-container');
    container.style.display = value === 'kunde' ? 'block' : 'none';
}

// Prüft, ob die Passwörter gleich und lang genug sind
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

// Validiert die IBAN bei Verlassen des Eingabefeldes
function validateIban() {
  const ibanInput = document.getElementById("iban");
  const fehlerText = document.getElementById("iban-fehler");

  if (!ibanInput || !fehlerText) return;

  ibanInput.addEventListener("blur", () => {
    const iban = ibanInput.value.trim();

    const isValid = IBAN.isValid(iban);
    ibanInput.classList.toggle("invalid", !isValid);
    fehlerText.textContent = isValid
      ? ""
      : "Bitte geben Sie eine gültige IBAN ein.";
  });
}

// Initialisierung bei DOM-Load
// Initialisierung bei DOM-Load
window.addEventListener("DOMContentLoaded", () => {
  toggleFirmenfelder();
  validateIban();

  const form = document.querySelector("form");
  if (form) {
    // Nur hinzufügen, wenn Passwortfelder existieren
    const pw1 = document.getElementById("passwort");
    const pw2 = document.getElementById("passwort2");

    if (pw1 && pw2) {
      form.addEventListener("submit", checkPasswortGleichheit);
    }
  }

  const typElement = document.getElementById("typ");
  if (typElement) {
    typElement.addEventListener("change", toggleFirmenfelder);
  }

  const rolleElement = document.getElementById("rolle");
  if (rolleElement) {
    toggleKundentyp(rolleElement.value);
    rolleElement.addEventListener("change", function () {
      toggleKundentyp(this.value);
    });
  }
});

