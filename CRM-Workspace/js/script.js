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

// Zeigt oder versteckt Felder für Kunden
function toggleKundenfelder(rolle) {
  const kundenbereiche = document.querySelectorAll(".kunde-feld");
  const anzeigen = rolle === "kunde";

  kundenbereiche.forEach((bereich) => {
    bereich.style.display = anzeigen ? "" : "none";
    bereich.querySelectorAll("input, select, textarea").forEach((el) => {
      el.disabled = !anzeigen;
    });
  });
}

// Zeigt oder versteckt den Kundentyp-Container
function toggleKundentyp(rolle) {
  const container = document.getElementById("kundentyp-container");
  if (container) {
    container.style.display = rolle === "kunde" ? "block" : "none";
  }
}

// IBAN validieren und optisch markieren
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
      fehlerText.style.display = isValid ? "none" : "block";
    });
  }
}

// Passwort-Validierung (optisch + logisch)
function checkPasswortGleichheit(event) {
  const pw1 = document.getElementById("passwort");
  const pw2 = document.getElementById("passwort2");
  const fehlermeldung = document.getElementById("passwort-fehler");

  if (!pw1 || !pw2 || !fehlermeldung) return;

  const val1 = pw1.value.trim();
  const val2 = pw2.value.trim();
  const minLength = 8;

  let fehler = "";

  if (val1.length < minLength) {
    fehler = `Das Passwort muss mindestens ${minLength} Zeichen lang sein.`;
  } else if (val1 !== val2) {
    fehler = "Die Passwörter stimmen nicht überein.";
  }

  if (fehler) {
    fehlermeldung.textContent = fehler;
    fehlermeldung.style.display = "block";
    pw1.classList.add("invalid");
    pw2.classList.add("invalid");
    event.preventDefault();
    return false;
  }

  // Falls OK
  fehlermeldung.textContent = "";
  fehlermeldung.style.display = "none";
  pw1.classList.remove("invalid");
  pw2.classList.remove("invalid");
  return true;
}

window.addEventListener("DOMContentLoaded", () => {
  toggleFirmenfelder();

  const rolleElement = document.getElementById("rolle");
  if (rolleElement) {
    const rolle = rolleElement.value;
    toggleKundentyp(rolle);
    toggleKundenfelder(rolle);

    rolleElement.addEventListener("change", (e) => {
      const neueRolle = e.target.value;
      toggleKundentyp(neueRolle);
      toggleKundenfelder(neueRolle);
    });
  }

  initIbanValidation();

  const form = document.getElementById("passwort-formular");
  if (form) {
    form.addEventListener("submit", checkPasswortGleichheit);
  }

  const typElement = document.getElementById("typ");
  if (typElement) {
    typElement.addEventListener("change", toggleFirmenfelder);
  }
});
