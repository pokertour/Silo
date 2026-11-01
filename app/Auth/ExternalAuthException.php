<?php

namespace App\Auth;

use RuntimeException;

/** Échec d'une connexion LDAP / SSO ; le message est affiché à l'utilisateur. */
class ExternalAuthException extends RuntimeException {}
