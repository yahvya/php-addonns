<?php

namespace PhpAddons\AttemptLimiter;

/**
 * @brief Marker servant à décrire la manière de détecter une erreur
 * @author yahaya https://github.com/yahvya
 */
enum AttemptErrorMarker{
    /**
     * @brief Retour booléen false
     */
    case FALSE_RETURNED;

    /**
     * @brief Exception renvoyée
     */
    case EXCEPTION_THROWN;

    /**
     * @brief Valeur null retournée
     */
    case NULL_RETURNED;

    /**
     * @brief Renvoi de cette valeur d'énumération comme retour
     */
    case ERROR_MARKER;
}