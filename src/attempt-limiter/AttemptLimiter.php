<?php

namespace PhpAddons\AttemptLimiter;

use ReflectionMethod;
use Throwable;

/**
 * @brief Utilitaire de limitation d'appel
 * @author yahaya https://github.com/yahvya
 */
class AttemptLimiter{
    /**
     * @brief Clé de stockage en session de l'utilitaire
     */
    protected const string SESSION_STORAGE_KEY = "phpaddons.attempt-limiter.datas";

    /**
     * @brief Vérifie si une tentative peut être faîtes
     * @param array $callable callable à appeler
     * @return bool si une tentative peut être faîtes
     * @attention Peut mettre à jour les données de limitation
     */
    public static function checkCanAttempt(array $callable):bool{
        $callableUniqueKey = self::getCallableUniqueKey(callable: $callable);

        if($callableUniqueKey === NULL)
            return false;

        if(
            empty($_SESSION[self::SESSION_STORAGE_KEY][$callableUniqueKey]) ||
            empty($_SESSION[self::SESSION_STORAGE_KEY][$callableUniqueKey]["supposedEndTime"])
        )
            return true;

        // suppression de la limitation pour temps passé
        if($_SESSION[self::SESSION_STORAGE_KEY][$callableUniqueKey]["supposedEndTime"] - time() <= 0){
            self::removeAttempts(callable: $callable);
            return true;
        }

        return false;
    }

    /**
     * @brief Supprime les tentatives enregistrés
     * @param array $callable callable
     */
    public static function removeAttempts(array $callable):void{
        unset($_SESSION[self::SESSION_STORAGE_KEY][self::getCallableUniqueKey(callable: $callable)]);
    }

    /**
     * @brief Enregistre une tentative
     * @param array $callable callable
     * @param AttemptLimitable $limiterDescription Description de limitation
     */
    public static function registerAttempt(array $callable,AttemptLimitable $limiterDescription):void{
        $callableUniqueKey = self::getCallableUniqueKey(callable: $callable);

        // vérification d'existence ou création
        if(empty($_SESSION[self::SESSION_STORAGE_KEY][$callableUniqueKey])){
            $_SESSION[self::SESSION_STORAGE_KEY][$callableUniqueKey][$callableUniqueKey] = [
                "countOfAttempts" => 1
            ];
        }

        // récupération des données
        $limitationDatas = $_SESSION[self::SESSION_STORAGE_KEY][$callableUniqueKey][$callableUniqueKey];
        $limitationDatas["countOfAttempts"]++;

        // enregistrement du temps de fin
        if($limitationDatas["countOfAttempts"] >= $limiterDescription->countOfAttempt)
            $limitationDatas["supposedEndTime"] = time() + $limiterDescription->timeBeforeNextAttempt;

        $_SESSION[self::SESSION_STORAGE_KEY][$callableUniqueKey][$callableUniqueKey] = $limitationDatas;
    }

    /**
     * @brief Tente d'appeler la fonction fournie
     * @param array $callable callable à appeler
     * @param array $args arguments à fournir au callable
     * @param array|null $onError Fonction à exécuter en cas de refus de tentatives et retour de cette fonction fourni, si null exception de refus envoyée
     * @return mixed le résultat du callable fourni
     * @throws AttemptLimiterException en cas de refus d'appel et $exceptionOnFail à true
     * @throws Throwable exception de la fonction appelée
     * @attention Si le callable fourni renvoi une exception, l'exception sera également levé
     */
    public static function attempt(array $callable,array $args = [],array|null $onError = null):mixed{
        // vérification de possibilité de tentative
        if(!self::checkCanAttempt(callable: $callable))
            return self::throwFail(onError: $onError);

        $limiterDescription = self::getLimiterDescription(callable: $callable);

        // récupération de retour
        try{
            $callResult = call_user_func_array(callback: $callable,args: $args);
        }
        catch(Throwable $e){
            $exception = $e;
            $callResult = null;
        }

        // gestion du cas d'erreur
        switch($limiterDescription->errorMarker){
            case AttemptErrorMarker::ERROR_MARKER:
                if($callResult === AttemptErrorMarker::ERROR_MARKER)
                    self::registerAttempt(callable: $callable,limiterDescription: $limiterDescription);
                else if($limiterDescription->resetOnSuccess)
                    self::removeAttempts(callable: $callable);

                return $callResult;

            case AttemptErrorMarker::FALSE_RETURNED:
                if($callResult === false)
                    self::registerAttempt(callable: $callable,limiterDescription: $limiterDescription);
                else if($limiterDescription->resetOnSuccess)
                    self::removeAttempts(callable: $callable);

                return $callResult;

            case AttemptErrorMarker::EXCEPTION_THROWN:
                if(empty($exception) ){
                    if($limiterDescription->resetOnSuccess)
                        self::removeAttempts(callable: $callable);

                    return $callResult;
                }

                self::registerAttempt(callable: $callable,limiterDescription: $limiterDescription);

                throw $exception;

            case AttemptErrorMarker::NULL_RETURNED:
                if($callResult === null)
                    self::registerAttempt(callable: $callable,limiterDescription: $limiterDescription);
                else if($limiterDescription->resetOnSuccess)
                    self::removeAttempts(callable: $callable);

                return $callResult;
        }

        self::registerAttempt(callable: $callable,limiterDescription: $limiterDescription);
        return $callable;
    }

    /**
     * @brief Récupère l'attribut de limitation associé au callable
     * @param array $callable le callable
     * @return AttemptLimitable|null l'attribut trouvé ou null en cas d'échec
     */
    protected static function getLimiterDescription(array $callable):?AttemptLimitable{
        try{
            [$objectClassname,$method] = $callable;

            $reflection = new ReflectionMethod(objectOrMethod: $objectClassname,method: $method);

            $attributes = $reflection->getAttributes(name: AttemptLimitable::class);

            if(empty($attributes) )
                return null;

            return $attributes[0]->newInstance();
        }
        catch(Throwable){}

        return null;
    }

    /**
     * @brief Gestion de l'envoi d'erreur
     * @param array|null $onError callable de gestion d'erreur
     * @param string $customErrorMessage message d'erreur en cas d'exception
     * @return mixed retour de $onError si null
     * @throws AttemptLimiterException en cas d'erreur et $onError NULL
     */
    protected static function throwFail(array|null $onError = null,string $customErrorMessage = "Tentative non autorisé"):mixed{
        if($onError === NULL)
            throw new AttemptLimiterException(message: $customErrorMessage);

        return call_user_func(callback: $onError);
    }

    /**
     * @brief Fourni la clé unique associé au callable
     * @attention La clé générée est basée sur le namespace, le nom de la class ainsi que du nom de la méthode. Au changement d'un de ses éléments la clé unique sera modifiée également
     * @param array $callable le callable
     * @return string|null la clé unique ou null en cas d'échec
     */
    protected static function getCallableUniqueKey(array $callable):?string{
        try{
            [$objectClassname,$method] = $callable;

            return str_replace(
                search: "\\",
                replace: ".",
                subject: "$objectClassname.$method"
            );
        }
        catch(Throwable){}

        return null;
    }
}