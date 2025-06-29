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

  fehlermeldung.textContent = "";
  fehlermeldung.style.display = "none";
  pw1.classList.remove("invalid");
  pw2.classList.remove("invalid");
  return true;
}

// 🔐 Login Modal initialisieren (einziger Modalteil)
function initLoginForm() {
  const loginForm = document.getElementById("login-form");
  const errorText = document.getElementById("login-error");

  if (!loginForm) return;

  loginForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const passwort = document.getElementById("passwort").value;

    fetch("login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, passwort }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          window.location.href = data.redirect;
        } else {
          errorText.textContent = data.message;
          errorText.style.display = "block";
        }
      })
      .catch(() => {
        errorText.textContent = "Es ist ein Fehler aufgetreten.";
        errorText.style.display = "block";
      });
  });
}

// 🪟 Nur das Login-Modal dynamisch laden
function openModal() {
  const modal = document.getElementById("auth-modal");
  const modalBody = document.getElementById("modal-body");

  fetch("login.html")
    .then((response) => response.text())
    .then((html) => {
      modalBody.innerHTML = html;
      modal.style.display = "block";
      initLoginForm();
    })
    .catch((err) => {
      modalBody.innerHTML = "<p>Fehler beim Laden des Formulars.</p>";
      modal.style.display = "block";
    });
}

function closeModal() {
  document.getElementById("auth-modal").style.display = "none";
}

function showStatusMessage() {
  const params = new URLSearchParams(window.location.search);
  const status = params.get("status");
  if (!status) return;

  const messageBox = document.createElement("div");
  messageBox.className = "status-message";

  switch (status) {
    case "reset_sent":
      messageBox.textContent =
        "Wenn diese E-Mail registriert ist, wurde ein Link zum Zurücksetzen gesendet.";
      break;
    case "limit":
      messageBox.textContent =
        "Bitte warte mindestens eine Minute, bevor du erneut einen Link anforderst.";
      break;
    case "invalid":
      messageBox.textContent = "Bitte gib eine gültige E-Mail-Adresse ein.";
      break;
    case "confirmation_sent":
      messageBox.textContent =
        "Registrierung erfolgreich! Bitte bestätigen Sie Ihre E-Mail-Adresse.";
      break;
    case "email_exists":
      messageBox.textContent = "Diese E-Mail-Adresse ist bereits registriert.";
      break;
    case "kunden_update":
      messageBox.textContent = "Ihre Kundendaten wurden aktualisiert.";
      break;
    case "konto_geloescht":
      messageBox.textContent = "Ihr Konto wurde erfolgreich entfernt.";
      break;
    case "passwort_falsch":
      messageBox.textContent = "Das eingegebene Passwort ist falsch.";
      break;
    case "offene_rechnung":
      messageBox.textContent =
        "Sie können Ihr Konto nicht löschen, solange noch offene Rechnungen bestehen.";
      break;
    case "passwort_geaendert":
      messageBox.textContent = "Ihr Passwort wurde erfolgreich geändert.";
      break;
    case "benutzer_geloescht":
      messageBox.textContent = "Der Benutzer wurde erfolgreich gelöscht.";
      break;
    default:
      return;
  }

  document.body.prepend(messageBox);

  setTimeout(() => {
    messageBox.remove();
  }, 5000);
}

// Alles Initialisieren (für klassische Seiten + Login-Modal)
window.addEventListener("DOMContentLoaded", () => {
  toggleFirmenfelder();
  initIbanValidation();
  showStatusMessage();

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

  const form = document.getElementById("passwort-formular");
  if (form) {
    form.addEventListener("submit", checkPasswortGleichheit);
  }

  const typElement = document.getElementById("typ");
  if (typElement) {
    typElement.addEventListener("change", toggleFirmenfelder);
  }
});
