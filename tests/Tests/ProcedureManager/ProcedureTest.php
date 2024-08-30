<?php

namespace Tests\ProcedureManager;

use PhpAddons\ProcedureManager\Procedure;
use PhpAddons\ProcedureManager\ProcedureStep;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @brief Etape de test
 */
readonly class TestStep implements ProcedureStep{
    public function __construct(public int $id){
    }

    public function canAccessNext(Procedure $procedure, ...$args): bool{
        return $args[0];
    }

    /**
     * @param TestStep $toCompare objet à comparer
     * @return bool si les objets sont égaux
     */
    public function equals(TestStep $toCompare):bool{
        return $toCompare->id === $this->id;
    }
}

/**
 * @brief Procédure de test
 */
class TestProcedure extends Procedure {
    /**
     * @param ProcedureStep[] $steps
     */
    public function __construct(protected array $steps){
        parent::__construct();
    }

    public function getSteps(): array{
        return $this->steps;
    }
}

/**
 * @brief Test de procédure
 */
class ProcedureTest extends TestCase {
    #[TestDox(text: "Test de récupération du numéro de l'étape courante")]
    public function testGetCurrentStepNumber():void{
        $procedure = new TestProcedure(steps:[]);
        $this->assertEquals(expected: 1,actual: $procedure->getCurrentStepNumber());

        $procedure = new TestProcedure(steps:[new TestStep(id: 1)]);
        $this->assertEquals(expected: 1,actual: $procedure->getCurrentStepNumber());
    }

    #[TestDox(text: "Test de récupération de l'étape courante")]
    public function testGetCurrentStep():void{
        $procedure = new TestProcedure(steps:[]);
        $this->assertNull(actual: $procedure->getCurrentStep());

        $procedure = new TestProcedure(steps:[new TestStep(id: 1)]);
        $this->assertNotNull(actual: $procedure->getCurrentStep());
    }

    #[TestDox(text: "Test de récupération d'une étape au numéro donné")]
    public function testGetStep():void{
        $procedure = new TestProcedure(steps:[]);

        $this->assertNull(actual: $procedure->getStep(stepNumber: 1));
        $this->assertNull(actual: $procedure->getStep(stepNumber: 2));
        $this->assertNull(actual: $procedure->getStep(stepNumber: 0));

        $steps = [
            new TestStep(id: 1),
            new TestStep(id: 2),
            new TestStep(id: 3)
        ];
        $procedure = new TestProcedure(steps: $steps);

        $this->assertNull(actual: $procedure->getStep(stepNumber: 4));
        $this->assertObjectEquals(expected: $steps[0],actual: $procedure->getStep(stepNumber: 1));
        $this->assertObjectNotEquals(expected: $steps[0],actual: $procedure->getStep(stepNumber: 2));
        $this->assertObjectNotEquals(expected: $steps[0],actual: $procedure->getStep(stepNumber: 3));
        $this->assertObjectEquals(expected: $steps[2],actual: $procedure->getStep(stepNumber: 3));
        $this->assertObjectEquals(expected: $steps[1],actual: $procedure->getStep(stepNumber: 2));
    }

    #[TestDox(text: "Test de récupération de l'étape suivante")]
    public function testGetNextStep():void{
        $procedure = new TestProcedure(steps:[]);
        $this->assertNull(actual: $procedure->getNextStep());

        $steps = [
            new TestStep(id: 1),
        ];
        $procedure = new TestProcedure(steps: $steps);
        $this->assertNull(actual: $procedure->getNextStep());


        $steps = [
            new TestStep(id: 1),
            new TestStep(id: 2),
            new TestStep(id: 3),
        ];
        $procedure = new TestProcedure(steps: $steps);
        $this->assertObjectEquals(expected: $steps[1],actual: $procedure->getNextStep());
        $this->assertObjectEquals(expected: $steps[1],actual: $procedure->getNextStep());
    }

    #[TestDox(text: "Test de récupération de l'étape précédente")]
    public function testGetPreviousStep():void{
        $procedure = new TestProcedure(steps:[]);
        $this->assertNull(actual: $procedure->getPreviousStep());

        $steps = [
            new TestStep(id: 1),
            new TestStep(id: 2)
        ];

        $procedure = new TestProcedure(steps: $steps);
        $this->assertNull(actual: $procedure->getPreviousStep());
        $procedure->next(checkAccess: false);
        $this->assertObjectEquals(expected: $steps[0],actual: $procedure->getPreviousStep());
    }

    #[TestDox(text: "Test du passage à l'étape suivante")]
    public function testNext():void{
        $procedure = new TestProcedure(steps: []);
        $this->assertNull(actual: $procedure->next(checkAccess: false));

        $procedure = new TestProcedure(steps: [new TestStep(id: 1)]);
        $this->assertNull(actual: $procedure->next(checkAccess: false));

        $steps = [
            new TestStep(id: 1),
            new TestStep(id: 2)
        ];
        $procedure = new TestProcedure(steps: $steps);
        $this->assertObjectEquals(expected: $procedure->next(checkAccess: false), actual: $steps[1]);
    }

    #[TestDox(text: "Test du passage à l'étape précédente")]
    public function testPrevious():void{
        $procedure = new TestProcedure(steps: []);
        $this->assertNull(actual: $procedure->previous());

        $procedure = new TestProcedure(steps: [new TestStep(id: 1)]);
        $this->assertNull(actual: $procedure->previous());

        $steps = [
            new TestStep(id: 1),
            new TestStep(id: 2)
        ];
        $procedure = new TestProcedure(steps: $steps);
        $procedure->next(checkAccess: false);
        $this->assertObjectEquals(expected: $procedure->previous(), actual: $steps[0]);
    }

    /**
     * @throws Throwable en cas d'échec de serialization
     */
    #[TestDox(text: "Vérifie la serialization et le chargement")]
    public function testGetSavableAndLoadFrom():void{
        $procedure = new TestProcedure(steps: []);
        $savedClass = $procedure->getSavable();

        $this->assertIsString(actual: $savedClass);
        $this->assertTrue(condition: TestProcedure::loadFrom(from: $savedClass) instanceof TestProcedure);
    }

    #[TestDox(text: "Vérifie l'accès à l'élément suivant")]
    public function testCanAccessNext():void{
        $procedure = new TestProcedure(steps: []);
        $this->assertFalse(condition: $procedure->canAccessNext());

        $procedure = new TestProcedure(steps: [new TestStep(id: 1)]);
        $this->assertFalse(condition: $procedure->canAccessNext(false));

        $procedure = new TestProcedure(steps: [new TestStep(id: 1)]);
        $this->assertTrue(condition: $procedure->canAccessNext(true));
    }

    #[TestDox(text: "Vérifie l'état de fin d'une procédure")]
    public function testIsFinished():void{
        $procedure = new TestProcedure(steps: []);
        $this->assertFalse(condition: $procedure->isFinished());

        $procedure = new TestProcedure(steps: [
            new TestStep(id: 1),
            new TestStep(id: 2)
        ]);
        $this->assertFalse(condition: $procedure->isFinished());
        $procedure->next(checkAccess: false);
        $this->assertTrue(condition: $procedure->isFinished(true));
    }
}