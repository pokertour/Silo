<?php

namespace App\Exceptions;

use RuntimeException;

/** Levée pendant le téléchargement quand l'utilisateur annule un import. */
class ImportCancelledException extends RuntimeException {}
