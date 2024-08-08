<?php

namespace Tests\ProcedureManager;

use PhpAddons\ProcedureManager\DefaultProcedureStep;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . "/../../vendor/autoload.php");


class DefaultProcedureStepTest extends TestCase{
    #[TestDox(text: "Test d'accès et mise à jour des données")]
    public function testDatas():void{
        $procedureStep = new DefaultProcedureStep();

        $datas = $procedureStep->getDatas();
        $this->assertIsArray(actual: $datas);
        $this->assertEmpty(actual: $datas);

        $datas = $procedureStep
            ->setDatas(datas: ["yahvya"])
            ->getDatas();

        $this->assertIsArray(actual: $datas);
        $this->assertCount(expectedCount: 1,haystack: $datas);
        $this->assertEquals(expected: "yahvya",actual: $datas[0]);
    }

    #[TestDox(text: "Test des fonctions d'accès")]
    public function testAccessVerifier():void{
        $procedureStep = new DefaultProcedureStep();

        $this->assertTrue(condition: $procedureStep->canAccessNextStep());

        $procedureStep->setNextAccessVerifier(nextAccessVerifier: fn():bool => false);
        $this->assertFalse(condition: $procedureStep->canAccessNextStep());

        $procedureStep->setNextAccessVerifier(nextAccessVerifier: fn():bool => true);
        $this->assertTrue(condition: $procedureStep->canAccessNextStep());
    }
}