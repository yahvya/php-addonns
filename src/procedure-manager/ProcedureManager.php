<?php

namespace PhpAddons\ProcedureManager;

use Throwable;

/**
 * @brief Gestionnaire de procédure
 * @author yahaya https://github.com/yahvya
 */
class ProcedureManager{
    /**
     * @var int Index de l'étape courant
     */
    protected int $currentStepIndex;

    /**
     * @param array $steps Etapes de la procédure
     */
    protected function __construct(protected array $steps){
        $this->currentStepIndex = 0;
    }

    /**
     * @return ProcedureStep|null L'étape actuelle de la procédure ou null si aucune étapes
     */
    public function current():?ProcedureStep{
        return $this->steps[$this->currentStepIndex] ?? null;
    }

    /**
     * @return ProcedureStep|null l'étape précédente de la procédure
     * @attention déplace le curseur sur le précédent
     */
    public function previous():?ProcedureStep{
        $nextIndex = $this->currentStepIndex - 1;

        if(!empty($this->steps[$nextIndex])){
            $this->currentStepIndex--;
            return $this->steps[$this->currentStepIndex];
        }

        return null;
    }

    /**
     * @return ProcedureStep|null l'étape suivante de la procédure
     * @attention déplace le curseur sur le suivant
     */
    public function next():?ProcedureStep{
        $nextIndex = $this->currentStepIndex + 1;

        if(!empty($this->steps[$nextIndex])){
            $this->currentStepIndex++;
            return $this->steps[$this->currentStepIndex];
        }

        return null;
    }

    /**
     * @brief Met à jour l'index actuel d'étape (step fourni - 1)
     * @param int $step numéro de l'étape de 1 à "x"
     * @return $this
     */
    public function setCurrentStepIndex(int $step):ProcedureManager{
        $this->currentStepIndex = $step;

        return $this;
    }

    /**
     * @return int le numéro d'index actuel + 1 (pour le format 1 à "x" étapes)
     */
    public function getCurrentStepIndex():int{
        return $this->currentStepIndex + 1;
    }

    /**
     * @brief Met à jour les étapes
     * @attention Pensez à utiliser setStep si l'index doit être mis à jour
     * @param ProcedureStep[] $steps étapes
     * @return $this
     */
    public function setSteps(array $steps):ProcedureManager{
        $this->steps = $steps;

        return $this;
    }

    /**
     * @return ProcedureStep[] les étapes
     */
    public function getSteps():array{
        return $this->steps;
    }

    /**
     * @return string|null la version serializé de la class
     */
    public function serialize():?string{
        try{
            $serializedVer = serialize(value: $this);

            return empty($serializedVer) ? null : $serializedVer;
        }
        catch(Throwable){
            return null;
        }
    }


    /**
     * @brief Initialise une procédure
     * @param ProcedureStep ...$steps étape de la procédure
     * @return ProcedureStep le gestionnaire créé
     */
    public static function define(ProcedureStep... $steps):ProcedureStep{
        $procedureManager = new ProcedureManager(steps: $steps);

        return $procedureManager->current();
    }

    /**
     * @brief Charge un version serializé de la classe
     * @param string $serializedVer version serializé de la class
     * @return ProcedureManager|null la class chargée ou null
     */
    public static function loadFrom(string $serializedVer):?ProcedureManager{
        try{
            $unSerializedVer = unserialize(data: $serializedVer);

            return empty($unSerializedVer) ||
                (!$unSerializedVer instanceof ProcedureManager) ? null : $unSerializedVer;
        }
        catch(Throwable){
            return null;
        }
    }
}