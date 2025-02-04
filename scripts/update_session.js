
function updateSession() {
    fetch('/scripts/update_session.php')
        .then(response => response.text())
        .then(data => console.log('Session mise à jour'));
}

// Met à jour toutes les 30 secondes
setInterval(updateSession, 30000);

// Appel initial dès le chargement de la page
updateSession();