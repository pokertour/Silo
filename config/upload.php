<?php

return [
    // Taille des morceaux envoyés par le navigateur, en Mo. Moins de requêtes
    // avec une valeur plus grande, mais le proxy inverse doit accepter des
    // corps de requête de cette taille (nginx : client_max_body_size).
    'chunk_size_mb' => max(1, min(500, (int) env('UPLOAD_CHUNK_SIZE_MB', 5))),
];
