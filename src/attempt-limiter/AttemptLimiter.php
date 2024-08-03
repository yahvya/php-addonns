<?php

namespace PhpAddons\AttemptLimiter;

/**
 * @brief Utilitaire de limitation d'appel
 * @author yahaya https://github.com/yahvya
 */
class AttemptLimiter{
    /**
     * @brief Vérifie si une tentative peut être faîtes
     * @param callable $callable callable à appeler
     * @return bool si une tentative peut être faîtes
     * @attention Met à jour les données de tentatives si besoin
     */
    public static function checkCanAttempt(callable $callable):bool{

        return true;
    }

    /**
     * @brief Tente d'appeler la fonction fournie
     * @param callable $callable callable à appeler
     * @param array $args arguments à fournir au callable
     * @param callable|null $onError Fonction à exécuter en cas de refus de tentatives, si null exception de refus envoyée
     * @return mixed le résultat du callable fourni
     * @throws AttemptLimiterException en cas de refus d'appel et $exceptionOnFail à true
     */
    public static function attempt(callable $callable,array $args,callable|null $onError = null):mixed{

        return call_user_func_array(callback: $callable,args: $args);
    }
}