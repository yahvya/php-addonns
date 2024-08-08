<?php

namespace PhpAddons\ProcedureManager;

use Closure;

/**
 * @brief Etape de procédure par défaut
 * @author yahaya https://github.com/yahvya
 * @implements ProcedureStep<array>
 */
class DefaultProcedureStep implements ProcedureStep {
    /**
     * @param array $datas données de l'étape
     * @param Closure|null $nextAccessVerifier vérificateur à retour booléen ou si null remplacé par une fonction par défaut true
     */
    public function __construct(protected array $datas = [],protected Closure|null $nextAccessVerifier = null){
        if($this->nextAccessVerifier === null)
            $this->nextAccessVerifier = fn():bool => true;
    }

    public function getDatas():array{
        return $this->datas;
    }

    public function canAccessNextStep(): bool{
        return call_user_func(callback: $this->nextAccessVerifier);
    }

    public function setDatas(mixed $datas): ProcedureStep{
        $this->datas = $datas;

        return $this;
    }

    /**
     * @brief Met à jour le vérificateur de passage suivant
     * @param Closure $nextAccessVerifier vérificateur à retour booléen
     * @return $this
     */
    public function setNextAccessVerifier(Closure $nextAccessVerifier):DefaultProcedureStep{
        $this->nextAccessVerifier = $nextAccessVerifier;

        return $this;
    }
}