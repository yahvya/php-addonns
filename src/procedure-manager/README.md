# Utilitaire d'exécution de procédure

> Cet utilitaire n'a aucune notice d'intégration supplémentaire

## Utilisation

> Cet utilitaire fonctionne en définissant en amont une suite d'étape décrivant une procédure, et sert à gérer l'avancement dans la procédure.

> Une réprésentation d'état par défaut est défini pour être généraliste ***DefaultProcedureStep***, toutefois des étapes personnalisées peuvent être définies.


**Phase d'initialisation du processus**

```
use PhpAddons\ProcedureManager\ProcedureManager;
use PhpAddons\ProcedureManager\ProcedureStep;
use PhpAddons\ProcedureManager\DefaultProcedureStep;

class VerifyCodeStep implements ProcedureStep{
    public function getDatas():mixed{ return []; }
    
    public function setDatas(mixed $datas):ProcedureStep { return $this; }

    public function canAccessNextStep():bool { 
        // vérification de validation de code par exemple
        return true;
    }
}

function storeProcess(ProcedureManager $procedureManager):void{
    $datas = $procedureManager->serialize();

    if($datas === null)
        throw new Exception(message: "Echec d'initialisation");
    
    $_SESSION["STORE_KEY"] = $datas; 
} 

storeProcedure(toStore: ProcedureManager::define(
    new VerifyCodeStep(),
    new DefaultProcedureStep(datas: ["validateInscription" => true]),
    new DefaultProcedureStep(datas: ["finalize" => true],nextAccessVerifier: fn():bool => true)
));  
```

**Utilisation**

```
use PhpAddons\ProcedureManager\ProcedureManager;   

function getProcedure():?ProcedureManager{
    return ProcedureManager::loadFrom(serializedVer: $_SESSION["STORE_KEY"]);
}

$procedure = getProcedure();

if($procedure === null)
    throw new Exception(message: "Echec de chargement de la procédure");
    
if(empty($procedure->current()?->canAccessNextStep())
    throw new Exception(message: "Action non autorisé");
    
if($procedure->next() === null)
    echo "Fin de procédure";
    
storeProcedure(toStore: $procedure);
```
