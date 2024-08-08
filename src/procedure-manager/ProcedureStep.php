<?php

namespace PhpAddons\ProcedureManager;

/**
 * @brief Etape de procédure
 * @author yahaya https://github.com/yahvya
 * @template DatasType type des données
 */
interface ProcedureStep{
    /**
     * @return DatasType les données
     */
    public function getDatas():mixed;

    /**
     * @brief Met à jour les données
     * @param DatasType $datas nouvelles données
     * @return $this
     */
    public function setDatas(mixed $datas):ProcedureStep;

    /**
     * @return bool si l'étape suivante du processus est accessible
     */
    public function canAccessNextStep():bool;
}