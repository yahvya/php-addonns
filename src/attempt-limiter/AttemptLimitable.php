<?php

namespace PhpAddons\AttemptLimiter;

use Attribute;

/**
 * @brief Attribut de marquage d'élément limitable
 * @author yahaya https://github.com/yahvya
 */
#[Attribute]
class AttemptLimitable{
    /**
     * @param int $countOfAttempt nombre de tentatives maximum
     * @param int $timeBeforeNextAttempt temps en secondes avant réinitialisation de tentatives
     * @param AttemptErrorMarker $errorMarker type de marker servant à détecter un retour d'erreur
     * @param bool $resetOnSuccess si true, réinitialise les tentatives en cas de succès d'appel, sinon continue le décompte
     */
    public function __construct(
        public readonly int $countOfAttempt,
        public readonly int $timeBeforeNextAttempt,
        public readonly AttemptErrorMarker $errorMarker,
        public readonly bool $resetOnSuccess = true
    ){
    }
}