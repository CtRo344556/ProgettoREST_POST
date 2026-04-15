<?php
header("Content-Type: application/json");

// Connessione al database
$connessione = mysqli_connect("127.0.0.1:3307", "root", "", "biblioteca");

// Debug serio
if (!$connessione) {
    die(json_encode([
        'status' => 'error',
        'message' => mysqli_connect_error()
    ]));
}

// Imposta charset
mysqli_set_charset($connessione, "utf8");

// Test connessione
echo json_encode([
    'status' => 'success',
    'message' => 'Connessione riuscita'
]);

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo == "GET") {

    // Restituisce tutti i libri
    $risultato = mysqli_query($connessione, "SELECT * FROM libri");
    $listaLibri = [];

    while ($riga = mysqli_fetch_assoc($risultato)) {
        $listaLibri[] = $riga;
    }

    echo json_encode($listaLibri);

} elseif ($metodo == "POST") {

    // Aggiunge un nuovo libro
    $datiRicevuti = json_decode(file_get_contents('php://input'), true);

    if (!isset($datiRicevuti['id'], $datiRicevuti['titolo'], $datiRicevuti['autore'], $datiRicevuti['anno'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Campi mancanti: id, titolo, autore, anno']);
        exit;
    }

    $idLibro     = $datiRicevuti['id'];
    $titoloLibro = $datiRicevuti['titolo'];
    $autoreLibro = $datiRicevuti['autore'];
    $annoLibro   = $datiRicevuti['anno'];

    $query = "INSERT INTO libri (id, titolo, autore, anno) VALUES ($idLibro, '$titoloLibro', '$autoreLibro', $annoLibro)";
    mysqli_query($connessione, $query);

    http_response_code(201);
    echo json_encode(['id' => $idLibro, 'titolo' => $titoloLibro, 'autore' => $autoreLibro, 'anno' => $annoLibro]);

} elseif ($metodo == "DELETE") {

    // Elimina un libro tramite id
    $idLibro = $_GET['id'];

    $risultato = mysqli_query($connessione, "SELECT * FROM libri WHERE id = $idLibro");
    if (mysqli_num_rows($risultato) == 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => "Libro con id $idLibro non trovato"]);
        exit;
    }

    mysqli_query($connessione, "DELETE FROM libri WHERE id = $idLibro");
    echo json_encode(['status' => 'success', 'message' => "Libro con id $idLibro eliminato"]);

} elseif ($metodo == "PUT") {

    // Aggiorna un libro tramite id
    $idLibro      = $_GET['id'];
    $datiRicevuti = json_decode(file_get_contents('php://input'), true);

    $risultato = mysqli_query($connessione, "SELECT * FROM libri WHERE id = $idLibro");
    if (mysqli_num_rows($risultato) == 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => "Libro con id $idLibro non trovato"]);
        exit;
    }

    $titoloLibro = $datiRicevuti['titolo'];
    $autoreLibro = $datiRicevuti['autore'];
    $annoLibro   = $datiRicevuti['anno'];

    mysqli_query($connessione, "UPDATE libri SET titolo='$titoloLibro', autore='$autoreLibro', anno=$annoLibro WHERE id=$idLibro");
    echo json_encode(['status' => 'success', 'message' => "Libro con id $idLibro aggiornato"]);

} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metodo non supportato']);
}

mysqli_close($connessione);
?>
