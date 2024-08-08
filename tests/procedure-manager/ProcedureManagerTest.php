<?php

namespace Tests\ProcedureManager;

use PhpAddons\ProcedureManager\DefaultProcedureStep;
use PhpAddons\ProcedureManager\ProcedureManager;
use PhpAddons\ProcedureManager\ProcedureStep;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . "/../../vendor/autoload.php");

/**
 * @brief Action de mouvement
 */
enum Action{
    case NEXT;

    case PREVIOUS;

    case CURRENT;

    case NOTHING;
}

class ProcedureManagerTest extends TestCase {
    public ProcedureManager $procedureManager;

    // TEST D ACCESS COURANT

    #[TestDox("=> Vérification du retour courant - valeur attendue (\$expectedValue) - action suivante (\$nextAction)")]
    #[DataProvider(methodName: "getCurrentDatas")]
    public function testCurrent(ProcedureManager $procedureManager,?ProcedureStep $expectedValue,Action $nextAction):void{
        $this->assertEquals(expected: $expectedValue,actual: $procedureManager->current());

        switch($nextAction){
            case Action::CURRENT:
                $procedureManager->current();
                break;

            case Action::NEXT:
                $procedureManager->next();
                break;

            case Action::PREVIOUS:
                $procedureManager->previous();
                break;

            default:
        }
    }

    /**
     * @return array[] données de test du retour courant
     */
    public static function getCurrentDatas():array{
        $steps = [
            new DefaultProcedureStep(),
            new DefaultProcedureStep(),
            new DefaultProcedureStep(),
            new DefaultProcedureStep()
        ];

        $procedureManager = ProcedureManager::define(...$steps);

        return [
            [$procedureManager,$steps[0],Action::NOTHING],
            [$procedureManager,$steps[0],Action::NEXT],
            [$procedureManager,$steps[1],Action::NEXT],
            [$procedureManager,$steps[2],Action::NEXT],
            [$procedureManager,$steps[3],Action::PREVIOUS],
            [$procedureManager,$steps[2],Action::PREVIOUS],
            [$procedureManager,$steps[1],Action::PREVIOUS],
            [$procedureManager,$steps[1],Action::PREVIOUS]
        ];
    }

    // TEST D ACCESS SUIVANT

    #[TestDox(text: "=> Vérification du retour d'action suivante - valeur attendue (\$expectedValue)")]
    #[DataProvider(methodName: "getNextDatas")]
    #[DataProvider(methodName: "getNextNullDatas")]
    public function testNext(ProcedureManager $procedureManager,?ProcedureStep $expectedValue):void{
        $this->assertEquals(expected: $expectedValue,actual: $procedureManager->next());
    }

    /**
     * @return array[] données de test de l'accès suivant
     */
    public static function getNextDatas():array{
        $steps = [
            new DefaultProcedureStep(),
            new DefaultProcedureStep(),
            new DefaultProcedureStep(),
            new DefaultProcedureStep()
        ];

        $procedureManager = ProcedureManager::define(...$steps);

        return [
            [$procedureManager,$steps[1]],
            [$procedureManager,$steps[2]],
            [$procedureManager,$steps[3]],
            [$procedureManager,null],
            [$procedureManager,null]
        ];
    }

    /**
     * @return array[] données de test de l'accès suivant à null
     */
    public static function getNextNullDatas():array{
        $procedureManager = ProcedureManager::define();

        return [
            [$procedureManager,null]
        ];
    }

    // TEST DE MOUVEMENT D INDEX

    #[TestDox(text: "=> Test de gestion d'index - index attendu (\$expectedIndex) - action suivante (\$nextAction)")]
    #[DataProvider(methodName: "getIndexMoveDatas")]
    public function testIndexMove(ProcedureManager $procedureManager,int $expectedIndex,Action $nextAction):void{
        $this->assertEquals(expected: $expectedIndex,actual: $procedureManager->getCurrentStepIndex());

        switch($nextAction){
            case Action::CURRENT:
                $procedureManager->current();
            break;

            case Action::NEXT:
                $procedureManager->next();
            break;

            case Action::PREVIOUS:
                $procedureManager->previous();
            break;

            default:
        }
    }

    /**
     * @return array[] données de test du mouvement d'index
     */
    public static function getIndexMoveDatas():array{
        $procedureManager = ProcedureManager::define(
            new DefaultProcedureStep(),
            new DefaultProcedureStep()
        );

        return [
            [$procedureManager,1,Action::NOTHING],
            [$procedureManager,1,Action::NEXT],
            [$procedureManager,2,Action::NEXT],
            [$procedureManager,2,Action::PREVIOUS],
            [$procedureManager,1,Action::PREVIOUS],
            [$procedureManager,1,Action::NOTHING],
        ];
    }

    // TEST DE MISE A JOUR DES ETAPES

    #[TestDox(text: "=> Test de la mise à jour des étapes")]
    public function testSetSteps():void{
        $procedureManager = ProcedureManager::define();

        $this->assertEquals(expected: null,actual: $procedureManager->current());
        $this->assertCount(expectedCount: 0,haystack: $procedureManager->getSteps());

        $steps = [new DefaultProcedureStep(),new DefaultProcedureStep()];
        $procedureManager->setSteps(steps: $steps);

        $this->assertEquals(expected: $steps[0],actual: $procedureManager->current());
        $this->assertCount(expectedCount: 2,haystack: $procedureManager->getSteps());

        $procedureManager->setCurrentStepIndex(step: 2);
        $this->assertEquals(expected: $steps[1],actual: $procedureManager->current());
    }
}